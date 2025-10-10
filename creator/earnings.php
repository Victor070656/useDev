<?php
require_once '../includes/init.php';
require_auth();
require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile for total earnings
$stmt = db_prepare("SELECT * FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$creatorProfile = $profile;
$stmt->close();

$totalEarnings = $profile['total_earnings'] ?? 0;
$availableForPayout = $totalEarnings; // Simplified - in production, subtract pending/processing

$pageTitle = 'Earnings - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <!-- Topbar -->
        <?php include_once '../includes/topbar.php'; ?>
        <div class="px-12 py-6">

            <h1 class="text-3xl font-bold  mb-8">Earnings & Payouts</h1>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Total Earnings</h3>
                    <p class="text-3xl font-bold"
                        style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <?= format_money($totalEarnings) ?>
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Available for Payout</h3>
                    <p class="text-3xl font-bold text-green-600"><?= format_money($availableForPayout) ?></p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Pending</h3>
                    <p class="text-3xl font-bold text-yellow-600">$0.00</p>
                </div>
            </div>

            <!-- Payout Button -->
            <?php if ($availableForPayout >= MINIMUM_PAYOUT): ?>
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Request Payout</h3>
                            <p class="text-gray-600">You have <?= format_money($availableForPayout) ?> available for
                                withdrawal
                            </p>
                        </div>
                        <button class="px-6 py-3 rounded-full text-white font-semibold"
                            style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                            Request Payout
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-8">
                    <p class="text-gray-600 text-center">Minimum payout amount is <?= format_money(MINIMUM_PAYOUT) ?>. Keep
                        working to reach the threshold!</p>
                </div>
            <?php endif; ?>

            <!-- Payment History -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Payment History</h2>
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>No payment history yet</p>
                </div>
            </div>
        </div>

    </div>
</div>
<?php require_once '../includes/footer2.php'; ?>