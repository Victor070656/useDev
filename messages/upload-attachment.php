<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/upload_handler.php';

start_session();
require_auth();

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
if (!isset($_FILES['message_attachment']) || $_FILES['message_attachment']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file selected']);
    exit;
}

// Upload the attachment
$uploadResult = upload_message_attachment($_FILES['message_attachment'], $userId);

if (!$uploadResult['success']) {
    echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
    exit;
}

// Log activity
log_activity($userId, 'message_attachment_uploaded', null, null);

// Return the file information
echo json_encode([
    'success' => true,
    'file_path' => $uploadResult['file_path'],
    'thumb_path' => $uploadResult['thumb_path'],
    'file_name' => $uploadResult['file_name'],
    'file_size' => $uploadResult['file_size'],
    'mime_type' => $uploadResult['mime_type'],
    'message' => 'Attachment uploaded successfully'
]);
