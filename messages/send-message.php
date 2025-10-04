<?php
require_once '../includes/init.php';
require_auth();

if (!is_post()) {
    redirect('/messages/inbox.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/messages/inbox.php');
    exit;
}

$senderId = get_user_id();
$receiverId = sanitize_input(get_post('receiver_id'));
$subject = sanitize_input(get_post('subject'));
$body = sanitize_input(get_post('body'));
$redirectToThread = get_post('redirect_to_thread');

// Validation
if (!validate_required($body)) {
    set_flash('error', 'Message body is required');
    redirect('/messages/inbox.php');
    exit;
}

if (!$receiverId || !is_numeric($receiverId)) {
    set_flash('error', 'Invalid receiver');
    redirect('/messages/inbox.php');
    exit;
}

// Check if receiver exists and is active
$db = get_db_connection();
$stmt = db_prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
$stmt->bind_param('i', $receiverId);
$stmt->execute();
$receiver = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$receiver) {
    set_flash('error', 'Recipient not found');
    redirect('/messages/inbox.php');
    exit;
}

// Insert message
$stmt = db_prepare("
    INSERT INTO messages (sender_id, receiver_id, subject, body, is_read, created_at)
    VALUES (?, ?, ?, ?, 0, NOW())
");
$stmt->bind_param('iiss', $senderId, $receiverId, $subject, $body);

if ($stmt->execute()) {
    set_flash('success', 'Message sent successfully');

    // Redirect to thread if specified, otherwise to inbox
    if ($redirectToThread) {
        redirect('/messages/thread.php?user_id=' . $receiverId);
    } else {
        redirect('/messages/inbox.php');
    }
} else {
    set_flash('error', 'Failed to send message');
    redirect('/messages/inbox.php');
}

$stmt->close();
