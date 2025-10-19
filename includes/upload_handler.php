<?php

/**
 * Comprehensive File Upload Handler
 * Handles profile pictures, portfolio images, attachments, and more
 */

/**
 * Upload profile picture with image processing
 * Max size: 2MB, allowed: jpg, png, webp
 */
function upload_profile_picture_enhanced($file, $user_id) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Validate upload
    $validation = validate_file_upload($file, $allowedTypes, $maxSize);
    if (!$validation['success']) {
        return $validation;
    }

    // Generate unique filename
    $extension = $validation['extension'];
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/profiles';

    // Ensure directory exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    // Create thumbnail
    $thumbnailResult = create_thumbnail($destination, $uploadPath . '/thumb_' . $filename, 150, 150);

    return [
        'success' => true,
        'file_path' => '/uploads/profiles/' . $filename,
        'thumb_path' => $thumbnailResult ? '/uploads/profiles/thumb_' . $filename : null,
        'file_name' => $filename
    ];
}

/**
 * Upload portfolio image with multiple sizes
 * Max size: 5MB, allowed: jpg, png, gif, webp
 */
function upload_portfolio_image_enhanced($file, $creator_profile_id) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Validate upload
    $validation = validate_file_upload($file, $allowedTypes, $maxSize);
    if (!$validation['success']) {
        return $validation;
    }

    // Generate unique filename
    $extension = $validation['extension'];
    $filename = 'portfolio_' . $creator_profile_id . '_' . uniqid() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/portfolio';

    // Ensure directory exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    // Create thumbnail (400x300)
    $thumbnailResult = create_thumbnail($destination, $uploadPath . '/thumb_' . $filename, 400, 300);

    return [
        'success' => true,
        'file_path' => '/uploads/portfolio/' . $filename,
        'thumb_path' => $thumbnailResult ? '/uploads/portfolio/thumb_' . $filename : null,
        'file_name' => $filename
    ];
}

/**
 * Upload project attachment (documents)
 * Max size: 10MB, allowed: pdf, doc, docx, zip
 */
function upload_project_attachment($file, $brief_id = null) {
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/x-zip-compressed'
    ];
    $maxSize = 10 * 1024 * 1024; // 10MB

    // Validate upload
    $validation = validate_file_upload($file, $allowedTypes, $maxSize);
    if (!$validation['success']) {
        return $validation;
    }

    // Generate unique filename
    $extension = $validation['extension'];
    $filename = 'attachment_' . ($brief_id ?? uniqid()) . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/attachments';

    // Ensure directory exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    return [
        'success' => true,
        'file_path' => '/uploads/attachments/' . $filename,
        'file_name' => $filename,
        'file_size' => $file['size'],
        'mime_type' => $validation['mime_type']
    ];
}

/**
 * Upload message attachment (images and documents)
 * Max size: 5MB, allowed: images, pdf, doc, docx
 */
function upload_message_attachment($file, $user_id) {
    $allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Validate upload
    $validation = validate_file_upload($file, $allowedTypes, $maxSize);
    if (!$validation['success']) {
        return $validation;
    }

    // Generate unique filename
    $extension = $validation['extension'];
    $filename = 'msg_' . $user_id . '_' . uniqid() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/attachments';

    // Ensure directory exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    // Create thumbnail for images
    $thumbPath = null;
    if (strpos($validation['mime_type'], 'image/') === 0) {
        $thumbnailResult = create_thumbnail($destination, $uploadPath . '/thumb_' . $filename, 200, 200);
        $thumbPath = $thumbnailResult ? '/uploads/attachments/thumb_' . $filename : null;
    }

    return [
        'success' => true,
        'file_path' => '/uploads/attachments/' . $filename,
        'thumb_path' => $thumbPath,
        'file_name' => $filename,
        'file_size' => $file['size'],
        'mime_type' => $validation['mime_type']
    ];
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowedTypes, $maxSize) {
    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file upload'];
    }

    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'error' => 'No file was uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'error' => 'File size exceeds maximum limit'];
        default:
            return ['success' => false, 'error' => 'Unknown file upload error'];
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds ' . format_bytes($maxSize)];
    }

    // Validate MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type: ' . $mimeType];
    }

    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Additional security: check for double extensions
    if (substr_count($file['name'], '.') > 1) {
        return ['success' => false, 'error' => 'Invalid filename'];
    }

    // Validate extension matches MIME type
    $validExtensions = get_extensions_for_mime($mimeType);
    if (!in_array($extension, $validExtensions)) {
        return ['success' => false, 'error' => 'File extension does not match file type'];
    }

    return [
        'success' => true,
        'mime_type' => $mimeType,
        'extension' => $extension,
        'size' => $file['size']
    ];
}

/**
 * Get valid extensions for a MIME type
 */
function get_extensions_for_mime($mimeType) {
    $mimeMap = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/zip' => ['zip'],
        'application/x-zip-compressed' => ['zip']
    ];

    return $mimeMap[$mimeType] ?? [];
}

/**
 * Create thumbnail from image
 */
function create_thumbnail($source, $destination, $width, $height) {
    if (!file_exists($source)) {
        return false;
    }

    // Get image info
    $imageInfo = getimagesize($source);
    if (!$imageInfo) {
        return false;
    }

    list($origWidth, $origHeight, $imageType) = $imageInfo;

    // Create image resource from source
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    // Calculate thumbnail dimensions (maintain aspect ratio)
    $ratio = min($width / $origWidth, $height / $origHeight);
    $newWidth = round($origWidth * $ratio);
    $newHeight = round($origHeight * $ratio);

    // Create thumbnail
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Save thumbnail
    $result = false;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($thumbnail, $destination, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($thumbnail, $destination, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($thumbnail, $destination);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($thumbnail, $destination, 85);
            break;
    }

    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($thumbnail);

    return $result;
}

/**
 * Delete uploaded file and its thumbnail
 */
function delete_uploaded_file_enhanced($filePath) {
    if (empty($filePath)) {
        return false;
    }

    $fullPath = ROOT_PATH . $filePath;
    $deleted = false;

    // Delete main file
    if (file_exists($fullPath)) {
        $deleted = unlink($fullPath);
    }

    // Delete thumbnail if exists
    $dirname = dirname($fullPath);
    $basename = basename($fullPath);
    $thumbPath = $dirname . '/thumb_' . $basename;

    if (file_exists($thumbPath)) {
        unlink($thumbPath);
    }

    return $deleted;
}

/**
 * Get file icon based on MIME type
 */
function get_file_icon($mimeType) {
    if (strpos($mimeType, 'image/') === 0) {
        return 'photo';
    } elseif ($mimeType === 'application/pdf') {
        return 'document-text';
    } elseif (strpos($mimeType, 'word') !== false) {
        return 'document';
    } elseif (strpos($mimeType, 'zip') !== false) {
        return 'archive';
    }
    return 'document';
}

/**
 * Upload cover/banner image
 * Max size: 5MB, allowed: jpg, png, webp
 * Recommended size: 1200x400px
 */
function upload_cover_image_enhanced($file, $user_id) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Validate upload
    $validation = validate_file_upload($file, $allowedTypes, $maxSize);
    if (!$validation['success']) {
        return $validation;
    }

    // Generate unique filename
    $extension = $validation['extension'];
    $filename = 'cover_' . $user_id . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/covers';

    // Ensure directory exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    // Optionally resize cover image to max 1200px width while maintaining aspect ratio
    // This helps with page load performance
    $resizeResult = resize_image_max_width($destination, 1200);

    return [
        'success' => true,
        'file_path' => '/uploads/covers/' . $filename,
        'file_name' => $filename
    ];
}
