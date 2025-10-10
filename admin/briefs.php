<?php
require_once '../includes/init.php';
require_auth();
require_role('admin');

$db = get_db_connection();

// Get all briefs
$result = db_query("
    SELECT pb.*, u.first_name, u.last_name, u.email
    FROM project_briefs pb
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    ORDER BY pb.created_at DESC
    LIMIT 50
");
$briefs = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Briefs - ' . APP_NAME;
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
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Manage Briefs</h1>
                <p class="text-gray-400 mt-2 text-lg">View and manage all project briefs</p>
            </div>

            <!-- Briefs List -->
            <div class="space-y-4">
                <?php foreach ($briefs as $brief): ?>
                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= escape_output($brief['title']) ?></h3>
                                <p class="text-gray-600 mb-4"><?= escape_output(truncate($brief['description'], 200)) ?></p>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>Client: <?= escape_output($brief['first_name'] . ' ' . $brief['last_name']) ?></span>
                                    <span>Budget: <?= format_money($brief['budget_min']) ?></span>
                                    <span>Posted: <?= format_date($brief['created_at']) ?></span>
                                </div>
                            </div>
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?= ucfirst(str_replace('_', ' ', $brief['status'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
