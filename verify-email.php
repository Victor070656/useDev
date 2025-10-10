<?php
require_once 'includes/init.php';

// Get token from URL
$token = get_query('token');

if (!$token) {
    set_flash('error', 'Invalid verification link');
    redirect('/login.php');
    exit;
}

// Verify email
if (verify_user_email($token)) {
    set_flash('success', 'Email verified successfully! Please login.');
} else {
    set_flash('error', 'Invalid or expired verification link');
}

redirect('/login.php');
