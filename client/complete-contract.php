<?php
require_once '../includes/init.php';
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

$clientId = get_user_id();
$contractId = sanitize_input(get_post('contract_id'));

if (!$contractId || !is_numeric($contractId)) {
    set_flash('error', 'Invalid contract');
    redirect('/client/contracts.php');
    exit;
}

$db = get_db_connection();

// Get contract details and verify ownership
$stmt = db_prepare("
    SELECT c.*, pb.title as brief_title
    FROM contracts c
    JOIN project_briefs pb ON c.brief_id = pb.id
    WHERE c.id = ? AND c.client_id = ?
");
$stmt->bind_param('ii', $contractId, $clientId);
$stmt->execute();
$contract = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$contract) {
    set_flash('error', 'Contract not found');
    redirect('/client/contracts.php');
    exit;
}

// Check if contract is active
if ($contract['status'] !== 'active') {
    set_flash('error', 'Only active contracts can be marked as completed');
    redirect('/client/contracts.php');
    exit;
}

// Update contract status
$stmt = db_prepare("UPDATE contracts SET status = 'completed', end_date = NOW() WHERE id = ?");
$stmt->bind_param('i', $contractId);

if ($stmt->execute()) {
    // Update brief status
    $stmt2 = db_prepare("UPDATE project_briefs SET status = 'completed' WHERE id = ?");
    $stmt2->bind_param('i', $contract['brief_id']);
    $stmt2->execute();
    $stmt2->close();

    // Log activity
    log_activity($clientId, 'contract_completed', 'contract', $contractId);
    log_activity($contract['creator_id'], 'contract_completed_by_client', 'contract', $contractId);

    set_flash('success', 'Contract marked as completed');
} else {
    set_flash('error', 'Failed to update contract status');
}

$stmt->close();
redirect('/client/contracts.php');
