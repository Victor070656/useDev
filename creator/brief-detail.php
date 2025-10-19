<?php
require_once '../includes/init.php';
require_role('creator');

$briefId = get_query('id');

if (!$briefId) {
    redirect('/creator/briefs.php');
    exit;
}

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT id FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$creatorProfile = $result->fetch_assoc();
$stmt->close();

// Get brief details
$stmt = db_prepare("
    SELECT
        pb.*,
        u.first_name,
        u.last_name,
        u.email,
        cp.id as client_profile_id,
        (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count
    FROM project_briefs pb
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE pb.id = ? AND pb.status != 'draft'
");
$stmt->bind_param('i', $briefId);
$stmt->execute();
$brief = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$brief) {
    set_flash('error', 'Brief not found');
    redirect('/creator/briefs.php');
    exit;
}

// Check if user already submitted a proposal
$stmt = db_prepare("SELECT id, status, proposed_budget, cover_letter, created_at FROM proposals WHERE project_brief_id = ? AND creator_profile_id = ?");
$stmt->bind_param('ii', $briefId, $creatorProfile['id']);
$stmt->execute();
$existingProposal = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get required skills - parse from required_skills column
$skills = [];
if (!empty($brief['required_skills'])) {
    $skillsArray = explode(',', $brief['required_skills']);
    foreach ($skillsArray as $skill) {
        $skills[] = ['skill_name' => trim($skill)];
    }
}

$pageTitle = $brief['title'] . ' - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php include_once '../includes/topbar.php'; ?>
        <div class="px-12 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Brief Details -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                            <a href="briefs.php" class="hover:text-purple-600">Browse Projects</a>
                            <span>/</span>
                            <span class="text-gray-900"><?= escape_output($brief['title']) ?></span>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= escape_output($brief['title']) ?></h1>

                        <div class="flex flex-wrap items-center gap-4 mb-6">
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
                                <?= ucfirst($brief['project_type']) ?>
                            </span>
                            <span class="px-3 py-1 <?= $brief['status'] === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?> rounded-full text-sm font-semibold">
                                <?= ucfirst($brief['status']) ?>
                            </span>
                            <span class="text-sm text-gray-600">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Posted <?= time_ago($brief['created_at']) ?>
                            </span>
                            <span class="text-sm text-gray-600">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <?= $brief['proposal_count'] ?> proposal<?= $brief['proposal_count'] !== 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <div class="prose max-w-none">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                            <p class="text-gray-700 whitespace-pre-line"><?= escape_output($brief['description']) ?></p>
                        </div>
                    </div>

                    <!-- Your Proposal -->
                    <?php if ($existingProposal): ?>
                        <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-2xl shadow-lg p-6 text-white">
                            <h2 class="text-2xl font-bold mb-4">Your Proposal</h2>

                            <div class="bg-white/10 backdrop-blur rounded-xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <span class="text-sm text-white/70">Proposed Amount</span>
                                        <p class="text-2xl font-bold"><?= format_money($existingProposal['proposed_budget']) ?></p>
                                    </div>
                                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm font-semibold">
                                        <?= ucfirst($existingProposal['status']) ?>
                                    </span>
                                </div>

                                <div>
                                    <span class="text-sm text-white/70 block mb-2">Cover Letter</span>
                                    <p class="text-white/90 whitespace-pre-line"><?= escape_output($existingProposal['cover_letter']) ?></p>
                                </div>
                            </div>

                            <p class="text-sm text-white/70">Submitted <?= time_ago($existingProposal['created_at']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Budget & Timeline -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Project Details</h3>

                        <div class="space-y-4">
                            <div>
                                <span class="text-sm text-gray-600 block mb-1">Budget</span>
                                <p class="text-2xl font-bold text-purple-600">
                                    <?= format_money($brief['budget_min']) ?> - <?= format_money($brief['budget_max']) ?>
                                </p>
                            </div>

                            <?php if ($brief['timeline']): ?>
                                <div>
                                    <span class="text-sm text-gray-600 block mb-1">Timeline</span>
                                    <p class="text-lg font-semibold text-gray-900"><?= escape_output($brief['timeline']) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($skills)): ?>
                                <div>
                                    <span class="text-sm text-gray-600 block mb-2">Required Skills</span>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($skills as $skill): ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                                <?= escape_output($skill['skill_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">About the Client</h3>

                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($brief['first_name'], 0, 1) . substr($brief['last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    <?= escape_output($brief['first_name'] . ' ' . $brief['last_name']) ?>
                                </p>
                                <p class="text-sm text-gray-500">Client</p>
                            </div>
                        </div>

                        <a href="<?= url('/messages/inbox.php') ?>" class="block w-full px-4 py-2 border border-purple-600 text-purple-600 text-center rounded-full font-semibold hover:bg-purple-50 transition">
                            Send Message
                        </a>
                    </div>

                    <!-- Submit Proposal Button -->
                    <?php if (!$existingProposal && $brief['status'] === 'open'): ?>
                        <button onclick="showProposalModal()" class="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-2xl font-bold text-lg hover:opacity-90 transition shadow-lg">
                            Submit Proposal
                        </button>
                    <?php elseif ($brief['status'] !== 'open'): ?>
                        <div class="bg-gray-100 rounded-2xl p-4 text-center">
                            <p class="text-gray-600 font-semibold">This brief is no longer accepting proposals</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Proposal Modal -->
<?php if (!$existingProposal && $brief['status'] === 'open'): ?>
<div id="proposalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Submit Your Proposal</h2>
                <button onclick="hideProposalModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <form method="POST" action="submit-proposal.php" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="brief_id" value="<?= $briefId ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Proposed Amount (USD)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500">$</span>
                        <input type="number" name="amount" required min="<?= number_format($brief['budget_min'] / 100, 2, '.', '') ?>" max="<?= number_format($brief['budget_max'] / 100, 2, '.', '') ?>" step="0.01" class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Client budget: <?= format_money($brief['budget_min']) ?> - <?= format_money($brief['budget_max']) ?>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estimated Timeline</label>
                    <input type="text" name="timeline" required maxlength="100" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., 2-3 weeks">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cover Letter</label>
                    <textarea name="cover_letter" required rows="8" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none" placeholder="Explain why you're the best fit for this project..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Introduce yourself and explain your approach to this project</p>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" onclick="hideProposalModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-full font-semibold hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-full font-semibold hover:opacity-90 transition">
                    Submit Proposal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showProposalModal() {
    document.getElementById('proposalModal').classList.remove('hidden');
}

function hideProposalModal() {
    document.getElementById('proposalModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('proposalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideProposalModal();
    }
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer2.php'; ?>
