<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/paystack_helper.php';
require_once __DIR__ . '/../includes/contract_helpers.php';

// Read the input stream
$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Get Paystack signature from header
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

// Verify webhook signature
$paystack = get_paystack();
if (!$paystack->validateWebhookSignature($input, $signature)) {
    http_response_code(401);
    log_error('Invalid Paystack webhook signature');
    exit('Invalid signature');
}

// Log the webhook event
log_activity(null, 'paystack_webhook_received', 'webhook', null, [
    'event' => $event['event'] ?? 'unknown'
]);

$db = get_db_connection();

// Handle different event types
switch ($event['event']) {
    case 'charge.success':
        handleChargeSuccess($event['data'], $db);
        break;

    case 'transfer.success':
        handleTransferSuccess($event['data'], $db);
        break;

    case 'transfer.failed':
        handleTransferFailed($event['data'], $db);
        break;

    case 'transfer.reversed':
        handleTransferReversed($event['data'], $db);
        break;

    default:
        // Log unhandled events
        log_activity(null, 'paystack_webhook_unhandled', 'webhook', null, [
            'event' => $event['event']
        ]);
        break;
}

http_response_code(200);
exit('Webhook processed');

/**
 * Handle successful charge (payment)
 */
function handleChargeSuccess($data, $db) {
    $reference = $data['reference'] ?? null;

    if (!$reference) {
        log_error('Charge success webhook missing reference');
        return;
    }

    // Get transaction
    $stmt = db_prepare("
        SELECT * FROM transactions
        WHERE provider_transaction_id = ? AND transaction_type = 'payment'
    ");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$transaction) {
        log_error('Transaction not found for reference: ' . $reference);
        return;
    }

    if ($transaction['status'] === 'completed') {
        // Already processed
        return;
    }

    // Start database transaction
    $db->begin_transaction();

    try {
        // Update transaction status
        $stmt = db_prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
        $stmt->bind_param('i', $transaction['id']);
        $stmt->execute();
        $stmt->close();

        // If milestone payment, update milestone
        if ($transaction['milestone_id']) {
            update_milestone_status($transaction['milestone_id'], 'paid');
        }

        // Log activity
        log_activity($transaction['payer_user_id'], 'payment_webhook_processed', 'transaction', $transaction['id']);

        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        log_error('Error processing charge success webhook: ' . $e->getMessage(), ['reference' => $reference]);
    }
}

/**
 * Handle successful transfer (payout)
 */
function handleTransferSuccess($data, $db) {
    $reference = $data['reference'] ?? null;

    if (!$reference) {
        log_error('Transfer success webhook missing reference');
        return;
    }

    // Update payout transaction
    $stmt = db_prepare("
        UPDATE transactions
        SET status = 'completed'
        WHERE provider_transaction_id = ? AND transaction_type = 'payout'
    ");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        log_activity(null, 'payout_webhook_processed', 'transaction', $reference);
    }
}

/**
 * Handle failed transfer
 */
function handleTransferFailed($data, $db) {
    $reference = $data['reference'] ?? null;

    if (!$reference) {
        log_error('Transfer failed webhook missing reference');
        return;
    }

    // Update payout transaction to failed
    $stmt = db_prepare("
        UPDATE transactions
        SET status = 'failed'
        WHERE provider_transaction_id = ? AND transaction_type = 'payout'
    ");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $stmt->close();

    log_error('Payout transfer failed', ['reference' => $reference, 'reason' => $data['reason'] ?? 'unknown']);
}

/**
 * Handle reversed transfer
 */
function handleTransferReversed($data, $db) {
    $reference = $data['reference'] ?? null;

    if (!$reference) {
        log_error('Transfer reversed webhook missing reference');
        return;
    }

    // Update payout transaction to refunded
    $stmt = db_prepare("
        UPDATE transactions
        SET status = 'refunded'
        WHERE provider_transaction_id = ? AND transaction_type = 'payout'
    ");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $stmt->close();

    log_activity(null, 'payout_reversed', 'transaction', $reference);
}
