<?php
require_once '../includes/init.php';

// Require authentication and client role
require_auth();
require_role('client');

$userId = get_user_id();
$db = get_db_connection();

// Get client profile
$stmt = db_prepare("SELECT * FROM client_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$clientProfile = $result->fetch_assoc();
$stmt->close();

if (!$clientProfile) {
    // Create client profile if it doesn't exist
    $stmt = db_prepare("INSERT INTO client_profiles (user_id) VALUES (?)");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();

    // Fetch the newly created profile
    $stmt = db_prepare("SELECT * FROM client_profiles WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $clientProfile = $result->fetch_assoc();
    $stmt->close();
}

$clientProfileId = $clientProfile['id'];

// Get stats
$stats = [
    'active_briefs' => 0,
    'proposals_received' => 0,
    'hired_creators' => 0,
    'total_spent' => $clientProfile['total_spent']
];

// Count active briefs
$stmt = db_prepare("SELECT COUNT(*) as count FROM project_briefs WHERE client_profile_id = ? AND status = 'open'");
$stmt->bind_param('i', $clientProfileId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['active_briefs'] = $row['count'];
$stmt->close();

// Count proposals received
$stmt = db_prepare("
    SELECT COUNT(*) as count
    FROM proposals p
    JOIN project_briefs pb ON p.project_brief_id = pb.id
    WHERE pb.client_profile_id = ? AND p.status = 'pending'
");
$stmt->bind_param('i', $clientProfileId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['proposals_received'] = $row['count'];
$stmt->close();

// Count hired creators
$stmt = db_prepare("SELECT COUNT(*) as count FROM contracts WHERE client_profile_id = ? AND status IN ('active', 'completed')");
$stmt->bind_param('i', $clientProfileId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['hired_creators'] = $row['count'];
$stmt->close();

// Get recent briefs
$stmt = db_prepare("
    SELECT pb.*,
           (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count
    FROM project_briefs pb
    WHERE pb.client_profile_id = ?
    ORDER BY pb.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $clientProfileId);
$stmt->execute();
$result = $stmt->get_result();
$recentBriefs = [];
while ($row = $result->fetch_assoc()) {
    $recentBriefs[] = $row;
}
$stmt->close();

$user = find_user_by_id($userId);
$pageTitle = 'Client Dashboard - ' . APP_NAME;
include_once '../includes/header-client.php';

?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php include_once '../includes/sidebar-client.php'; ?>
    <!-- Dashboard Container -->
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php
        include_once '../includes/topbar-client.php';
        ?>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                Welcome back, <?= escape_output($user['first_name']) ?>! 🚀
            </h1>
            <p class="text-gray-600 mt-2 text-lg">Manage your projects and find the perfect talent for your needs.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Active Briefs -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['active_briefs'] ?></h3>
                <p class="text-sm text-gray-600 mt-1">Active Briefs</p>
            </div>

            <!-- Proposals Received -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-cyan-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['proposals_received'] ?></h3>
                <p class="text-sm text-gray-600 mt-1">New Proposals</p>
            </div>

            <!-- Hired Creators -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['hired_creators'] ?></h3>
                <p class="text-sm text-gray-600 mt-1">Hired Creators</p>
            </div>

            <!-- Total Spent -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= format_money($stats['total_spent']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Total Spent</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Recent Briefs -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Your Briefs</h2>
                        <a href="<?= url('/client/post-brief.php') ?>" class="px-4 py-2 rounded-lg text-white font-semibold transition hover:scale-105 text-sm" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                            Post New Brief
                        </a>
                    </div>

                    <?php if (empty($recentBriefs)): ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-4 text-gray-500">No briefs posted yet</p>
                            <a href="<?= url('/client/post-brief.php') ?>" class="mt-4 inline-block px-6 py-3 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                Post Your First Brief
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recentBriefs as $brief): ?>
                                <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-300 transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="font-semibold text-gray-900"><?= escape_output($brief['title']) ?></h3>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $brief['status'] === 'open' ? 'bg-green-100 text-green-700' : ($brief['status'] === 'draft' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700') ?>">
                                            <?= ucfirst($brief['status']) ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3"><?= excerpt($brief['description'], 100) ?></p>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">
                                            <?php if ($brief['budget_min'] && $brief['budget_max']): ?>
                                                Budget: <?= format_money($brief['budget_min']) ?> - <?= format_money($brief['budget_max']) ?>
                                            <?php endif; ?>
                                        </span>
                                        <div class="flex items-center gap-4">
                                            <span class="text-purple-600 font-semibold"><?= $brief['proposal_count'] ?> proposals</span>
                                            <span class="text-gray-500"><?= time_ago($brief['created_at']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="<?= url('/client/briefs.php') ?>" class="text-purple-600 hover:text-purple-700 font-semibold">
                                View All Briefs →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Quick Actions</h2>

                    <div class="space-y-3">
                        <a href="<?= url('/client/post-brief.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Post New Brief
                        </a>

                        <a href="<?= url('/client/proposals.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            View Proposals
                        </a>

                        <a href="<?= url('/browse.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Browse Creators
                        </a>

                        <a href="<?= url('/client/contracts.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Active Contracts
                        </a>

                        <a href="<?= url('/client/messages.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            Messages
                        </a>
                    </div>
                </div>

                <!-- How it Works -->
                <div class="mt-6 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 text-white">
                    <h3 class="font-bold text-lg mb-2">How It Works</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-bold mr-3">1</span>
                            <p class="text-white/90">Post your project brief with details and budget</p>
                        </div>
                        <div class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-bold mr-3">2</span>
                            <p class="text-white/90">Review proposals from qualified creators</p>
                        </div>
                        <div class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-bold mr-3">3</span>
                            <p class="text-white/90">Hire the best match and start your project</p>
                        </div>
                        <div class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-bold mr-3">4</span>
                            <p class="text-white/90">Pay securely through milestones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once '../includes/footer2.php'; ?>
