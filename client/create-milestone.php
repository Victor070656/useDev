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
$contractId = sanitize_input(get_post('contract_id'));
$title = sanitize_input(get_post('title'));
$description = sanitize_input(get_post('description'));
$amount = sanitize_input(get_post('amount'));
$dueDate = sanitize_input(get_post('due_date'));

// Validate inputs
if (!$contractId || !$title || !$amount) {
    set_flash('error', 'Please fill in all required fields');
    redirect('/client/contracts.php');
    exit;
}

// Convert amount to cents
$amountCents = $amount * 100;

// Verify contract ownership
$contract = get_contract_with_verification($contractId, $userId, 'client');

if (!$contract) {
    set_flash('error', 'Contract not found');
    redirect('/client/contracts.php');
    exit;
}

if ($contract['status'] !== 'active') {
    set_flash('error', 'Can only add milestones to active contracts');
    redirect('/client/contracts.php');
    exit;
}

// Create milestone
$result = create_milestone($contractId, $title, $description, $amountCents, $dueDate);

if ($result['success']) {
    log_activity($userId, 'milestone_created', 'contract_milestone', $result['milestone_id']);
    set_flash('success', 'Milestone created successfully');
} else {
    set_flash('error', $result['error']);
}

redirect('/client/contracts.php?id=' . $contractId);
