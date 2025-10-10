<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$pageTitle = 'Settings - ' . APP_NAME;
include_once '../includes/header-client.php';

?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php include_once '../includes/sidebar-client.php'; ?>
    <!-- Dashboard Container -->
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php include_once '../includes/topbar-client.php'; ?>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Settings</h1>
                <p class="text-gray-400 mt-2">Configure your account preferences</p>
            </div>

            <div class="max-w-4xl">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="space-y-6">
                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notifications</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-gray-700">Email notifications for new proposals</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-gray-700">Email notifications for contract updates</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-gray-700">Marketing emails</span>
                                </label>
                            </div>
                        </div>

                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Privacy</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-gray-700">Make my profile visible to creators</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-gray-700">Show my name on posted briefs</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <button class="px-6 py-3 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
