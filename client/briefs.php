<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();
$db = get_db_connection();

// Get all briefs
$stmt = db_prepare("
    SELECT pb.*, COUNT(p.id) as proposal_count
    FROM project_briefs pb
    LEFT JOIN proposals p ON pb.id = p.brief_id
    WHERE pb.client_id = ?
    GROUP BY pb.id
    ORDER BY pb.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$briefs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Briefs - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Project Briefs</h1>
        <a href="<?= url('/client/create-brief.php') ?>" class="px-6 py-3 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
            + Post New Brief
        </a>
    </div>

    <div class="space-y-4">
        <?php if (empty($briefs)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Briefs Posted Yet</h3>
                <p class="text-gray-600 mb-6">Post your first project brief to start receiving proposals from talented creators</p>
                <a href="<?= url('/client/create-brief.php') ?>" class="inline-block px-6 py-3 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Post Your First Brief
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($briefs as $brief): ?>
                <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <a href="<?= url('/client/brief-detail.php?id=' . $brief['id']) ?>" class="text-xl font-semibold text-gray-900 hover:text-purple-600 mb-2 block">
                                <?= escape_output($brief['title']) ?>
                            </a>
                            <p class="text-gray-600 mb-4"><?= escape_output(truncate($brief['description'], 200)) ?></p>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>Budget: <?= format_money($brief['budget']) ?></span>
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

<?php require_once '../includes/footer.php'; ?>
