<?php
require_once '../includes/init.php';
require_auth();
require_role('creator');

$proposalId = get_query('id', 0);
if (!$proposalId) {
    set_flash('error', 'Proposal not found');
    redirect('/creator/proposals.php');
}

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT id FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$creatorProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$creatorProfile) {
    set_flash('error', 'Creator profile not found');
    redirect('/creator/profile.php');
}

// Get proposal details with brief information
$stmt = db_prepare("
    SELECT
        p.*,
        pb.title as brief_title,
        pb.description as brief_description,
        pb.budget_min,
        pb.budget_max,
        pb.project_type,
        pb.timeline,
        pb.status as brief_status,
        u.first_name as client_first_name,
        u.last_name as client_last_name
    FROM proposals p
    JOIN project_briefs pb ON p.project_brief_id = pb.id
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE p.id = ? AND p.creator_profile_id = ?
");
$stmt->bind_param('ii', $proposalId, $creatorProfile['id']);
$stmt->execute();
$result = $stmt->get_result();
$proposal = $result->fetch_assoc();
$stmt->close();

if (!$proposal) {
    set_flash('error', 'Proposal not found or you do not have permission to view it');
    redirect('/creator/proposals.php');
}

$pageTitle = 'Proposal: ' . escape_output($proposal['brief_title']) . ' - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php require_once __DIR__ . '/../includes/sidebar-creator.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 lg:ml-64">
        <?php include_once '../includes/topbar-creator.php'; ?>

        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="<?= url('/creator/proposals.php') ?>" class="inline-flex items-center text-purple-400 hover:text-purple-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Proposals
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Proposal Header -->
                    <div class="bg-[#1a1825] rounded-2xl shadow-lg p-6 border border-gray-800">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-2xl font-bold text-white mb-2"><?= escape_output($proposal['brief_title']) ?></h1>
                                <p class="text-gray-400">Proposal submitted <?= time_ago($proposal['created_at']) ?></p>
                            </div>
                            <span class="px-4 py-2 rounded-full text-sm font-semibold
                                <?php if ($proposal['status'] === 'pending'): ?>
                                    bg-yellow-500/20 text-yellow-300
                                <?php elseif ($proposal['status'] === 'accepted'): ?>
                                    bg-green-500/20 text-green-300
                                <?php else: ?>
                                    bg-red-500/20 text-red-300
                                <?php endif; ?>">
                                <?= ucfirst($proposal['status']) ?>
                            </span>
                        </div>

                        <!-- Project Brief -->
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-white mb-3">Project Brief</h2>
                            <div class="bg-[#0f0e16] rounded-lg p-4 border border-gray-800">
                                <p class="text-gray-300 whitespace-pre-wrap"><?= escape_output($proposal['brief_description']) ?></p>
                            </div>
                        </div>

                        <!-- Brief Details -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-[#0f0e16] rounded-lg p-4 border border-gray-800">
                                <div class="text-sm text-gray-400 mb-1">Client Budget</div>
                                <div class="text-lg font-semibold text-white">
                                    <?= format_money($proposal['budget_min']) ?> - <?= format_money($proposal['budget_max']) ?>
                                </div>
                            </div>
                            <div class="bg-[#0f0e16] rounded-lg p-4 border border-gray-800">
                                <div class="text-sm text-gray-400 mb-1">Project Type</div>
                                <div class="text-lg font-semibold text-white capitalize"><?= escape_output($proposal['project_type']) ?></div>
                            </div>
                            <?php if ($proposal['timeline']): ?>
                            <div class="bg-[#0f0e16] rounded-lg p-4 border border-gray-800">
                                <div class="text-sm text-gray-400 mb-1">Timeline</div>
                                <div class="text-lg font-semibold text-white"><?= escape_output($proposal['timeline']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Your Proposal -->
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-white mb-3">Your Cover Letter</h2>
                            <div class="bg-[#0f0e16] rounded-lg p-4 border border-gray-800">
                                <p class="text-gray-300 whitespace-pre-wrap"><?= escape_output($proposal['cover_letter']) ?></p>
                            </div>
                        </div>

                        <!-- Proposed Budget & Timeline -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-purple-600/20 to-purple-800/20 rounded-lg p-4 border border-purple-700/50">
                                <div class="text-sm text-purple-300 mb-1">Your Proposed Budget</div>
                                <div class="text-2xl font-bold text-white"><?= format_money($proposal['proposed_budget']) ?></div>
                            </div>
                            <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/20 rounded-lg p-4 border border-blue-700/50">
                                <div class="text-sm text-blue-300 mb-1">Your Proposed Timeline</div>
                                <div class="text-2xl font-bold text-white"><?= escape_output($proposal['proposed_timeline']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Client Info -->
                    <div class="bg-[#1a1825] rounded-2xl shadow-lg p-6 border border-gray-800">
                        <h3 class="text-lg font-semibold text-white mb-4">Client Information</h3>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                <?= strtoupper(substr($proposal['client_first_name'], 0, 1) . substr($proposal['client_last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-semibold text-white"><?= escape_output($proposal['client_first_name'] . ' ' . $proposal['client_last_name']) ?></p>
                                <p class="text-sm text-gray-400">Client</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Card -->
                    <div class="bg-[#1a1825] rounded-2xl shadow-lg p-6 border border-gray-800">
                        <h3 class="text-lg font-semibold text-white mb-4">Proposal Status</h3>

                        <?php if ($proposal['status'] === 'pending'): ?>
                            <div class="text-center py-4">
                                <svg class="w-16 h-16 mx-auto mb-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-gray-300 mb-2">Waiting for client review</p>
                                <p class="text-sm text-gray-400">The client is reviewing your proposal</p>
                            </div>
                        <?php elseif ($proposal['status'] === 'accepted'): ?>
                            <div class="text-center py-4">
                                <svg class="w-16 h-16 mx-auto mb-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-gray-300 mb-2 font-semibold">Proposal Accepted!</p>
                                <p class="text-sm text-gray-400 mb-4">Congratulations! Your proposal was accepted.</p>
                                <a href="<?= url('/creator/contracts.php') ?>" class="inline-block px-6 py-2 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                    View Contract
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <svg class="w-16 h-16 mx-auto mb-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-gray-300 mb-2">Proposal Declined</p>
                                <p class="text-sm text-gray-400 mb-4">The client chose another proposal</p>
                                <a href="<?= url('/creator/briefs.php') ?>" class="inline-block px-6 py-2 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                    Browse More Projects
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <?php if ($proposal['status'] === 'pending'): ?>
                    <div class="bg-[#1a1825] rounded-2xl shadow-lg p-6 border border-gray-800">
                        <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>
                        <a href="<?= url('/creator/brief-detail.php?id=' . $proposal['project_brief_id']) ?>" class="block w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-center font-semibold mb-2">
                            View Project Brief
                        </a>
                        <a href="<?= url('/messages/inbox.php') ?>" class="block w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-center font-semibold">
                            Contact Client
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
