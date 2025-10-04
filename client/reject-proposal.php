<?php
require_once '../includes/init.php';
require_role('client');

if (!is_post()) {
    redirect('/client/');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/client/');
    exit;
}

$clientId = get_user_id();
$proposalId = sanitize_input(get_post('proposal_id'));

if (!$proposalId || !is_numeric($proposalId)) {
    set_flash('error', 'Invalid proposal');
    redirect('/client/');
    exit;
}

$db = get_db_connection();

// Get proposal details and verify ownership
$stmt = db_prepare("
    SELECT
        p.*,
        pb.client_id
    FROM proposals p
    JOIN project_briefs pb ON p.brief_id = pb.id
    WHERE p.id = ? AND pb.client_id = ?
");
$stmt->bind_param('ii', $proposalId, $clientId);
$stmt->execute();
$proposal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$proposal) {
    set_flash('error', 'Proposal not found');
    redirect('/client/');
    exit;
}

// Check if proposal is pending
if ($proposal['status'] !== 'pending') {
    set_flash('error', 'This proposal has already been processed');
    redirect('/client/brief-detail.php?id=' . $proposal['brief_id']);
    exit;
}

// Reject the proposal
$stmt = db_prepare("UPDATE proposals SET status = 'rejected' WHERE id = ?");
$stmt->bind_param('i', $proposalId);

if ($stmt->execute()) {
    log_activity($clientId, 'proposal_rejected', 'proposal', $proposalId);
    set_flash('success', 'Proposal rejected');
} else {
    set_flash('error', 'Failed to reject proposal');
}

$stmt->close();
redirect('/client/brief-detail.php?id=' . $proposal['brief_id']);
