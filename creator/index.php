<?php
require_once '../includes/init.php';

// Require authentication and creator role
require_auth();
require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT * FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$creatorProfile = $result->fetch_assoc();
$stmt->close();

if (!$creatorProfile) {
    set_flash('error', 'Creator profile not found. Please complete your profile setup.');
    redirect('/creator/profile.php');
}

$creatorProfileId = $creatorProfile['id'];

// Get stats
$stats = [
    'proposals' => 0,
    'active_projects' => 0,
    'earnings' => 0,
    'rating' => $creatorProfile['rating_average'],
    'total_projects' => $creatorProfile['total_projects']
];

// Count proposals
$stmt = db_prepare("SELECT COUNT(*) as count FROM proposals WHERE creator_profile_id = ?");
$stmt->bind_param('i', $creatorProfileId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['proposals'] = $row['count'];
$stmt->close();

// Count active projects (contracts)
$stmt = db_prepare("SELECT COUNT(*) as count FROM contracts WHERE creator_profile_id = ? AND status = 'active'");
$stmt->bind_param('i', $creatorProfileId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['active_projects'] = $row['count'];
$stmt->close();

// Get total earnings
$stats['earnings'] = $creatorProfile['total_earnings'];

// Get recent proposals
$stmt = db_prepare("
    SELECT p.*, pb.title as brief_title, pb.budget_min, pb.budget_max, pb.project_type
    FROM proposals p
    JOIN project_briefs pb ON p.project_brief_id = pb.id
    WHERE p.creator_profile_id = ?
    ORDER BY p.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $creatorProfileId);
$stmt->execute();
$result = $stmt->get_result();
$recentProposals = [];
while ($row = $result->fetch_assoc()) {
    $recentProposals[] = $row;
}
$stmt->close();

$pageTitle = 'Creator Dashboard - ' . APP_NAME;
require_once '../includes/header.php';
?>

<!-- Dashboard Container -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-purple-50/20 to-pink-50/20 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                Welcome back, <?= escape_output(get_user_first_name()) ?>! 👋
            </h1>
            <p class="text-gray-600 mt-2 text-lg">Here's what's happening with your creator account today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Proposals -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['proposals'] ?></h3>
                <p class="text-sm text-gray-600 mt-1">Total Proposals</p>
            </div>

            <!-- Active Projects -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-cyan-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['active_projects'] ?></h3>
                <p class="text-sm text-gray-600 mt-1">Active Projects</p>
            </div>

            <!-- Total Earnings -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= format_money($stats['earnings']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">Total Earnings</p>
            </div>

            <!-- Rating -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-yellow-400 to-orange-500">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['rating'], 1) ?> ★</h3>
                <p class="text-sm text-gray-600 mt-1">Average Rating</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Recent Proposals -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Proposals</h2>

                    <?php if (empty($recentProposals)): ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-4 text-gray-500">No proposals yet</p>
                            <a href="<?= url('/briefs.php') ?>" class="mt-4 inline-block px-6 py-3 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                Browse Briefs
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recentProposals as $proposal): ?>
                                <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-300 transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="font-semibold text-gray-900"><?= escape_output($proposal['brief_title']) ?></h3>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $proposal['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($proposal['status'] === 'accepted' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') ?>">
                                            <?= ucfirst($proposal['status']) ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3"><?= excerpt($proposal['cover_letter'], 100) ?></p>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Proposed: <?= format_money($proposal['proposed_budget']) ?></span>
                                        <span class="text-gray-500"><?= time_ago($proposal['created_at']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="<?= url('/creator/proposals.php') ?>" class="text-purple-600 hover:text-purple-700 font-semibold">
                                View All Proposals →
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
                        <a href="<?= url('/creator/profile.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Edit Portfolio
                        </a>

                        <a href="<?= url('/briefs.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Browse Briefs
                        </a>

                        <a href="<?= url('/creator/earnings.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            View Earnings
                        </a>

                        <a href="<?= url('/creator/courses.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Create Course
                        </a>

                        <a href="<?= url('/creator/products.php') ?>" class="block w-full px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-xl hover:from-purple-100 hover:to-pink-100 transition font-medium text-center">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Sell Products
                        </a>
                    </div>
                </div>

                <!-- Profile Completion -->
                <div class="mt-6 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 text-white">
                    <h3 class="font-bold text-lg mb-2">Boost Your Profile</h3>
                    <p class="text-white/90 text-sm mb-4">Complete your profile to attract more clients and increase your visibility.</p>
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm">Profile Completion</span>
                            <span class="text-sm font-semibold">
                                <?php
                                $completionScore = 0;
                                if ($creatorProfile['profile_image']) $completionScore += 20;
                                if ($creatorProfile['bio']) $completionScore += 20;
                                if ($creatorProfile['headline']) $completionScore += 20;
                                if ($creatorProfile['hourly_rate']) $completionScore += 20;
                                if ($creatorProfile['location']) $completionScore += 20;
                                echo $completionScore;
                                ?>%
                            </span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2">
                            <div class="bg-white h-2 rounded-full transition-all" style="width: <?= $completionScore ?>%"></div>
                        </div>
                    </div>
                    <a href="<?= url('/creator/profile.php') ?>" class="block w-full px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-gray-100 transition font-semibold text-center">
                        Complete Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
