<?php
require_once '../includes/init.php';
require_role('creator');

if (!is_post()) {
    redirect('/creator/profile.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/creator/profile.php');
    exit;
}

$userId = get_user_id();

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
    set_flash('error', 'No file selected');
    redirect('/creator/profile.php');
    exit;
}

// Upload the file
$result = upload_profile_picture($_FILES['profile_picture']);

if (!$result['success']) {
    set_flash('error', $result['error']);
    redirect('/creator/profile.php');
    exit;
}

$db = get_db_connection();

// Get old profile picture to delete it
$stmt = db_prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$oldPicture = $stmt->get_result()->fetch_assoc()['profile_picture'];
$stmt->close();

// Update user profile picture
$stmt = db_prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
$stmt->bind_param('si', $result['file_path'], $userId);

if ($stmt->execute()) {
    // Delete old profile picture if exists
    if ($oldPicture) {
        delete_uploaded_file($oldPicture);
    }

    set_flash('success', 'Profile picture updated successfully');
} else {
    // Delete the newly uploaded file if database update failed
    delete_uploaded_file($result['file_path']);
    set_flash('error', 'Failed to update profile picture');
}

$stmt->close();
redirect('/creator/profile.php');
