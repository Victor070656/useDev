<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();
$db = get_db_connection();

// Get active contracts
$stmt = db_prepare("
    SELECT c.*, u.first_name, u.last_name, pb.title as brief_title
    FROM contracts c
    JOIN users u ON c.creator_id = u.id
    JOIN project_briefs pb ON c.brief_id = pb.id
    WHERE c.client_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$contracts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Contracts - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Contracts</h1>

    <div class="space-y-6">
        <?php if (empty($contracts)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No contracts yet</h3>
                <p class="text-gray-600">Once you accept a proposal, a contract will be created here</p>
            </div>
        <?php else: ?>
            <?php foreach ($contracts as $contract): ?>
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= escape_output($contract['brief_title']) ?></h3>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($contract['first_name'], 0, 1) . substr($contract['last_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <a href="../creator-profile.php?id=<?= $contract['creator_id'] ?>" class="font-semibold text-gray-900 hover:text-purple-600">
                                        <?= escape_output($contract['first_name'] . ' ' . $contract['last_name']) ?>
                                    </a>
                                    <p class="text-sm text-gray-500">Creator</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-purple-600"><?= format_money($contract['amount']) ?></div>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold inline-block mt-2
                                <?= $contract['status'] === 'active' ? 'bg-green-100 text-green-700' : '' ?>
                                <?= $contract['status'] === 'completed' ? 'bg-blue-100 text-blue-700' : '' ?>
                                <?= $contract['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : '' ?>">
                                <?= ucfirst($contract['status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4 border-t border-b border-gray-200 my-4">
                        <div>
                            <span class="text-sm text-gray-500 block mb-1">Start Date</span>
                            <span class="font-semibold text-gray-900"><?= format_date($contract['start_date']) ?></span>
                        </div>
                        <?php if ($contract['end_date']): ?>
                            <div>
                                <span class="text-sm text-gray-500 block mb-1">End Date</span>
                                <span class="font-semibold text-gray-900"><?= format_date($contract['end_date']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="text-sm text-gray-500 block mb-1">Created</span>
                            <span class="font-semibold text-gray-900"><?= time_ago($contract['created_at']) ?></span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="../messages/thread.php?user_id=<?= $contract['creator_id'] ?>" class="px-6 py-2 border border-purple-600 text-purple-600 rounded-full font-semibold hover:bg-purple-50 transition">
                            Message Creator
                        </a>

                        <?php if ($contract['status'] === 'active'): ?>
                            <form method="POST" action="complete-contract.php" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">
                                <button type="submit" onclick="return confirm('Mark this contract as completed?')" class="px-6 py-2 bg-green-600 text-white rounded-full font-semibold hover:bg-green-700 transition">
                                    Mark as Completed
                                </button>
                            </form>

                            <form method="POST" action="cancel-contract.php" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">
                                <button type="submit" onclick="return confirm('Cancel this contract? This action cannot be undone.')" class="px-6 py-2 border border-red-600 text-red-600 rounded-full font-semibold hover:bg-red-50 transition">
                                    Cancel Contract
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
