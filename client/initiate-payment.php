<?php
require_once '../includes/init.php';
require_once '../includes/contract_helpers.php';
require_once '../includes/paystack_helper.php';

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
$milestoneId = sanitize_input(get_post('milestone_id')); // Optional: pay for specific milestone

if (!$contractId) {
    set_flash('error', 'Invalid contract');
    redirect('/client/contracts.php');
    exit;
}

$db = get_db_connection();

// Get contract details
$contract = get_contract_with_verification($contractId, $userId, 'client');

if (!$contract) {
    set_flash('error', 'Contract not found');
    redirect('/client/contracts.php');
    exit;
}

if ($contract['status'] !== 'active') {
    set_flash('error', 'Can only pay for active contracts');
    redirect('/client/contracts.php');
    exit;
}

// Get user details
$user = find_user_by_id($userId);

// Determine payment amount
$amount = $contract['contract_amount'];
$paymentType = 'contract';
$entityId = $contractId;

if ($milestoneId) {
    // Pay for specific milestone
    $milestone = get_milestone_by_id($milestoneId, $userId, 'client');

    if (!$milestone || $milestone['contract_id'] != $contractId) {
        set_flash('error', 'Invalid milestone');
        redirect('/client/contracts.php');
        exit;
    }

    if ($milestone['status'] !== 'approved') {
        set_flash('error', 'Only approved milestones can be paid');
        redirect('/client/contracts.php');
        exit;
    }

    $amount = $milestone['amount'];
    $paymentType = 'milestone';
    $entityId = $milestoneId;
}

// Convert USD cents to NGN kobo (you can adjust currency conversion)
// For now, assuming amount is in cents and we need to convert to kobo
$amountInKobo = PaystackHelper::convertCurrency($amount, 'USD', 'NGN');

// Generate unique reference
$reference = PaystackHelper::generateReference('PAY');

// Initialize Paystack
$paystack = get_paystack();

$metadata = [
    'contract_id' => $contractId,
    'payment_type' => $paymentType,
    'entity_id' => $entityId,
    'user_id' => $userId,
    'custom_fields' => [
        [
            'display_name' => 'Contract',
            'variable_name' => 'contract_id',
            'value' => $contractId
        ]
    ]
];

// Initialize transaction
$result = $paystack->initializeTransaction($user['email'], $amountInKobo, $reference, $metadata);

if ($result['success']) {
    // Store transaction in database
    $stmt = db_prepare("
        INSERT INTO transactions (
            transaction_type,
            contract_id,
            milestone_id,
            payer_user_id,
            amount,
            currency,
            payment_provider,
            provider_transaction_id,
            status,
            metadata
        ) VALUES ('payment', ?, ?, ?, ?, 'NGN', 'paystack', ?, 'pending', ?)
    ");

    $metadataJson = json_encode($metadata);
    $stmt->bind_param('iiiiss',
        $contractId,
        $milestoneId,
        $userId,
        $amount,
        $reference,
        $metadataJson
    );

    if ($stmt->execute()) {
        $stmt->close();

        // Log activity
        log_activity($userId, 'payment_initiated', 'transaction', $reference);

        // Redirect to Paystack payment page
        if (isset($result['data']['authorization_url'])) {
            header('Location: ' . $result['data']['authorization_url']);
            exit;
        }
    }

    $stmt->close();
}

set_flash('error', 'Failed to initialize payment: ' . $result['message']);
redirect('/client/contracts.php');
