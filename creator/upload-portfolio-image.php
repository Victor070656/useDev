<?php
require_once '../includes/init.php';
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

// Check if file was uploaded
if (!isset($_FILES['portfolio_image']) || $_FILES['portfolio_image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file selected']);
    exit;
}

// Upload the file
$result = upload_portfolio_image($_FILES['portfolio_image']);

if (!$result['success']) {
    echo json_encode(['success' => false, 'error' => $result['error']]);
    exit;
}

// Return the file path for use in the form
echo json_encode([
    'success' => true,
    'file_path' => $result['file_path'],
    'message' => 'Image uploaded successfully'
]);
