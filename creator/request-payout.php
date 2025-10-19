<?php
require_once '../includes/init.php';
require_once '../includes/paystack_helper.php';

start_session();
require_role('creator');

if (!is_post()) {
    redirect('/creator/earnings.php');
    exit;
}

if (!verify_csrf_token(get_post('csrf_token'))) {
    set_flash('error', 'Invalid request');
    redirect('/creator/earnings.php');
    exit;
}

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT * FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    set_flash('error', 'Creator profile not found');
    redirect('/creator/earnings.php');
    exit;
}

// Get available balance (completed payouts that are pending)
$stmt = db_prepare("
    SELECT SUM(amount) as available_balance
    FROM transactions
    WHERE transaction_type = 'payout'
      AND payee_user_id = ?
      AND status = 'pending'
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$availableBalance = (int)($result['available_balance'] ?? 0);
$stmt->close();

// Check minimum payout amount
if ($availableBalance < MINIMUM_PAYOUT) {
    set_flash('error', 'Minimum payout amount is ' . format_money(MINIMUM_PAYOUT));
    redirect('/creator/earnings.php');
    exit;
}

// Get bank details (assuming they're stored in creator profile or separate table)
// For now, we'll need to collect them
$bankCode = sanitize_input(get_post('bank_code'));
$accountNumber = sanitize_input(get_post('account_number'));
$accountName = sanitize_input(get_post('account_name'));

if (!$bankCode || !$accountNumber || !$accountName) {
    set_flash('error', 'Please provide complete bank details');
    redirect('/creator/earnings.php');
    exit;
}

// Initialize Paystack
$paystack = get_paystack();

// Verify account number
$verifyResult = $paystack->resolveAccountNumber($accountNumber, $bankCode);

if (!$verifyResult['success']) {
    set_flash('error', 'Could not verify bank account: ' . $verifyResult['message']);
    redirect('/creator/earnings.php');
    exit;
}

// Create or get transfer recipient
$recipientResult = $paystack->createTransferRecipient(
    'nuban',
    $accountName,
    $accountNumber,
    $bankCode,
    'NGN'
);

if (!$recipientResult['success']) {
    set_flash('error', 'Failed to create recipient: ' . $recipientResult['message']);
    redirect('/creator/earnings.php');
    exit;
}

$recipientCode = $recipientResult['data']['recipient_code'];

// Generate transfer reference
$reference = PaystackHelper::generateReference('PAYOUT');

// Convert amount to kobo
$amountInKobo = PaystackHelper::convertCurrency($availableBalance, 'USD', 'NGN');

// Initiate transfer
$transferResult = $paystack->initiateTransfer(
    'balance',
    $amountInKobo,
    $recipientCode,
    'Creator payout for completed work',
    $reference
);

if (!$transferResult['success']) {
    set_flash('error', 'Failed to initiate transfer: ' . $transferResult['message']);
    redirect('/creator/earnings.php');
    exit;
}

// Update all pending payout transactions
$stmt = db_prepare("
    UPDATE transactions
    SET status = 'completed',
        provider_transaction_id = ?
    WHERE transaction_type = 'payout'
      AND payee_user_id = ?
      AND status = 'pending'
");
$stmt->bind_param('si', $reference, $userId);
$stmt->execute();
$stmt->close();

// Update creator total earnings
$stmt = db_prepare("
    UPDATE creator_profiles
    SET total_earnings = total_earnings + ?
    WHERE user_id = ?
");
$stmt->bind_param('ii', $availableBalance, $userId);
$stmt->execute();
$stmt->close();

// Log activity
log_activity($userId, 'payout_requested', 'transaction', $reference);

set_flash('success', 'Payout request submitted successfully! Funds should arrive within 24 hours.');
redirect('/creator/earnings.php');
