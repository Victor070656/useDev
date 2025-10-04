<?php
require_once '../includes/init.php';
require_auth();
require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Get all proposals
$stmt = db_prepare("
    SELECT p.*, pb.title as brief_title, pb.budget, pb.status as brief_status
    FROM proposals p
    JOIN project_briefs pb ON p.brief_id = pb.id
    WHERE p.creator_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$proposals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Proposals - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Proposals</h1>

    <div class="space-y-4">
        <?php if (empty($proposals)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Proposals Yet</h3>
                <p class="text-gray-600 mb-6">Browse available briefs and submit your first proposal</p>
                <a href="<?= url('/browse.php') ?>" class="inline-block px-6 py-3 rounded-full text-white font-semibold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Browse Briefs
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($proposals as $proposal): ?>
                <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= escape_output($proposal['brief_title']) ?></h3>
                            <p class="text-gray-600 mb-4"><?= escape_output(truncate($proposal['cover_letter'], 150)) ?></p>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>Proposed: <?= format_money($proposal['proposed_amount']) ?></span>
                                <span>•</span>
                                <span>Submitted: <?= format_date($proposal['created_at']) ?></span>
                            </div>
                        </div>
                        <div>
                            <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'accepted' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800'
                            ];
                            $colorClass = $statusColors[$proposal['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-4 py-2 rounded-full text-sm font-medium <?= $colorClass ?>">
                                <?= ucfirst($proposal['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
