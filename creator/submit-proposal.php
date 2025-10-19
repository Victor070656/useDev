<?php
require_once '../includes/init.php';
require_role('creator');

if (!is_post()) {
    redirect('/browse.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/browse.php');
    exit;
}

$creatorId = get_user_id();
$briefId = sanitize_input(get_post('brief_id'));
$amount = sanitize_input(get_post('amount'));
$timeline = sanitize_input(get_post('timeline'));
$coverLetter = sanitize_input(get_post('cover_letter'));

// Validation
$errors = [];

if (!$briefId || !is_numeric($briefId)) {
    $errors[] = 'Invalid project brief';
}

if (!$amount || !is_numeric($amount) || $amount <= 0) {
    $errors[] = 'Invalid amount';
}

if (!validate_required($timeline)) {
    $errors[] = 'Timeline is required';
}

if (!validate_required($coverLetter)) {
    $errors[] = 'Cover letter is required';
}

if (!empty($errors)) {
    set_flash('error', implode('<br>', $errors));
    redirect('/creator/brief-detail.php?id=' . $briefId);
    exit;
}

$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT id FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $creatorId);
$stmt->execute();
$creatorProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$creatorProfile) {
    set_flash('error', 'Creator profile not found. Please complete your profile first.');
    redirect('/creator/profile.php');
    exit;
}

$creatorProfileId = $creatorProfile['id'];

// Check if brief exists and is open
$stmt = db_prepare("SELECT id, client_profile_id, budget_min, budget_max, status FROM project_briefs WHERE id = ?");
$stmt->bind_param('i', $briefId);
$stmt->execute();
$brief = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$brief) {
    set_flash('error', 'Project brief not found');
    redirect('/browse.php');
    exit;
}

if ($brief['status'] !== 'open') {
    set_flash('error', 'This brief is no longer accepting proposals');
    redirect('/creator/brief-detail.php?id=' . $briefId);
    exit;
}

// Check if user already submitted a proposal
$stmt = db_prepare("SELECT id FROM proposals WHERE project_brief_id = ? AND creator_profile_id = ?");
$stmt->bind_param('ii', $briefId, $creatorProfileId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    set_flash('error', 'You have already submitted a proposal for this project');
    redirect('/creator/brief-detail.php?id=' . $briefId);
    exit;
}

// Convert amount to cents
$amountInCents = (int)($amount * 100);

// Validate amount is within budget range
if ($amountInCents < $brief['budget_min'] || $amountInCents > $brief['budget_max']) {
    set_flash('error', 'Amount must be within the project budget range');
    redirect('/creator/brief-detail.php?id=' . $briefId);
    exit;
}

// Insert proposal
$stmt = db_prepare("
    INSERT INTO proposals (project_brief_id, creator_profile_id, proposed_budget, proposed_timeline, cover_letter, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt->bind_param('iiiss', $briefId, $creatorProfileId, $amountInCents, $timeline, $coverLetter);

if ($stmt->execute()) {
    $proposalId = $db->insert_id;

    // Log activity
    log_activity($creatorId, 'proposal_submitted', 'proposal', $proposalId);

    set_flash('success', 'Proposal submitted successfully! The client will review it soon.');
    redirect('/creator/proposals.php');
} else {
    set_flash('error', 'Failed to submit proposal. Please try again.');
    redirect('/creator/brief-detail.php?id=' . $briefId);
}

$stmt->close();
