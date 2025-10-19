<?php
require_once 'includes/init.php';

$db = get_db_connection();

// Get filter parameters
$projectType = get_query('type', 'all');
$search = get_query('search', '');
$sortBy = get_query('sort', 'recent'); // recent, budget_high, budget_low
$page = max(1, (int)get_query('page', 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
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

// Build ORDER BY clause
$orderBySQL = match($sortBy) {
    'budget_high' => "pb.budget_max DESC",
    'budget_low' => "pb.budget_min ASC",
    default => "pb.created_at DESC"
};

// Get total count
$countSql = "SELECT COUNT(*) as total FROM project_briefs pb WHERE $whereClause";
$stmt = db_prepare($countSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get briefs
$sql = "
    SELECT
        pb.*,
        u.first_name,
        u.last_name,
        (SELECT COUNT(*) FROM proposals WHERE project_brief_id = pb.id) as proposal_count
    FROM project_briefs pb
    JOIN client_profiles cp ON pb.client_profile_id = cp.id
    JOIN users u ON cp.user_id = u.id
    WHERE $whereClause
    ORDER BY $orderBySQL
    LIMIT ? OFFSET ?
";

$stmt = db_prepare($sql);
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$briefs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pagination = paginate($total, $perPage, $page);

// Get total counts for filters
$totalBriefs = $db->query("SELECT COUNT(*) as count FROM project_briefs WHERE status = 'open'")->fetch_assoc()['count'];
$totalDev = $db->query("SELECT COUNT(*) as count FROM project_briefs WHERE status = 'open' AND project_type = 'development'")->fetch_assoc()['count'];
$totalDesign = $db->query("SELECT COUNT(*) as count FROM project_briefs WHERE status = 'open' AND project_type = 'design'")->fetch_assoc()['count'];
$totalBoth = $db->query("SELECT COUNT(*) as count FROM project_briefs WHERE status = 'open' AND project_type = 'both'")->fetch_assoc()['count'];

$pageTitle = 'Browse Projects - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Browse Header -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Browse Projects</h1>
        <p class="text-lg text-gray-600">Find exciting projects that match your skills</p>
    </div>
</div>

<!-- Filters & Search -->
<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <form method="GET" action="<?= url('/briefs.php') ?>" class="space-y-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text"
                       name="search"
                       value="<?= escape_output($search) ?>"
                       placeholder="Search projects by title or description..."
                       class="w-full px-4 py-3 pl-12 rounded-xl border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Filter Tabs & Sort -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Type Filter -->
                <div class="flex gap-2 flex-wrap">
                    <a href="<?= url('/briefs.php?type=all' . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'recent' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $projectType === 'all' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        All (<?= $totalBriefs ?>)
                    </a>
                    <a href="<?= url('/briefs.php?type=development' . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'recent' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $projectType === 'development' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        Development (<?= $totalDev ?>)
                    </a>
                    <a href="<?= url('/briefs.php?type=design' . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'recent' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $projectType === 'design' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        Design (<?= $totalDesign ?>)
                    </a>
                    <a href="<?= url('/briefs.php?type=both' . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'recent' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $projectType === 'both' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        Both (<?= $totalBoth ?>)
                    </a>
                </div>

                <!-- Sort Dropdown -->
                <select name="sort"
                        onchange="this.form.submit()"
                        class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="recent" <?= $sortBy === 'recent' ? 'selected' : '' ?>>Most Recent</option>
                    <option value="budget_high" <?= $sortBy === 'budget_high' ? 'selected' : '' ?>>Highest Budget</option>
                    <option value="budget_low" <?= $sortBy === 'budget_low' ? 'selected' : '' ?>>Lowest Budget</option>
                </select>
            </div>

            <!-- Hidden fields to preserve filters -->
            <input type="hidden" name="type" value="<?= escape_output($projectType) ?>">
        </form>
    </div>
</div>

<!-- Projects List -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if (empty($briefs)): ?>
        <div class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">No projects found</h3>
            <p class="mt-2 text-gray-600">Try adjusting your search or filters</p>
            <a href="<?= url('/briefs.php') ?>" class="mt-6 inline-block px-6 py-3 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                View All Projects
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($briefs as $brief): ?>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-900 mb-2 hover:text-purple-600">
                                    <a href="<?= url('/brief/' . $brief['id']) ?>">
                                        <?= escape_output($brief['title']) ?>
                                    </a>
                                </h2>
                                <p class="text-gray-600 mb-3 line-clamp-2"><?= excerpt($brief['description'], 200) ?></p>
                            </div>
                        </div>

                        <!-- Budget & Meta Info -->
                        <div class="flex flex-wrap items-center gap-4 mb-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                <span class="font-bold text-gray-900 text-lg">
                                    <?= format_money($brief['budget_min']) ?> - <?= format_money($brief['budget_max']) ?>
                                </span>
                            </div>

                            <span class="px-3 py-1 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-full text-sm font-semibold">
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

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                    <?= strtoupper(substr($brief['first_name'], 0, 1) . substr($brief['last_name'], 0, 1)) ?>
                                </div>
                                <span><?= escape_output($brief['first_name'] . ' ' . $brief['last_name']) ?></span>
                                <span>•</span>
                                <span><?= time_ago($brief['created_at']) ?></span>
                            </div>

                            <?php if (is_authenticated() && get_user_type() === 'creator'): ?>
                                <a href="<?= url('/creator/brief-detail.php?id=' . $brief['id']) ?>" class="px-6 py-2 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                    Submit Proposal
                                </a>
                            <?php else: ?>
                                <a href="<?= url('/brief/' . $brief['id']) ?>" class="px-6 py-2 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                    View Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="flex items-center justify-center space-x-2 mt-8">
                <?php if ($pagination['has_prev']): ?>
                    <a href="?type=<?= $projectType ?>&search=<?= urlencode($search) ?>&sort=<?= $sortBy ?>&page=<?= $pagination['prev_page'] ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Previous
                    </a>
                <?php endif; ?>

                <span class="px-4 py-2 text-gray-600">
                    Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                </span>

                <?php if ($pagination['has_next']): ?>
                    <a href="?type=<?= $projectType ?>&search=<?= urlencode($search) ?>&sort=<?= $sortBy ?>&page=<?= $pagination['next_page'] ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- CTA Section -->
<?php if (!is_authenticated()): ?>
<div class="bg-gray-50 py-16 mt-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to work on exciting projects?</h2>
        <p class="text-lg text-gray-600 mb-8">Join as a creator and start submitting proposals to clients</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/register.php?type=creator') ?>" class="px-10 py-4 rounded-full text-white font-bold transition hover:scale-105 shadow-lg" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                Join as Creator
            </a>
            <a href="<?= url('/login.php') ?>" class="px-10 py-4 rounded-full bg-white text-purple-600 border-2 border-purple-600 font-bold transition hover:scale-105">
                Login
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
