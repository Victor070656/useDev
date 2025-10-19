<?php
require_once 'includes/init.php';

$briefId = get_query('id', 0);
if (!$briefId) {
    set_flash('error', 'Project not found');
    redirect('/briefs.php');
}

$db = get_db_connection();

// Get brief details
$stmt = db_prepare("
    SELECT
        pb.*,
        cp.id as client_profile_id,
        u.first_name,
        u.last_name,
        u.email,
        (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count
    FROM project_briefs pb
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE pb.id = ?
");
$stmt->bind_param('i', $briefId);
$stmt->execute();
$result = $stmt->get_result();
$brief = $result->fetch_assoc();
$stmt->close();

if (!$brief) {
    set_flash('error', 'Project not found');
    redirect('/briefs.php');
}

// Check if user already submitted a proposal (if logged in as creator)
$userHasProposal = false;
if (is_authenticated() && get_user_type() === 'creator') {
    $userId = get_user_id();
    $stmt = db_prepare("
        SELECT p.id
        FROM proposals p
        JOIN creator_profiles cp ON p.creator_profile_id = cp.id
        WHERE p.project_brief_id = ? AND cp.user_id = ?
    ");
    $stmt->bind_param('ii', $briefId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userHasProposal = $result->num_rows > 0;
    $stmt->close();
}

$pageTitle = escape_output($brief['title']) . ' - ' . APP_NAME;
require_once 'includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= url('/briefs.php') ?>" class="inline-flex items-center text-purple-600 hover:text-purple-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Projects
        </a>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Brief Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Brief Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= escape_output($brief['title']) ?></h1>

                <!-- Meta Info -->
                <div class="flex flex-wrap items-center gap-4 mb-6">
                    <span class="px-3 py-1 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-full text-sm font-semibold">
                        <?= ucfirst($brief['project_type']) ?>
                    </span>

                    <?php if ($brief['status'] === 'open'): ?>
                        <span class="px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-semibold">
                            Open for Proposals
                        </span>
                    <?php endif; ?>

                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Posted <?= time_ago($brief['created_at']) ?>
                    </div>

                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <?= $brief['proposal_count'] ?> proposal<?= $brief['proposal_count'] !== 1 ? 's' : '' ?>
                    </div>
                </div>

                <!-- Description -->
                <div class="prose max-w-none">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Project Description</h2>
                    <p class="text-gray-700 whitespace-pre-wrap"><?= escape_output($brief['description']) ?></p>
                </div>

                <!-- Requirements -->
                <?php if ($brief['requirements']): ?>
                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirements</h2>
                        <p class="text-gray-700 whitespace-pre-wrap"><?= escape_output($brief['requirements']) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Deliverables -->
                <?php if ($brief['deliverables']): ?>
                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Deliverables</h2>
                        <p class="text-gray-700 whitespace-pre-wrap"><?= escape_output($brief['deliverables']) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Attachment -->
                <?php if ($brief['attachment_path']): ?>
                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Attachments</h2>
                        <a href="<?= url($brief['attachment_path']) ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Attachment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Budget Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Budget</h3>
                <div class="flex items-baseline space-x-2 mb-2">
                    <span class="text-3xl font-bold text-gray-900"><?= format_money($brief['budget_min']) ?></span>
                    <span class="text-gray-600">-</span>
                    <span class="text-3xl font-bold text-gray-900"><?= format_money($brief['budget_max']) ?></span>
                </div>
                <p class="text-sm text-gray-600">Budget Range</p>
            </div>

            <!-- Timeline Card -->
            <?php if ($brief['timeline']): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><?= escape_output($brief['timeline']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Client Info Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">About the Client</h3>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        <?= strtoupper(substr($brief['first_name'], 0, 1) . substr($brief['last_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900"><?= escape_output($brief['first_name'] . ' ' . $brief['last_name']) ?></p>
                        <p class="text-sm text-gray-600">Client</p>
                    </div>
                </div>
            </div>

            <!-- Action Card -->
            <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-2xl shadow-lg p-6 text-white sticky top-6">
                <?php if (!is_authenticated()): ?>
                    <h3 class="text-lg font-semibold mb-3">Interested in this project?</h3>
                    <p class="text-purple-100 text-sm mb-4">Sign up as a creator to submit your proposal</p>
                    <a href="<?= url('/register.php?type=creator') ?>" class="block w-full px-6 py-3 bg-white text-purple-600 rounded-full font-semibold text-center hover:bg-gray-100 transition mb-2">
                        Join as Creator
                    </a>
                    <a href="<?= url('/login.php') ?>" class="block w-full px-6 py-3 bg-purple-700 text-white rounded-full font-semibold text-center hover:bg-purple-800 transition">
                        Login
                    </a>
                <?php elseif (get_user_type() === 'creator'): ?>
                    <?php if ($userHasProposal): ?>
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold mb-2">Proposal Submitted</h3>
                            <p class="text-purple-100 text-sm mb-4">You've already submitted a proposal for this project</p>
                            <a href="<?= url('/creator/proposals.php') ?>" class="block w-full px-6 py-3 bg-white text-purple-600 rounded-full font-semibold text-center hover:bg-gray-100 transition">
                                View My Proposals
                            </a>
                        </div>
                    <?php else: ?>
                        <h3 class="text-lg font-semibold mb-3">Ready to submit a proposal?</h3>
                        <p class="text-purple-100 text-sm mb-4">Show the client why you're the best fit for this project</p>
                        <a href="<?= url('/creator/brief-detail.php?id=' . $brief['id']) ?>" class="block w-full px-6 py-3 bg-white text-purple-600 rounded-full font-semibold text-center hover:bg-gray-100 transition">
                            Submit Proposal
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <h3 class="text-lg font-semibold mb-3">Want to submit a proposal?</h3>
                    <p class="text-purple-100 text-sm mb-4">You need a creator account to submit proposals</p>
                    <a href="<?= url('/register.php?type=creator') ?>" class="block w-full px-6 py-3 bg-white text-purple-600 rounded-full font-semibold text-center hover:bg-gray-100 transition">
                        Join as Creator
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Similar Projects -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Similar Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            // Get similar projects
            $stmt = db_prepare("
                SELECT
                    pb.*,
                    u.first_name,
                    u.last_name,
                    (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count
                FROM project_briefs pb
                JOIN client_profiles cp ON pb.client_profile_id = cp.id
                JOIN users u ON cp.user_id = u.id
                WHERE pb.status = 'open'
                    AND pb.id != ?
                    AND pb.project_type = ?
                ORDER BY pb.created_at DESC
                LIMIT 4
            ");
            $stmt->bind_param('is', $briefId, $brief['project_type']);
            $stmt->execute();
            $similarBriefs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (!empty($similarBriefs)):
                foreach ($similarBriefs as $similar):
            ?>
                <a href="<?= url('/brief-detail.php?id=' . $similar['id']) ?>" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-900 mb-2 hover:text-purple-600"><?= escape_output($similar['title']) ?></h3>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= excerpt($similar['description'], 100) ?></p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-bold text-gray-900"><?= format_money($similar['budget_min']) ?> - <?= format_money($similar['budget_max']) ?></span>
                        <span class="text-gray-600"><?= $similar['proposal_count'] ?> proposals</span>
                    </div>
                </a>
            <?php
                endforeach;
            else:
            ?>
                <p class="text-gray-600 col-span-2">No similar projects found</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
