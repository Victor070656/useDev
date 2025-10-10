<?php
require_once 'includes/init.php';

$db = get_db_connection();

// Get filter parameters
$creatorType = get_query('type', 'all');
$searchQuery = get_query('search', '');
$sortBy = get_query('sort', 'rating'); // rating, rate, recent

// Build WHERE clause
$whereClauses = ["u.is_active = TRUE"];
$params = [];
$types = "";

if ($creatorType !== 'all' && in_array($creatorType, ['developer', 'designer'])) {
    $whereClauses[] = "cp.creator_type = ?";
    $params[] = $creatorType;
    $types .= "s";
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(cp.display_name LIKE ? OR cp.headline LIKE ? OR cp.bio LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

$whereSQL = implode(' AND ', $whereClauses);

// Build ORDER BY clause
$orderBySQL = match($sortBy) {
    'rate' => "cp.hourly_rate ASC",
    'recent' => "cp.created_at DESC",
    default => "cp.rating_average DESC"
};

// Prepare and execute query
$sql = "
    SELECT cp.*, u.first_name, u.last_name
    FROM creator_profiles cp
    JOIN users u ON cp.user_id = u.id
    WHERE {$whereSQL}
    ORDER BY {$orderBySQL}
    LIMIT 50
";

if (!empty($params)) {
    $stmt = db_prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $db->query($sql);
}

$creators = [];
while ($row = $result->fetch_assoc()) {
    $creators[] = $row;
}

if (!empty($params)) {
    $stmt->close();
}

// Get total counts for filters
$totalCreators = $db->query("SELECT COUNT(*) as count FROM creator_profiles cp JOIN users u ON cp.user_id = u.id WHERE u.is_active = TRUE")->fetch_assoc()['count'];
$totalDevelopers = $db->query("SELECT COUNT(*) as count FROM creator_profiles cp JOIN users u ON cp.user_id = u.id WHERE u.is_active = TRUE AND cp.creator_type = 'developer'")->fetch_assoc()['count'];
$totalDesigners = $db->query("SELECT COUNT(*) as count FROM creator_profiles cp JOIN users u ON cp.user_id = u.id WHERE u.is_active = TRUE AND cp.creator_type = 'designer'")->fetch_assoc()['count'];

$pageTitle = 'Browse Creators - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Browse Header -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Browse Creators</h1>
        <p class="text-lg text-gray-600">Find talented developers and designers for your next project</p>
    </div>
</div>

<!-- Filters & Search -->
<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <form method="GET" action="<?= url('/browse.php') ?>" class="space-y-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text"
                       name="search"
                       value="<?= escape_output($searchQuery) ?>"
                       placeholder="Search by name, skills, or expertise..."
                       class="w-full px-4 py-3 pl-12 rounded-xl border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Filter Tabs & Sort -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Type Filter -->
                <div class="flex gap-2">
                    <a href="<?= url('/browse.php?type=all' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '') . ($sortBy !== 'rating' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $creatorType === 'all' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        All (<?= $totalCreators ?>)
                    </a>
                    <a href="<?= url('/browse.php?type=developer' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '') . ($sortBy !== 'rating' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $creatorType === 'developer' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        Developers (<?= $totalDevelopers ?>)
                    </a>
                    <a href="<?= url('/browse.php?type=designer' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '') . ($sortBy !== 'rating' ? '&sort=' . $sortBy : '')) ?>"
                       class="px-4 py-2 rounded-lg font-medium transition <?= $creatorType === 'designer' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        Designers (<?= $totalDesigners ?>)
                    </a>
                </div>

                <!-- Sort Dropdown -->
                <select name="sort"
                        onchange="this.form.submit()"
                        class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                    <option value="rate" <?= $sortBy === 'rate' ? 'selected' : '' ?>>Lowest Rate</option>
                    <option value="recent" <?= $sortBy === 'recent' ? 'selected' : '' ?>>Recently Joined</option>
                </select>
            </div>

            <!-- Hidden fields to preserve filters -->
            <input type="hidden" name="type" value="<?= escape_output($creatorType) ?>">
        </form>
    </div>
</div>

<!-- Creators Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if (empty($creators)): ?>
        <div class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">No creators found</h3>
            <p class="mt-2 text-gray-600">Try adjusting your search or filters</p>
            <a href="<?= url('/browse.php') ?>" class="mt-6 inline-block px-6 py-3 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                View All Creators
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $avatarIndex = 0;
            foreach ($creators as $creator):
                $avatarIndex++;
                // Use a variety of professional avatar images
                $avatarUrl = "https://randomuser.me/api/portraits/" . ($creator['creator_type'] === 'designer' ? 'women' : 'men') . "/" . (($creator['id'] % 50) + 1) . ".jpg";
            ?>
                <a href="<?= url('/creator/' . $creator['id']) ?>" class="group block bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                    <!-- Gradient Header -->
                    <div class="h-24" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);"></div>

                    <!-- Card Content -->
                    <div class="p-6 -mt-12">
                        <!-- Avatar -->
                        <img src="<?= $avatarUrl ?>"
                             alt="<?= escape_output($creator['display_name']) ?>"
                             class="w-20 h-20 rounded-full border-4 border-white shadow-lg mb-4 object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-20 h-20 rounded-full bg-white border-4 border-white shadow-lg mb-4 hidden items-center justify-center text-2xl font-bold text-purple-600">
                            <?= strtoupper(substr($creator['first_name'], 0, 1) . substr($creator['last_name'], 0, 1)) ?>
                        </div>

                        <!-- Name & Badge -->
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-purple-600 transition">
                            <?= escape_output($creator['display_name']) ?>
                            <?php if ($creator['verified_badge']): ?>
                                <span class="inline-block ml-1 text-purple-500">✓</span>
                            <?php endif; ?>
                        </h3>

                        <!-- Headline -->
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2 min-h-[40px]">
                            <?= escape_output($creator['headline'] ?: 'Professional ' . ucfirst($creator['creator_type'])) ?>
                        </p>

                        <!-- Stats -->
                        <div class="flex items-center justify-between text-sm mb-4">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="font-semibold"><?= number_format($creator['rating_average'], 1) ?></span>
                                <span class="text-gray-500 ml-1">(<?= $creator['rating_count'] ?>)</span>
                            </div>
                            <?php if ($creator['hourly_rate']): ?>
                                <div class="font-bold text-gray-900">
                                    <?= format_money($creator['hourly_rate']) ?><span class="text-gray-500 font-normal">/hr</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Type Badge -->
                        <div class="flex items-center justify-between">
                            <div class="inline-flex px-3 py-1.5 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-full text-xs font-semibold">
                                <?= ucfirst($creator['creator_type']) ?>
                            </div>
                            <?php if ($creator['is_available']): ?>
                                <span class="flex items-center text-xs text-green-600 font-medium">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                                    Available
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Projects Count -->
                        <?php if ($creator['total_projects'] > 0): ?>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-600">
                                    <?= $creator['total_projects'] ?> project<?= $creator['total_projects'] !== 1 ? 's' : '' ?> completed
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Load More (if needed) -->
        <?php if (count($creators) >= 50): ?>
            <div class="text-center mt-12">
                <p class="text-gray-600 mb-4">Showing <?= count($creators) ?> creators</p>
                <button class="px-8 py-3 rounded-full text-white font-semibold transition hover:scale-105" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Load More
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- CTA Section -->
<div class="bg-gray-50 py-16 mt-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Are you a creator?</h2>
        <p class="text-lg text-gray-600 mb-8">Join our platform and connect with clients looking for your expertise</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/register.php?type=creator') ?>" class="px-10 py-4 rounded-full text-white font-bold transition hover:scale-105 shadow-lg" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                Join as Creator
            </a>
            <a href="<?= url('/register.php?type=client') ?>" class="px-10 py-4 rounded-full bg-white text-purple-600 border-2 border-purple-600 font-bold transition hover:scale-105">
                Hire Talent
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
