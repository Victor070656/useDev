<?php
require_once '../includes/init.php';
require_auth();
require_role('admin');

$pageTitle = 'Platform Settings - ' . APP_NAME;
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
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Platform Settings</h1>
                <p class="text-gray-400 mt-2 text-lg">Configure platform-wide settings</p>
            </div>

            <!-- Settings Card -->
            <div class="max-w-4xl">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="space-y-6">
                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Fee</h3>
                            <div class="flex items-center justify-between">
                                <p class="text-gray-600">Current platform fee percentage</p>
                                <span class="text-2xl font-bold text-purple-600"><?= PLATFORM_FEE_PERCENTAGE ?>%</span>
                            </div>
                        </div>

                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Minimum Payout</h3>
                            <div class="flex items-center justify-between">
                                <p class="text-gray-600">Minimum amount for creator payouts</p>
                                <span class="text-2xl font-bold text-purple-600"><?= format_money(MINIMUM_PAYOUT) ?></span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Environment</h3>
                            <div class="flex items-center justify-between">
                                <p class="text-gray-600">Current environment mode</p>
                                <span class="px-4 py-2 rounded-full text-sm font-medium <?= APP_ENV === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= strtoupper(APP_ENV) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
