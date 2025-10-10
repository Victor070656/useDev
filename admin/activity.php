<?php
require_once '../includes/init.php';
require_auth();
require_role('admin');

$db = get_db_connection();

// Get activity logs
$result = db_query("
    SELECT al.*, u.first_name, u.last_name, u.email
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 100
");
$activities = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Activity Logs - ' . APP_NAME;
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
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Activity Logs</h1>
                <p class="text-gray-400 mt-2 text-lg">Monitor user activity and platform events</p>
            </div>

            <!-- Activity Table -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($activities as $activity): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= escape_output($activity['first_name'] . ' ' . $activity['last_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= escape_output($activity['action']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= escape_output($activity['ip_address']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= format_datetime($activity['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
