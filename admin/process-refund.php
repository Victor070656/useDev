<?php
require_once '../includes/init.php';
require_once '../includes/paystack_helper.php';

start_session();
require_role('admin');

if (!is_post()) {
    redirect('/admin/transactions.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/admin/transactions.php');
    exit;
}

$userId = get_user_id();
$transactionId = sanitize_input(get_post('transaction_id'));
$refundAmount = sanitize_input(get_post('refund_amount')); // Optional: partial refund
$reason = sanitize_input(get_post('reason'));

if (!$transactionId) {
    set_flash('error', 'Invalid transaction');
    redirect('/admin/transactions.php');
    exit;
}

$db = get_db_connection();

// Get transaction details
$stmt = db_prepare("
    SELECT * FROM transactions
    WHERE id = ? AND transaction_type = 'payment' AND status = 'completed'
");
$stmt->bind_param('i', $transactionId);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$transaction) {
    set_flash('error', 'Transaction not found or cannot be refunded');
    redirect('/admin/transactions.php');
    exit;
}

// Determine refund amount
$amountToRefund = $refundAmount ? ($refundAmount * 100) : $transaction['amount'];

if ($amountToRefund > $transaction['amount']) {
    set_flash('error', 'Refund amount cannot exceed transaction amount');
    redirect('/admin/transactions.php');
    exit;
}

// Initialize Paystack
$paystack = get_paystack();

// Convert amount to kobo
$amountInKobo = PaystackHelper::convertCurrency($amountToRefund, 'USD', 'NGN');

// Process refund
$result = $paystack->createRefund($transaction['provider_transaction_id'], $amountInKobo);

if (!$result['success']) {
    set_flash('error', 'Failed to process refund: ' . $result['message']);
    redirect('/admin/transactions.php');
    exit;
}

$db->begin_transaction();

try {
    // Create refund transaction record
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
            provider_transaction_id,
            status,
            metadata
        ) VALUES ('refund', ?, ?, ?, ?, ?, 'NGN', 'paystack', ?, 'completed', ?)
    ");

    $metadata = json_encode([
        'original_transaction_id' => $transactionId,
        'reason' => $reason,
        'refunded_by' => $userId
    ]);

    $refundReference = 'REFUND_' . $transaction['provider_transaction_id'];

    $stmt->bind_param('iiiisis',
        $transaction['contract_id'],
        $transaction['milestone_id'],
        $transaction['payer_user_id'],
        $transaction['payee_user_id'],
        $amountToRefund,
        $refundReference,
        $metadata
    );
    $stmt->execute();
    $stmt->close();

    // Update original transaction status
    if ($amountToRefund == $transaction['amount']) {
        // Full refund
        $stmt = db_prepare("UPDATE transactions SET status = 'refunded' WHERE id = ?");
        $stmt->bind_param('i', $transactionId);
        $stmt->execute();
        $stmt->close();
    }

    // If milestone was paid, revert it
    if ($transaction['milestone_id']) {
        $stmt = db_prepare("UPDATE contract_milestones SET status = 'approved' WHERE id = ?");
        $stmt->bind_param('i', $transaction['milestone_id']);
        $stmt->execute();
        $stmt->close();
    }

    // Log activity
    log_activity($userId, 'refund_processed', 'transaction', $transactionId, [
        'amount' => $amountToRefund,
        'reason' => $reason
    ]);
    log_activity($transaction['payer_user_id'], 'refund_received', 'transaction', $transactionId);

    $db->commit();

    set_flash('success', 'Refund processed successfully');
    redirect('/admin/transactions.php');

} catch (Exception $e) {
    $db->rollback();
    log_error('Refund processing error: ' . $e->getMessage(), ['transaction_id' => $transactionId]);

    set_flash('error', 'Error processing refund. Please try again.');
    redirect('/admin/transactions.php');
}
