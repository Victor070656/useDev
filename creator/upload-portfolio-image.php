<?php
require_once '../includes/init.php';
require_once '../includes/upload_handler.php';

start_session();
require_role('creator');

header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$userId = get_user_id();

// Check if file was uploaded
if (!isset($_FILES['portfolio_image']) || $_FILES['portfolio_image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file selected']);
    exit;
}

// Get creator profile
$stmt = db_prepare("SELECT id FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    echo json_encode(['success' => false, 'error' => 'Creator profile not found']);
    exit;
}

// Upload the file with enhanced handler
$uploadResult = upload_portfolio_image_enhanced($_FILES['portfolio_image'], $profile['id']);

if (!$uploadResult['success']) {
    echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
    exit;
}

// Log activity
log_activity($userId, 'portfolio_image_uploaded', 'creator_profile', $profile['id']);

// Return the file path for use in the form
echo json_encode([
    'success' => true,
    'file_path' => $uploadResult['file_path'],
    'thumb_path' => $uploadResult['thumb_path'],
    'file_name' => $uploadResult['file_name'],
    'message' => 'Image uploaded successfully'
]);
