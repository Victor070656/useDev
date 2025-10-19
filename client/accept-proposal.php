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

$userId = get_user_id();
$proposalId = sanitize_input(get_post('proposal_id'));

if (!$proposalId || !is_numeric($proposalId)) {
    set_flash('error', 'Invalid proposal');
    redirect('/client/');
    exit;
}

$db = get_db_connection();

// Get client profile
$stmt = db_prepare("SELECT id FROM client_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$clientProfile = $result->fetch_assoc();
$stmt->close();

// Get proposal details and verify ownership
$stmt = db_prepare("
    SELECT
        p.*,
        pb.client_profile_id,
        pb.title as brief_title,
        pb.status as brief_status
    FROM proposals p
    JOIN project_briefs pb ON p.project_brief_id = pb.id
    WHERE p.id = ? AND pb.client_profile_id = ?
");
$stmt->bind_param('ii', $proposalId, $clientProfile['id']);
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
    redirect('/client/brief-detail.php?id=' . $proposal['project_brief_id']);
    exit;
}

// Check if brief is still open
if ($proposal['brief_status'] !== 'open') {
    set_flash('error', 'This brief is no longer accepting proposals');
    redirect('/client/brief-detail.php?id=' . $proposal['project_brief_id']);
    exit;
}

// Start transaction
$db->begin_transaction();

try {
    // 1. Accept the proposal
    $stmt = db_prepare("UPDATE proposals SET status = 'accepted' WHERE id = ?");
    $stmt->bind_param('i', $proposalId);
    $stmt->execute();
    $stmt->close();

    // 2. Reject all other proposals for this brief
    $stmt = db_prepare("UPDATE proposals SET status = 'rejected' WHERE project_brief_id = ? AND id != ? AND status = 'pending'");
    $stmt->bind_param('ii', $proposal['project_brief_id'], $proposalId);
    $stmt->execute();
    $stmt->close();

    // 3. Create a contract with platform fee calculation
    $contractAmount = $proposal['proposed_budget'];
    $platformFeePercentage = PLATFORM_FEE_PERCENTAGE; // 10% from config
    $platformFee = round($contractAmount * ($platformFeePercentage / 100));
    $creatorPayout = $contractAmount - $platformFee;

    $stmt = db_prepare("
        INSERT INTO contracts (
            client_profile_id,
            creator_profile_id,
            project_brief_id,
            proposal_id,
            contract_amount,
            platform_fee,
            creator_payout,
            status,
            start_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    $stmt->bind_param('iiiiiii',
        $clientProfile['id'],
        $proposal['creator_profile_id'],
        $proposal['project_brief_id'],
        $proposalId,
        $contractAmount,
        $platformFee,
        $creatorPayout
    );
    $stmt->execute();
    $contractId = $db->insert_id;
    $stmt->close();

    // 4. Update brief status to 'in_progress'
    $stmt = db_prepare("UPDATE project_briefs SET status = 'in_progress' WHERE id = ?");
    $stmt->bind_param('i', $proposal['project_brief_id']);
    $stmt->execute();
    $stmt->close();

    // 5. Log activity
    log_activity($userId, 'proposal_accepted', 'proposal', $proposalId);
    log_activity($proposal['creator_profile_id'], 'proposal_accepted_by_client', 'contract', $contractId);

    // Commit transaction
    $db->commit();

    set_flash('success', 'Proposal accepted! Contract created successfully.');
    redirect('/client/contracts.php');

} catch (Exception $e) {
    // Rollback on error
    $db->rollback();

    set_flash('error', 'Failed to accept proposal. Please try again.');
    redirect('/client/brief-detail.php?id=' . $proposal['project_brief_id']);
}
