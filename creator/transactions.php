<?php
require_once '../includes/init.php';
require_once '../includes/transaction_helpers.php';

start_session();
require_auth();
require_role('creator');

$userId = get_user_id();

// Handle CSV export
if (get_query('export') === 'csv') {
    export_transactions_csv($userId, 'creator', 'my-transactions-' . date('Y-m-d') . '.csv');
}

// Get earnings summary
$earnings = get_creator_earnings_summary($userId);

// Get transaction history
$page = max(1, (int)get_query('page', 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$transactions = get_user_transactions($userId, 'creator', $perPage, $offset);

$pageTitle = 'Transaction History - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php include_once '../includes/topbar.php'; ?>

        <div class="px-12 py-6">
            <!-- Page Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Transaction History</h1>
                    <p class="text-gray-400 mt-2">View all your earnings and payouts</p>
                </div>
                <a href="?export=csv" class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition">
                    Export CSV
                </a>
            </div>

            <!-- Earnings Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-2xl p-6 text-white">
                    <h3 class="text-sm font-semibold opacity-80 mb-2">Total Earned</h3>
                    <p class="text-3xl font-bold"><?= format_money($earnings['total_earned']) ?></p>
                </div>

                <div class="bg-gradient-to-br from-green-600 to-green-800 rounded-2xl p-6 text-white">
                    <h3 class="text-sm font-semibold opacity-80 mb-2">Available Balance</h3>
                    <p class="text-3xl font-bold"><?= format_money($earnings['available_balance']) ?></p>
                </div>

                <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 text-white">
                    <h3 class="text-sm font-semibold opacity-80 mb-2">Total Withdrawn</h3>
                    <p class="text-3xl font-bold"><?= format_money($earnings['total_withdrawn']) ?></p>
                </div>

                <div class="bg-gradient-to-br from-gray-700 to-gray-900 rounded-2xl p-6 text-white">
                    <h3 class="text-sm font-semibold opacity-80 mb-2">Total Contracts</h3>
                    <p class="text-3xl font-bold"><?= $earnings['total_contracts'] ?></p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Recent Transactions</h2>
                </div>

                <?php if (empty($transactions)): ?>
                    <div class="p-12 text-center">
                        <p class="text-gray-500">No transactions yet</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= format_date($transaction['created_at']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                                <?= $transaction['transaction_type'] === 'payment' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                                <?= ucfirst($transaction['transaction_type']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?= escape_output($transaction['brief_title'] ?? 'N/A') ?>
                                            <?php if ($transaction['first_name']): ?>
                                                <br>
                                                <span class="text-xs text-gray-500">From: <?= escape_output($transaction['first_name'] . ' ' . $transaction['last_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            <?= format_money($transaction['amount']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                                <?php
                                                    echo match($transaction['status']) {
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                        'refunded' => 'bg-gray-100 text-gray-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                ?>">
                                                <?= ucfirst($transaction['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="receipt.php?id=<?= $transaction['id'] ?>" class="text-purple-600 hover:text-purple-900">
                                                View Receipt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-400">
                                    Previous
                                </span>
                            <?php endif; ?>

                            <span class="text-sm text-gray-700">Page <?= $page ?></span>

                            <?php if (count($transactions) === $perPage): ?>
                                <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Next
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-400">
                                    Next
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
