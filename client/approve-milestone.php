<?php
require_once '../includes/init.php';
require_once '../includes/contract_helpers.php';

start_session();
require_role('client');

if (!is_post()) {
    redirect('/client/contracts.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/client/contracts.php');
    exit;
}

$userId = get_user_id();
$milestoneId = sanitize_input(get_post('milestone_id'));

if (!$milestoneId) {
    set_flash('error', 'Invalid milestone');
    redirect('/client/contracts.php');
    exit;
}

// Get milestone and verify client ownership
$milestone = get_milestone_by_id($milestoneId, $userId, 'client');

if (!$milestone) {
    set_flash('error', 'Milestone not found or access denied');
    redirect('/client/contracts.php');
    exit;
}

if ($milestone['status'] !== 'submitted') {
    set_flash('error', 'Only submitted milestones can be approved');
    redirect('/client/contracts.php');
    exit;
}

// Update milestone status to approved
$result = update_milestone_status($milestoneId, 'approved');

if ($result['success']) {
    log_activity($userId, 'milestone_approved', 'contract_milestone', $milestoneId);
    set_flash('success', 'Milestone approved. Payment will be processed.');
} else {
    set_flash('error', $result['error']);
}

redirect('/client/contracts.php');
