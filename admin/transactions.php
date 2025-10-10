<?php
require_once '../includes/init.php';
require_auth();
require_role('admin');

$db = get_db_connection();

// Get transactions
$result = db_query("
    SELECT t.*, u.first_name, u.last_name, u.email
    FROM transactions t
    JOIN users u ON t.payee_user_id = u.id
    ORDER BY t.created_at DESC
    LIMIT 100
");
$transactions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Transactions - ' . APP_NAME;
include_once '../includes/header-admin.php';

?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php include_once '../includes/sidebar-admin.php'; ?>
    <!-- Dashboard Container -->
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php
        include_once '../includes/topbar-admin.php';
        ?>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Transactions</h1>
                <p class="text-gray-400 mt-2 text-lg">View all platform transactions</p>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No transactions yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= escape_output($t['first_name'] . ' ' . $t['last_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= ucfirst($t['type']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= format_money($t['amount']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $t['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= format_datetime($t['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
