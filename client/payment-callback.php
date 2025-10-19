<?php
require_once '../includes/init.php';
require_once '../includes/contract_helpers.php';
require_once '../includes/paystack_helper.php';

start_session();
require_auth();

$reference = sanitize_input(get_query('reference'));

if (!$reference) {
    set_flash('error', 'Invalid payment reference');
    redirect('/client/contracts.php');
    exit;
}

$userId = get_user_id();
$db = get_db_connection();

// Verify transaction with Paystack
$paystack = get_paystack();
$result = $paystack->verifyTransaction($reference);

if (!$result['success']) {
    set_flash('error', 'Payment verification failed: ' . $result['message']);
    redirect('/client/contracts.php');
    exit;
}

$transactionData = $result['data'];

// Check if payment was successful
if ($transactionData['status'] !== 'success') {
    set_flash('error', 'Payment was not successful');
    redirect('/client/contracts.php');
    exit;
}

// Get transaction from database
$stmt = db_prepare("
    SELECT * FROM transactions
    WHERE provider_transaction_id = ? AND payer_user_id = ?
");
$stmt->bind_param('si', $reference, $userId);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$transaction) {
    set_flash('error', 'Transaction not found');
    redirect('/client/contracts.php');
    exit;
}

if ($transaction['status'] === 'completed') {
    set_flash('info', 'Payment has already been processed');
    redirect('/client/contracts.php');
    exit;
}

// Start database transaction
$db->begin_transaction();

try {
    // Update transaction status
    $stmt = db_prepare("
        UPDATE transactions
        SET status = 'completed'
        WHERE id = ?
    ");
    $stmt->bind_param('i', $transaction['id']);
    $stmt->execute();
    $stmt->close();

    // If paying for a milestone, update milestone status
    if ($transaction['milestone_id']) {
        update_milestone_status($transaction['milestone_id'], 'paid');

        // Check if all milestones are paid, then mark contract as completed
        $milestones = get_contract_milestones($transaction['contract_id']);
        $allPaid = true;
        foreach ($milestones as $milestone) {
            if ($milestone['status'] !== 'paid') {
                $allPaid = false;
                break;
            }
        }

        if ($allPaid) {
            $stmt = db_prepare("
                UPDATE contracts
                SET status = 'completed', end_date = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param('i', $transaction['contract_id']);
            $stmt->execute();
            $stmt->close();

            // Update brief status
            $stmt = db_prepare("
                UPDATE project_briefs pb
                JOIN contracts c ON pb.id = c.project_brief_id
                SET pb.status = 'completed'
                WHERE c.id = ?
            ");
            $stmt->bind_param('i', $transaction['contract_id']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Get contract details for creator payout
    $stmt = db_prepare("
        SELECT c.*, crp.user_id as creator_user_id
        FROM contracts c
        JOIN creator_profiles crp ON c.creator_profile_id = crp.id
        WHERE c.id = ?
    ");
    $stmt->bind_param('i', $transaction['contract_id']);
    $stmt->execute();
    $contract = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Create a payout transaction record (will be processed separately)
    $payoutAmount = $transaction['milestone_id']
        ? $transaction['amount'] - round($transaction['amount'] * (PLATFORM_FEE_PERCENTAGE / 100))
        : $contract['creator_payout'];

    $stmt = db_prepare("
        INSERT INTO transactions (
            transaction_type,
            contract_id,
            milestone_id,
            payer_user_id,
            payee_user_id,
            amount,
            currency,
            payment_provider,
            status,
            metadata
        ) VALUES ('payout', ?, ?, ?, ?, ?, 'NGN', 'paystack', 'pending', ?)
    ");

    $metadataJson = json_encode(['payment_transaction_id' => $transaction['id']]);
    $stmt->bind_param('iiiiss',
        $transaction['contract_id'],
        $transaction['milestone_id'],
        $userId,
        $contract['creator_user_id'],
        $payoutAmount,
        $metadataJson
    );
    $stmt->execute();
    $stmt->close();

    // Log activities
    log_activity($userId, 'payment_completed', 'transaction', $transaction['id']);
    log_activity($contract['creator_user_id'], 'payment_received', 'transaction', $transaction['id']);

    // Commit transaction
    $db->commit();

    set_flash('success', 'Payment successful! Funds will be released to the creator.');
    redirect('/client/contracts.php');

} catch (Exception $e) {
    $db->rollback();
    log_error('Payment callback error: ' . $e->getMessage(), ['reference' => $reference]);

    set_flash('error', 'Error processing payment. Please contact support.');
    redirect('/client/contracts.php');
}
