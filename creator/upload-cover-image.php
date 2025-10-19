<?php
require_once '../includes/init.php';
require_once '../includes/upload_handler.php';

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
if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] === UPLOAD_ERR_NO_FILE) {
    set_flash('error', 'No file selected');
    redirect('/creator/profile.php');
    exit;
}

// Get creator profile
$stmt = db_prepare("SELECT id, cover_image FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    set_flash('error', 'Creator profile not found');
    redirect('/creator/profile.php');
    exit;
}

// Upload the file
$uploadResult = upload_cover_image_enhanced($_FILES['cover_image'], $userId);

if (!$uploadResult['success']) {
    set_flash('error', $uploadResult['error']);
    redirect('/creator/profile.php');
    exit;
}

// Delete old cover image if exists
if ($profile['cover_image']) {
    delete_uploaded_file_enhanced($profile['cover_image']);
}

// Update creator cover image
$stmt = db_prepare("UPDATE creator_profiles SET cover_image = ? WHERE id = ?");
$stmt->bind_param('si', $uploadResult['file_path'], $profile['id']);

if ($stmt->execute()) {
    // Log activity
    log_activity($userId, 'cover_image_updated', 'creator_profile', $profile['id']);

    set_flash('success', 'Cover image updated successfully');
} else {
    // Delete the newly uploaded file if database update failed
    delete_uploaded_file_enhanced($uploadResult['file_path']);
    set_flash('error', 'Failed to update cover image');
}

$stmt->close();
redirect('/creator/profile.php');
