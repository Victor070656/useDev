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
$result = $stmt->get_result();
$clientProfile = $result->fetch_assoc();
$stmt->close();

// Get all briefs
$stmt = db_prepare("
    SELECT pb.*, COUNT(p.id) as proposal_count
    FROM project_briefs pb
    LEFT JOIN proposals p ON pb.id = p.project_brief_id
    WHERE pb.client_profile_id = ?
    GROUP BY pb.id
    ORDER BY pb.created_at DESC
");
$stmt->bind_param('i', $clientProfile['id']);
$stmt->execute();
$briefs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Briefs - ' . APP_NAME;
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

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1
                        class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        My Project Briefs</h1>
                    <p class="text-gray-400 mt-2">Manage your posted project briefs and proposals</p>
                </div>
                <a href="<?= url('/client/create-brief.php') ?>"
                    class="px-6 py-3 rounded-full text-white font-semibold hover:scale-105 transition-transform"
                    style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    + Post New Brief
                </a>
            </div>

            <!-- Briefs List -->
            <div class="space-y-4">
                <?php if (empty($briefs)): ?>
                    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Briefs Posted Yet</h3>
                        <p class="text-gray-600 mb-6">Post your first project brief to start receiving proposals from
                            talented creators</p>
                        <a href="<?= url('/client/create-brief.php') ?>"
                            class="inline-block px-6 py-3 rounded-full text-white font-semibold hover:scale-105 transition-transform"
                            style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                            Post Your First Brief
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($briefs as $brief): ?>
                        <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <a href="<?= url('/client/brief-detail.php?id=' . $brief['id']) ?>"
                                        class="text-xl font-semibold text-gray-900 hover:text-purple-600 mb-2 block">
                                        <?= escape_output($brief['title']) ?>
                                    </a>
                                    <p class="text-gray-600 mb-4"><?= escape_output(truncate($brief['description'], 200)) ?></p>
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span>Budget: <?= format_money($brief['budget_min']) ?> -
                                            <?= format_money($brief['budget_max']) ?></span>
                                        <span>•</span>
                                        <span><?= $brief['proposal_count'] ?> Proposals</span>
                                        <span>•</span>
                                        <span>Posted: <?= format_date($brief['created_at']) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <?php
                                    $statusColors = [
                                        'open' => 'bg-green-100 text-green-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-gray-100 text-gray-800',
                                        'closed' => 'bg-red-100 text-red-800'
                                    ];
                                    $colorClass = $statusColors[$brief['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-4 py-2 rounded-full text-sm font-medium <?= $colorClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $brief['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>