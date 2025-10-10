<?php
require_once '../includes/init.php';
require_auth();
require_role('admin');

$db = get_db_connection();

// Get all users
$result = db_query("
    SELECT u.*, cp.creator_type
    FROM users u
    LEFT JOIN creator_profiles cp ON u.id = cp.user_id
    ORDER BY u.created_at DESC
    LIMIT 50
");
$users = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Users - ' . APP_NAME;
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
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Manage Users</h1>
                <p class="text-gray-400 mt-2 text-lg">View and manage all platform users</p>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= escape_output($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= ucfirst($user['user_type']) ?><?= $user['creator_type'] ? ' (' . ucfirst($user['creator_type']) . ')' : '' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= escape_output($user['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= format_date($user['created_at']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
