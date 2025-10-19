<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();
$db = get_db_connection();

// Get client profile
$stmt = db_prepare("SELECT id FROM client_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$clientProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$clientProfile) {
    set_flash('error', 'Client profile not found');
    redirect('/client/profile.php');
}

$clientProfileId = $clientProfile['id'];

// Get all proposals for all client's briefs
$stmt = db_prepare("
    SELECT
        p.*,
        pb.title as brief_title,
        pb.id as brief_id,
        pb.budget_min,
        pb.budget_max,
        cp.display_name as creator_name,
        cp.headline as creator_headline,
        cp.hourly_rate as creator_rate,
        cp.rating_average as creator_rating,
        u.first_name,
        u.last_name
    FROM proposals p
    JOIN project_briefs pb ON p.project_brief_id = pb.id
    JOIN creator_profiles cp ON p.creator_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE pb.client_profile_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param('i', $clientProfileId);
$stmt->execute();
$proposals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group proposals by status
$groupedProposals = [
    'pending' => [],
    'accepted' => [],
    'rejected' => []
];

foreach ($proposals as $proposal) {
    $groupedProposals[$proposal['status']][] = $proposal;
}

$pageTitle = 'Received Proposals - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-gray-50">
    <?php require_once __DIR__ . '/../includes/sidebar-client.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 lg:ml-64">
        <?php include_once '../includes/topbar-client.php'; ?>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Received Proposals</h1>
                <p class="text-gray-600">Review and manage proposals from creators</p>
            </div>

            <?php if (empty($proposals)): ?>
                <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-gray-100">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Proposals Yet</h3>
                    <p class="text-gray-600 mb-6">Post a project brief to start receiving proposals from creators</p>
                    <a href="<?= url('/client/create-brief.php') ?>" class="inline-block px-6 py-3 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        Post a Project
                    </a>
                </div>
            <?php else: ?>
                <!-- Tabs -->
                <div class="mb-6" x-data="{ tab: 'pending' }">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="tab = 'pending'" :class="tab === 'pending' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Pending (<?= count($groupedProposals['pending']) ?>)
                            </button>
                            <button @click="tab = 'accepted'" :class="tab === 'accepted' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Accepted (<?= count($groupedProposals['accepted']) ?>)
                            </button>
                            <button @click="tab = 'rejected'" :class="tab === 'rejected' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Rejected (<?= count($groupedProposals['rejected']) ?>)
                            </button>
                        </nav>
                    </div>

                    <!-- Pending Proposals -->
                    <div x-show="tab === 'pending'" class="mt-6 space-y-4">
                        <?php if (empty($groupedProposals['pending'])): ?>
                            <div class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-100">
                                <p class="text-gray-600">No pending proposals</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groupedProposals['pending'] as $proposal): ?>
                                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                                    <?= strtoupper(substr($proposal['first_name'], 0, 1) . substr($proposal['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?= escape_output($proposal['creator_name']) ?></h3>
                                                    <p class="text-sm text-gray-600"><?= escape_output($proposal['creator_headline']) ?></p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500 mb-2">
                                                For: <a href="<?= url('/client/brief-detail.php?id=' . $proposal['brief_id']) ?>" class="text-purple-600 hover:text-purple-700 font-medium">
                                                    <?= escape_output($proposal['brief_title']) ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="flex items-center text-sm text-gray-600 mb-1">
                                                <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                <span class="font-semibold"><?= number_format($proposal['creator_rating'], 1) ?></span>
                                            </div>
                                            <p class="text-sm text-gray-500"><?= time_ago($proposal['created_at']) ?></p>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-gray-700 line-clamp-3"><?= escape_output($proposal['cover_letter']) ?></p>
                                    </div>

                                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                        <div class="flex items-center space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-600">Proposed Budget</p>
                                                <p class="text-lg font-bold text-gray-900"><?= format_money($proposal['proposed_budget']) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Timeline</p>
                                                <p class="text-lg font-semibold text-gray-900"><?= escape_output($proposal['proposed_timeline']) ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="<?= url('/client/brief-detail.php?id=' . $proposal['brief_id'] . '#proposal-' . $proposal['id']) ?>" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Accepted Proposals -->
                    <div x-show="tab === 'accepted'" class="mt-6 space-y-4">
                        <?php if (empty($groupedProposals['accepted'])): ?>
                            <div class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-100">
                                <p class="text-gray-600">No accepted proposals</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groupedProposals['accepted'] as $proposal): ?>
                                <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold bg-green-600">
                                                    <?= strtoupper(substr($proposal['first_name'], 0, 1) . substr($proposal['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?= escape_output($proposal['creator_name']) ?></h3>
                                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Accepted</span>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                For: <a href="<?= url('/client/brief-detail.php?id=' . $proposal['brief_id']) ?>" class="text-purple-600 hover:text-purple-700 font-medium">
                                                    <?= escape_output($proposal['brief_title']) ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                        <div class="flex items-center space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-600">Contract Budget</p>
                                                <p class="text-lg font-bold text-gray-900"><?= format_money($proposal['proposed_budget']) ?></p>
                                            </div>
                                        </div>
                                        <a href="<?= url('/client/contracts.php') ?>" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition">
                                            View Contract
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Rejected Proposals -->
                    <div x-show="tab === 'rejected'" class="mt-6 space-y-4">
                        <?php if (empty($groupedProposals['rejected'])): ?>
                            <div class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-100">
                                <p class="text-gray-600">No rejected proposals</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groupedProposals['rejected'] as $proposal): ?>
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 opacity-75">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold bg-gray-500">
                                                    <?= strtoupper(substr($proposal['first_name'], 0, 1) . substr($proposal['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?= escape_output($proposal['creator_name']) ?></h3>
                                                    <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">Rejected</span>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                For: <?= escape_output($proposal['brief_title']) ?>
                                            </p>
                                        </div>
                                        <p class="text-sm text-gray-400"><?= time_ago($proposal['created_at']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
