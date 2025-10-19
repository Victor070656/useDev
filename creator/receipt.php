<?php
require_once '../includes/init.php';
require_once '../includes/transaction_helpers.php';

start_session();
require_auth();
require_role('creator');

$userId = get_user_id();
$transactionId = get_query('id');

if (!$transactionId) {
    set_flash('error', 'Invalid transaction');
    redirect('/creator/transactions.php');
    exit;
}

// Generate receipt data
$receiptData = generate_transaction_receipt($transactionId, $userId, 'creator');

if (!$receiptData) {
    set_flash('error', 'Transaction not found');
    redirect('/creator/transactions.php');
    exit;
}

$transaction = $receiptData['transaction'];
$user = $receiptData['user'];

$pageTitle = 'Receipt - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white rounded-2xl shadow-lg p-12" id="receipt">
            <!-- Header -->
            <div class="flex justify-between items-start mb-12">
                <div>
                    <h1 class="text-4xl font-bold text-purple-600"><?= APP_NAME ?></h1>
                    <p class="text-gray-600 mt-2"><?= APP_URL ?></p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-gray-900">RECEIPT</h2>
                    <p class="text-gray-600 mt-2">Receipt #: <?= $receiptData['receipt_number'] ?></p>
                    <p class="text-gray-600">Date: <?= format_date($transaction['created_at']) ?></p>
                </div>
            </div>

            <!-- Recipient Info -->
            <div class="mb-12">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Received by:</h3>
                <p class="text-lg font-semibold text-gray-900"><?= escape_output($user['first_name'] . ' ' . $user['last_name']) ?></p>
                <p class="text-gray-600"><?= escape_output($user['email']) ?></p>
            </div>

            <!-- Transaction Details -->
            <div class="border-t border-b border-gray-200 py-6 mb-8">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 text-sm font-semibold text-gray-700">Description</th>
                            <th class="text-right py-3 text-sm font-semibold text-gray-700">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="py-4">
                                <p class="font-medium text-gray-900"><?= ucfirst($transaction['transaction_type']) ?></p>
                                <p class="text-sm text-gray-600"><?= escape_output($transaction['brief_title'] ?? 'N/A') ?></p>
                                <?php if ($transaction['contract_id']): ?>
                                    <p class="text-xs text-gray-500 mt-1">Contract ID: <?= $transaction['contract_id'] ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 text-right font-semibold text-gray-900">
                                <?= format_money($transaction['amount']) ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="py-4 text-right font-bold text-gray-900">Total:</td>
                            <td class="py-4 text-right font-bold text-2xl text-purple-600">
                                <?= format_money($transaction['amount']) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Additional Info -->
            <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">Transaction Details</h4>
                    <p class="text-gray-600">Transaction ID: <?= $transaction['id'] ?></p>
                    <p class="text-gray-600">Status: <span class="font-semibold text-green-600"><?= ucfirst($transaction['status']) ?></span></p>
                    <p class="text-gray-600">Payment Method: <?= ucfirst($transaction['payment_provider']) ?></p>
                    <?php if ($transaction['provider_transaction_id']): ?>
                        <p class="text-gray-600">Reference: <?= escape_output($transaction['provider_transaction_id']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">Payment Date</h4>
                    <p class="text-gray-600"><?= format_datetime($transaction['created_at']) ?></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
                <p>This is an official receipt from <?= APP_NAME ?></p>
                <p class="mt-2">For questions about this receipt, please contact support</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="/creator/transactions.php" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300 transition">
                Back to Transactions
            </a>
            <button onclick="window.print()" class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition">
                Print Receipt
            </button>
        </div>
    </div>

    <style>
        @media print {
            body {
                background: white;
            }
            .mt-6 {
                display: none;
            }
        }
    </style>
</body>
</html>
