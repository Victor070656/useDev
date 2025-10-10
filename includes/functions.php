<?php

/**
 * Security Helper Functions
 */

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    if ($data === null || $data === '') {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function escape_output($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Session Management
 */

function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');

        if (APP_ENV === 'production') {
            ini_set('session.cookie_secure', 1);
        }

        session_start();
    }
}

function is_authenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function require_auth($redirect = '/login.php') {
    if (!is_authenticated()) {
        redirect($redirect);
        exit;
    }
}

function require_role($role, $redirect = '/index.php') {
    if (!is_authenticated() || $_SESSION['user_type'] !== $role) {
        redirect($redirect);
        exit;
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_type() {
    return $_SESSION['user_type'] ?? null;
}

function get_user_name() {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Flash Messages
 */

function set_flash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function get_flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

function has_flash($key) {
    return isset($_SESSION['flash'][$key]);
}

/**
 * Routing & Navigation
 */

function redirect($path, $statusCode = 302) {
    $url = $path;
    if (strpos($path, 'http') !== 0) {
        if (strpos($path, '/') === 0) {
            $url = APP_URL . $path;
        } else {
            $url = APP_URL . '/' . $path;
        }
    }
    header("Location: {$url}", true, $statusCode);
    exit;
}

function url($path = '') {
    return APP_URL . $path;
}

function asset($path) {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function current_url() {
    return $_SERVER['REQUEST_URI'];
}

/**
 * Request Helpers
 */

function request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

function is_post() {
    return request_method() === 'POST';
}

function is_get() {
    return request_method() === 'GET';
}

function get_input($key, $default = null) {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function get_post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

function get_query($key, $default = null) {
    return $_GET[$key] ?? $default;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function set_old_input() {
    $_SESSION['old'] = $_POST;
}

function clear_old_input() {
    unset($_SESSION['old']);
}

/**
 * Validation Helpers
 */

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function validate_required($value) {
    return !empty(trim($value));
}

function validate_min_length($value, $min) {
    return strlen($value) >= $min;
}

function validate_max_length($value, $max) {
    return strlen($value) <= $max;
}

/**
 * File Upload Helpers
 */

function upload_file($file, $directory = 'uploads', $allowed_types = null) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if ($allowed_types && !in_array($mimeType, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/' . $directory;

    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }

    return [
        'success' => true,
        'filename' => $filename,
        'path' => '/uploads/' . $directory . '/' . $filename
    ];
}

/**
 * Delete an uploaded file
 */
function delete_uploaded_file($filePath) {
    if (empty($filePath)) {
        return false;
    }

    $fullPath = __DIR__ . '/..' . $filePath;

    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }

    return false;
}

/**
 * Format bytes to human readable format
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Upload profile picture (2MB limit, images only)
 */
function upload_profile_picture($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }

    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        return ['success' => false, 'error' => 'File size exceeds 2MB'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and WEBP allowed'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = __DIR__ . '/../uploads/profiles';

    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $destination = $uploadPath . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    return [
        'success' => true,
        'file_path' => '/uploads/profiles/' . $filename,
        'file_name' => $filename
    ];
}

/**
 * Upload portfolio image (5MB limit, images only)
 */
function upload_portfolio_image($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        return ['success' => false, 'error' => 'File size exceeds 5MB'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Only images allowed'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = __DIR__ . '/../uploads/portfolio';

    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $destination = $uploadPath . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file'];
    }

    return [
        'success' => true,
        'file_path' => '/uploads/portfolio/' . $filename,
        'file_name' => $filename
    ];
}

/**
 * Date & Time Helpers
 */

function format_date($date, $format = 'M d, Y') {
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

function format_datetime($datetime, $format = 'M d, Y g:i A') {
    if (!$datetime) return '';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date($format, $timestamp);
}

function time_ago($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';

    return format_date($timestamp);
}

/**
 * Number & Currency Helpers
 */

function format_money($cents, $currency = 'USD') {
    $amount = $cents / 100;

    switch ($currency) {
        case 'USD':
            return '$' . number_format($amount, 2);
        case 'EUR':
            return '€' . number_format($amount, 2);
        default:
            return number_format($amount, 2) . ' ' . $currency;
    }
}

function format_number($number, $decimals = 0) {
    return number_format($number, $decimals);
}

/**
 * String Helpers
 */

function slugify($text) {
    if ($text === null || $text === '') {
        return 'n-a';
    }
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

function truncate($text, $length = 100, $suffix = '...') {
    if ($text === null || $text === '') {
        return '';
    }
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

function excerpt($text, $length = 150) {
    if ($text === null || $text === '') {
        return '';
    }
    $text = strip_tags($text);
    return truncate($text, $length);
}

/**
 * Array Helpers
 */

function array_get($array, $key, $default = null) {
    if (!is_array($array)) {
        return $default;
    }

    if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (is_array($array) && isset($array[$segment])) {
            $array = $array[$segment];
        } else {
            return $default;
        }
    }

    return $array;
}

/**
 * Logging
 */

function log_activity($user_id, $action, $entity_type = null, $entity_id = null, $metadata = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $metadataJson = $metadata ? json_encode($metadata) : null;

    $stmt = db_prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, metadata)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param('issssss', $user_id, $action, $entity_type, $entity_id, $ip, $userAgent, $metadataJson);
        $stmt->execute();
        $stmt->close();
    }
}

function log_error($message, $context = []) {
    $logFile = LOG_PATH . '/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextJson = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] {$message}{$contextJson}\n";
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Email Helpers
 */

function send_email($to, $subject, $body, $headers = []) {
    $defaultHeaders = [
        'From' => MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
        'Reply-To' => MAIL_FROM,
        'X-Mailer' => 'PHP/' . phpversion(),
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=UTF-8'
    ];

    $headers = array_merge($defaultHeaders, $headers);
    $headerString = '';

    foreach ($headers as $key => $value) {
        $headerString .= "{$key}: {$value}\r\n";
    }

    return mail($to, $subject, $body, $headerString);
}

/**
 * Pagination Helper
 */

function paginate($total, $perPage, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($totalPages, $currentPage));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_page' => $currentPage - 1,
        'next_page' => $currentPage + 1
    ];
}

/**
 * JSON Response Helpers
 */

function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function json_success($message = 'Success', $data = []) {
    json_response([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

function json_error($message = 'Error', $statusCode = 400) {
    json_response([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * User Model Functions
 */

function find_user_by_email($email) {
    $stmt = db_prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    return null;
}

function find_user_by_id($id) {
    $stmt = db_prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    return null;
}

function email_exists($email) {
    return find_user_by_email($email) !== null;
}

function verify_password($password, $hash) {
    // Plain text password comparison - NO HASHING
    return $password === $hash;
}

function hash_password($password) {
    // Plain text password - NO HASHING
    return $password;
}

function create_user($data) {
    $passwordHash = $data['password']; // Store plain text password
    $verificationToken = bin2hex(random_bytes(32));
    $fullname = $data['first_name'] . ' ' . $data['last_name'];

    $stmt = db_prepare("
        INSERT INTO users (email, password_hash, first_name, last_name, user_type, email_verification_token)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param('ssssss',
            $data['email'],
            $passwordHash,
            $data['first_name'],
            $data['last_name'],
            $data['user_type'],
            $verificationToken
        );

        if ($stmt->execute()) {
            $userId = db_last_insert_id();
            $stmt->close();

            // Create creator profile if user_type is creator
            if ($data['user_type'] === 'creator' && isset($data['creator_type'])) {
                $stmtProfile = db_prepare("
                    INSERT INTO creator_profiles (user_id, creator_type, display_name)
                    VALUES (?, ?, ?)
                ");
                if ($stmtProfile) {
                    $stmtProfile->bind_param('iss', $userId, $data['creator_type'], $fullname);
                    $stmtProfile->execute();
                    $stmtProfile->close();
                }
            }

            return ['success' => true, 'user_id' => $userId, 'verification_token' => $verificationToken];
        }
        $stmt->close();
    }

    return ['success' => false];
}

function update_last_login($userId) {
    $stmt = db_prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
}

function verify_user_email($token) {
    $stmt = db_prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE email_verification_token = ?");
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }
    return false;
}

function create_password_reset_token($email) {
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = db_prepare("UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param('sss', $token, $expiresAt, $email);
        $stmt->execute();
        $stmt->close();
        return $token;
    }
    return null;
}

function reset_user_password($token, $password) {
    $passwordHash = hash_password($password);

    $stmt = db_prepare("
        UPDATE users
        SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL
        WHERE password_reset_token = ? AND password_reset_expires > NOW()
    ");

    if ($stmt) {
        $stmt->bind_param('ss', $passwordHash, $token);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    }
    return false;
}
