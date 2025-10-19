<?php
require_once '../includes/init.php';
require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Get filter parameters
$projectType = get_query('type', 'all');
$search = get_query('search', '');
$page = max(1, (int)get_query('page', 1));
$perPage = SEARCH_RESULTS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Build query
$where = ["pb.status = 'open'"];
$params = [];
$types = '';

if ($projectType !== 'all') {
    $where[] = "pb.project_type = ?";
    $params[] = $projectType;
    $types .= 's';
}

if ($search) {
    $where[] = "(pb.title LIKE ? OR pb.description LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM project_briefs pb WHERE $whereClause";
$stmt = db_prepare($countSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get creator profile
$stmt = db_prepare("SELECT id FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$creatorProfile = $result->fetch_assoc();
$stmt->close();

// Get briefs
$sql = "
    SELECT
        pb.*,
        u.first_name,
        u.last_name,
        (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count,
        (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id AND creator_profile_id = ?) as user_proposal_count
    FROM project_briefs pb
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE $whereClause
    ORDER BY pb.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = db_prepare($sql);
$allParams = array_merge([$creatorProfile['id']], $params, [$perPage, $offset]);
$allTypes = 'i' . $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$briefs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pagination = paginate($total, $perPage, $page);

$pageTitle = 'Browse Projects - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php include_once '../includes/topbar.php'; ?>
        <div class="px-12 py-6">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Browse Projects</h1>
                <p class="text-gray-400">Find projects that match your skills and interests</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Project Type</label>
                        <select name="type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="all" <?= $projectType === 'all' ? 'selected' : '' ?>>All Types</option>
                            <option value="development" <?= $projectType === 'development' ? 'selected' : '' ?>>Development</option>
                            <option value="design" <?= $projectType === 'design' ? 'selected' : '' ?>>Design</option>
                            <option value="both" <?= $projectType === 'both' ? 'selected' : '' ?>>Both</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="<?= escape_output($search) ?>" placeholder="Search projects..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-xl font-semibold hover:opacity-90 transition">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <?php if (empty($briefs)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No projects found</h3>
                    <p class="text-gray-600">Try adjusting your filters or check back later</p>
                </div>
            <?php else: ?>
                <div class="space-y-4 mb-8">
                    <p class="text-gray-400"><?= $total ?> project<?= $total !== 1 ? 's' : '' ?> found</p>

                    <?php foreach ($briefs as $brief): ?>
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h2 class="text-xl font-bold text-gray-900">
                                            <a href="brief-detail.php?id=<?= $brief['id'] ?>" class="hover:text-purple-600">
                                                <?= escape_output($brief['title']) ?>
                                            </a>
                                        </h2>
                                        <?php if ($brief['user_proposal_count'] > 0): ?>
                                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                                Applied
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-gray-600 mb-3"><?= excerpt($brief['description'], 200) ?></p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 mb-4">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    <span class="font-semibold text-gray-900">
                                        <?= format_money($brief['budget_min']) ?> - <?= format_money($brief['budget_max']) ?>
                                    </span>
                                </div>

                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                    <?= ucfirst($brief['project_type']) ?>
                                </span>

                                <?php if ($brief['timeline']): ?>
                                    <div class="flex items-center space-x-1 text-gray-600 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span><?= escape_output($brief['timeline']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="flex items-center space-x-1 text-gray-600 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span><?= $brief['proposal_count'] ?> proposal<?= $brief['proposal_count'] !== 1 ? 's' : '' ?></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white text-xs font-bold">
                                        <?= strtoupper(substr($brief['first_name'], 0, 1) . substr($brief['last_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= escape_output($brief['first_name'] . ' ' . $brief['last_name']) ?></span>
                                    <span>•</span>
                                    <span><?= time_ago($brief['created_at']) ?></span>
                                </div>

                                <a href="brief-detail.php?id=<?= $brief['id'] ?>" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-full font-semibold hover:opacity-90 transition">
                                    <?= $brief['user_proposal_count'] > 0 ? 'View Details' : 'Submit Proposal' ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="flex items-center justify-center space-x-2">
                        <?php if ($pagination['has_prev']): ?>
                            <a href="?type=<?= $projectType ?>&search=<?= urlencode($search) ?>&page=<?= $pagination['prev_page'] ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Previous
                            </a>
                        <?php endif; ?>

                        <span class="px-4 py-2 text-gray-600">
                            Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                        </span>

                        <?php if ($pagination['has_next']): ?>
                            <a href="?type=<?= $projectType ?>&search=<?= urlencode($search) ?>&page=<?= $pagination['next_page'] ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
