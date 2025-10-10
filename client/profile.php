<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();
$user = find_user_by_id($userId);

$pageTitle = 'My Profile - ' . APP_NAME;
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
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">My Profile</h1>
                <p class="text-gray-400 mt-2">Manage your account information</p>
            </div>

            <div class="max-w-4xl">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <p class="text-lg text-gray-900"><?= escape_output($user['first_name']) ?></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <p class="text-lg text-gray-900"><?= escape_output($user['last_name']) ?></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <p class="text-lg text-gray-900"><?= escape_output($user['email']) ?></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                            <p class="text-lg text-gray-900">Client</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Member Since</label>
                            <p class="text-lg text-gray-900"><?= format_date($user['created_at']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
