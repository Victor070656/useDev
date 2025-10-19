<?php
require_once '../includes/init.php';
require_once '../includes/contract_helpers.php';

start_session();
require_role('creator');

if (!is_post()) {
    redirect('/creator/contracts.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/creator/contracts.php');
    exit;
}

$userId = get_user_id();
$milestoneId = sanitize_input(get_post('milestone_id'));

if (!$milestoneId) {
    set_flash('error', 'Invalid milestone');
    redirect('/creator/contracts.php');
    exit;
}

// Get milestone and verify creator ownership
$milestone = get_milestone_by_id($milestoneId, $userId, 'creator');

if (!$milestone) {
    set_flash('error', 'Milestone not found or access denied');
    redirect('/creator/contracts.php');
    exit;
}

if ($milestone['status'] !== 'in_progress' && $milestone['status'] !== 'pending') {
    set_flash('error', 'This milestone cannot be submitted');
    redirect('/creator/contracts.php');
    exit;
}

// Update milestone status to submitted
$result = update_milestone_status($milestoneId, 'submitted');

if ($result['success']) {
    log_activity($userId, 'milestone_submitted', 'contract_milestone', $milestoneId);
    set_flash('success', 'Milestone submitted for approval');
} else {
    set_flash('error', $result['error']);
}

redirect('/creator/contracts.php');
