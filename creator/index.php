<?php
require_once '../includes/init.php';

// Require authentication and creator role
// require_auth();
// require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT * FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$creatorProfile = $result->fetch_assoc();
$stmt->close();

// if (!$creatorProfile) {
//     set_flash('error', 'Creator profile not found. Please complete your profile setup.');
//     redirect('/creator/profile.php');
// }

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
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main content column -->
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">

        <!-- Topbar -->
        <?php include_once '../includes/topbar.php'; ?>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">

                <!-- Stats cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-[#111019] to-[#15121d] border border-gray-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Proposals</p>
                                <p class="text-2xl font-semibold text-white"><?= (int) $stats['proposals'] ?></p>
                            </div>
                            <div class="p-3 rounded-lg" style="background: linear-gradient(135deg,#240046,#7103a0);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 .707.293l5.414 5.414a1 1 0 .293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Total proposals you submitted</p>
                    </div>

                    <div class="bg-gradient-to-br from-[#0b1724] to-[#0f1b2b] border border-gray-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Active Projects</p>
                                <p class="text-2xl font-semibold text-white"><?= (int) $stats['active_projects'] ?></p>
                            </div>
                            <div class="p-3 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Projects currently in progress</p>
                    </div>

                    <div class="bg-gradient-to-br from-[#081b10] to-[#07201a] border border-gray-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Earnings</p>
                                <p class="text-2xl font-semibold text-white">
                                    <?= escape_output(format_money($stats['earnings'])) ?>
                                </p>
                            </div>
                            <div class="p-3 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Total earnings to date</p>
                    </div>

                    <div class="bg-gradient-to-br from-[#2b1b07] to-[#321b08] border border-gray-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Rating</p>
                                <p class="text-2xl font-semibold text-white"><?= number_format($stats['rating'], 1) ?> ★
                                </p>
                            </div>
                            <div class="p-3 rounded-lg bg-gradient-to-br from-yellow-400 to-orange-500">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Average client rating</p>
                    </div>
                </div>

                <!-- Recent Proposals + Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-[#0f1116] border border-gray-800 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold">Recent Proposals</h2>
                            <a href="<?= url('/creator/proposals.php') ?>" class="text-sm text-purple-400">View all</a>
                        </div>

                        <?php if (empty($recentProposals)): ?>
                            <div class="text-center py-12 text-gray-400">
                                <p>No recent proposals</p>
                                <a href="<?= url('/briefs.php') ?>"
                                    class="mt-4 inline-block px-4 py-2 rounded-full bg-gradient-to-r from-[#240046] to-[#7103a0] text-white">Browse
                                    Briefs</a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentProposals as $proposal): ?>
                                    <div class="p-4 border border-gray-800 rounded-lg hover:border-purple-600 transition">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h3 class="font-semibold text-white">
                                                    <?= escape_output($proposal['brief_title']) ?>
                                                </h3>
                                                <div class="text-xs text-gray-400 mt-1">
                                                    <?= escape_output($proposal['project_type']) ?> •
                                                    <?= escape_output($proposal['budget_min']) ?> -
                                                    <?= escape_output($proposal['budget_max']) ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div
                                                    class="text-sm <?= $proposal['status'] === 'pending' ? 'text-yellow-300' : ($proposal['status'] === 'accepted' ? 'text-green-400' : 'text-gray-400') ?>">
                                                    <?= ucfirst($proposal['status']) ?>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-2"><?= time_ago($proposal['created_at']) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-300 mt-3"><?= excerpt($proposal['cover_letter'], 120) ?></p>
                                        <div class="mt-3 flex items-center justify-between text-sm text-gray-400">
                                            <div>Proposed: <?= format_money($proposal['proposed_budget']) ?></div>
                                            <a href="<?= url('/creator/proposal_view.php?id=' . (int) $proposal['id']) ?>"
                                                class="text-purple-400">View →</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <aside class="bg-[#0f1116] border border-gray-800 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="<?= url('/creator/profile.php') ?>"
                                class="block w-full text-left px-3 py-2 rounded-md hover:bg-[#1b1730]">Edit Profile</a>
                            <a href="<?= url('/briefs.php') ?>"
                                class="block w-full text-left px-3 py-2 rounded-md hover:bg-[#1b1730]">Browse Briefs</a>
                            <a href="<?= url('/creator/earnings.php') ?>"
                                class="block w-full text-left px-3 py-2 rounded-md hover:bg-[#1b1730]">View Earnings</a>
                            <a href="<?= url('/creator/courses.php') ?>"
                                class="block w-full text-left px-3 py-2 rounded-md hover:bg-[#1b1730]">Create Course</a>
                            <a href="<?= url('/creator/products.php') ?>"
                                class="block w-full text-left px-3 py-2 rounded-md hover:bg-[#1b1730]">Sell Product</a>
                        </div>

                        <!-- Profile completion -->
                        <div class="mt-6 bg-gradient-to-br from-[#240046] to-[#3b1054] rounded-lg p-4 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-semibold">Profile Completion</div>
                                <?php
                                $completionScore = 0;
                                if ($creatorProfile['profile_image'])
                                    $completionScore += 20;
                                if ($creatorProfile['bio'])
                                    $completionScore += 20;
                                if ($creatorProfile['headline'])
                                    $completionScore += 20;
                                if ($creatorProfile['hourly_rate'])
                                    $completionScore += 20;
                                if ($creatorProfile['location'])
                                    $completionScore += 20;
                                ?>
                                <div class="text-sm font-semibold"><?= (int) $completionScore ?>%</div>
                            </div>
                            <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                                <div class="h-2 bg-white rounded-full" style="width: <?= (int) $completionScore ?>%">
                                </div>
                            </div>
                            <a href="<?= url('/creator/profile.php') ?>"
                                class="mt-3 block text-center bg-white text-[#3b1054] rounded-md px-3 py-2 font-semibold">Complete
                                Profile</a>
                        </div>
                    </aside>
                </div>

                <!-- Optional: add charts, activity feed, or other widgets below -->
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>