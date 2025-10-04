<?php
require_once '../includes/init.php';

// Require authentication and admin role
require_auth();
require_role('admin');

$db = get_db_connection();

// Get platform-wide stats
$stats = [
    'total_users' => 0,
    'total_creators' => 0,
    'total_clients' => 0,
    'total_briefs' => 0,
    'total_contracts' => 0,
    'total_revenue' => 0
];

// Total users
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
$row = $result->fetch_assoc();
$stats['total_users'] = $row['count'];

// Total creators
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'creator' AND is_active = TRUE");
$row = $result->fetch_assoc();
$stats['total_creators'] = $row['count'];

// Total clients
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'client' AND is_active = TRUE");
$row = $result->fetch_assoc();
$stats['total_clients'] = $row['count'];

// Total briefs
$result = $db->query("SELECT COUNT(*) as count FROM project_briefs");
$row = $result->fetch_assoc();
$stats['total_briefs'] = $row['count'];

// Total contracts
$result = $db->query("SELECT COUNT(*) as count FROM contracts");
$row = $result->fetch_assoc();
$stats['total_contracts'] = $row['count'];

// Total platform revenue (sum of platform fees)
$result = $db->query("SELECT COALESCE(SUM(platform_fee), 0) as total FROM contracts");
$row = $result->fetch_assoc();
$stats['total_revenue'] = $row['total'];

// Recent activity logs
$recentActivity = [];
$result = $db->query("
    SELECT al.*, u.email, u.user_type
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $recentActivity[] = $row;
}

// Recent users
$recentUsers = [];
$result = $db->query("
    SELECT u.*,
           CASE
               WHEN u.user_type = 'creator' THEN cp.display_name
               ELSE CONCAT(u.first_name, ' ', u.last_name)
           END as display_name
    FROM users u
    LEFT JOIN creator_profiles cp ON u.id = cp.user_id AND u.user_type = 'creator'
    ORDER BY u.created_at DESC
    LIMIT 8
");
while ($row = $result->fetch_assoc()) {
    $recentUsers[] = $row;
}

// Active briefs
$activeBriefs = [];
$result = $db->query("
    SELECT pb.*,
           CONCAT(u.first_name, ' ', u.last_name) as client_name,
           (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count
    FROM project_briefs pb
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE pb.status = 'open'
    ORDER BY pb.created_at DESC
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    $activeBriefs[] = $row;
}

$pageTitle = 'Admin Dashboard - ' . APP_NAME;
require_once '../includes/header.php';
?>

<!-- Dashboard Container -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Admin Dashboard</h1>
            <p class="text-gray-600 mt-2 text-lg">Platform overview and management tools</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_users']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Total Users</p>
                <a href="<?= url('/admin/users.php') ?>" class="text-xs text-purple-600 hover:text-purple-700 mt-2 inline-block">View all →</a>
            </div>

            <!-- Total Creators -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-cyan-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_creators']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Creators</p>
                <a href="<?= url('/admin/creators.php') ?>" class="text-xs text-purple-600 hover:text-purple-700 mt-2 inline-block">Manage →</a>
            </div>

            <!-- Total Clients -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_clients']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Clients</p>
                <a href="<?= url('/admin/clients.php') ?>" class="text-xs text-purple-600 hover:text-purple-700 mt-2 inline-block">Manage →</a>
            </div>

            <!-- Total Briefs -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-yellow-400 to-orange-500">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_briefs']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Project Briefs</p>
                <a href="<?= url('/admin/briefs.php') ?>" class="text-xs text-purple-600 hover:text-purple-700 mt-2 inline-block">View all →</a>
            </div>

            <!-- Total Contracts -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_contracts']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Contracts</p>
                <a href="<?= url('/admin/contracts.php') ?>" class="text-xs text-purple-600 hover:text-purple-700 mt-2 inline-block">View all →</a>
            </div>

            <!-- Platform Revenue -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-pink-500 to-rose-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= format_money($stats['total_revenue']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Platform Revenue</p>
                <a href="<?= url('/admin/revenue.php') ?>" class="text-xs text-purple-600 hover:text-purple-700 mt-2 inline-block">View details →</a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Recent Activity -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Activity</h2>

                    <?php if (empty($recentActivity)): ?>
                        <div class="text-center py-12">
                            <p class="text-gray-500">No recent activity</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">
                                            <span class="font-semibold"><?= escape_output($activity['email'] ?? 'System') ?></span>
                                            <span class="text-gray-600"> - <?= escape_output($activity['action']) ?></span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1"><?= time_ago($activity['created_at']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Active Briefs -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Active Briefs</h2>

                    <?php if (empty($activeBriefs)): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">No active briefs</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($activeBriefs as $brief): ?>
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1"><?= escape_output($brief['title']) ?></h3>
                                    <p class="text-sm text-gray-600 mb-2">by <?= escape_output($brief['client_name']) ?></p>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-purple-600"><?= $brief['proposal_count'] ?> proposals</span>
                                        <span class="text-gray-500"><?= time_ago($brief['created_at']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Links -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Quick Links</h2>

                    <div class="space-y-3">
                        <a href="<?= url('/admin/users.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Manage Users
                        </a>

                        <a href="<?= url('/admin/briefs.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Manage Briefs
                        </a>

                        <a href="<?= url('/admin/contracts.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            View Contracts
                        </a>

                        <a href="<?= url('/admin/transactions.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Transactions
                        </a>

                        <a href="<?= url('/admin/settings.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Platform Settings
                        </a>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Users</h2>

                    <?php if (empty($recentUsers)): ?>
                        <p class="text-gray-500 text-sm">No users yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recentUsers as $user): ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate"><?= escape_output($user['display_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= ucfirst($user['user_type']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
