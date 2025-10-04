<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();
$briefId = get_query('id');

if (!$briefId) {
    set_flash('error', 'Brief not found');
    redirect('/client/briefs.php');
    exit;
}

$db = get_db_connection();

// Get brief details
$stmt = db_prepare("SELECT * FROM project_briefs WHERE id = ? AND client_id = ?");
$stmt->bind_param('ii', $briefId, $userId);
$stmt->execute();
$brief = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$brief) {
    set_flash('error', 'Brief not found or access denied');
    redirect('/client/briefs.php');
    exit;
}

// Get proposals
$stmt = db_prepare("
    SELECT p.*, u.first_name, u.last_name, cp.hourly_rate, cp.rating_average
    FROM proposals p
    JOIN users u ON p.creator_id = u.id
    LEFT JOIN creator_profiles cp ON p.creator_id = cp.user_id
    WHERE p.brief_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param('i', $briefId);
$stmt->execute();
$proposals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = escape_output($brief['title']) . ' - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= escape_output($brief['title']) ?></h1>
        <div class="flex items-center space-x-6 text-sm text-gray-500 mb-6">
            <span>Budget: <strong><?= format_money($brief['budget']) ?></strong></span>
            <span>Timeline: <strong><?= escape_output($brief['timeline']) ?></strong></span>
            <span>Posted: <?= format_date($brief['created_at']) ?></span>
        </div>
        <p class="text-gray-600 whitespace-pre-line"><?= escape_output($brief['description']) ?></p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Proposals (<?= count($proposals) ?>)</h2>
        <?php if (empty($proposals)): ?>
            <div class="text-center py-12 text-gray-500">No proposals yet</div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($proposals as $proposal): ?>
                    <div class="border border-gray-200 rounded-xl p-6 <?= $proposal['status'] === 'accepted' ? 'bg-green-50 border-green-300' : '' ?> <?= $proposal['status'] === 'rejected' ? 'bg-gray-50 border-gray-300' : '' ?>">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($proposal['first_name'], 0, 1) . substr($proposal['last_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <a href="../creator-profile.php?id=<?= $proposal['creator_id'] ?>" class="hover:text-purple-600">
                                            <?= escape_output($proposal['first_name'] . ' ' . $proposal['last_name']) ?>
                                        </a>
                                    </h3>
                                    <?php if ($proposal['hourly_rate']): ?>
                                        <p class="text-sm text-gray-500">$<?= $proposal['hourly_rate'] ?>/hr</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-purple-600"><?= format_money($proposal['amount']) ?></div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold mt-2 inline-block
                                    <?= $proposal['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' ?>
                                    <?= $proposal['status'] === 'accepted' ? 'bg-green-100 text-green-700' : '' ?>
                                    <?= $proposal['status'] === 'rejected' ? 'bg-gray-100 text-gray-700' : '' ?>">
                                    <?= ucfirst($proposal['status']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">
                                <strong>Timeline:</strong> <?= escape_output($proposal['timeline']) ?>
                            </p>
                            <p class="text-sm text-gray-600 mb-2">
                                <strong>Submitted:</strong> <?= time_ago($proposal['created_at']) ?>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Cover Letter:</h4>
                            <p class="text-gray-700 whitespace-pre-line"><?= escape_output($proposal['cover_letter']) ?></p>
                        </div>

                        <?php if ($proposal['status'] === 'pending'): ?>
                            <div class="flex items-center space-x-3 pt-4 border-t border-gray-200">
                                <form method="POST" action="accept-proposal.php" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
                                    <button type="submit" onclick="return confirm('Accept this proposal? All other proposals will be rejected and a contract will be created.')" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-full font-semibold hover:opacity-90 transition">
                                        Accept Proposal
                                    </button>
                                </form>

                                <form method="POST" action="reject-proposal.php" class="inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
                                    <button type="submit" onclick="return confirm('Reject this proposal?')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-full font-semibold hover:bg-gray-50 transition">
                                        Reject
                                    </button>
                                </form>

                                <a href="../messages/thread.php?user_id=<?= $proposal['creator_id'] ?>" class="px-6 py-2 border border-purple-600 text-purple-600 rounded-full font-semibold hover:bg-purple-50 transition">
                                    Message Creator
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
