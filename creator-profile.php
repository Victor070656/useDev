<?php
require_once 'includes/init.php';

$creatorId = get_query('id');

if (!$creatorId) {
    redirect('/browse.php');
    exit;
}

$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("
    SELECT cp.*, u.first_name, u.last_name, u.email, u.created_at as member_since
    FROM creator_profiles cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.user_id = ? AND u.is_active = 1
");
$stmt->bind_param('i', $creatorId);
$stmt->execute();
$creator = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$creator) {
    set_flash('error', 'Creator profile not found');
    redirect('/browse.php');
    exit;
}

// Get portfolio items
$stmt = db_prepare("SELECT * FROM portfolio_items WHERE creator_id = ? ORDER BY created_at DESC LIMIT 6");
$stmt->bind_param('i', $creatorId);
$stmt->execute();
$portfolioItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = $creator['first_name'] . ' ' . $creator['last_name'] . ' - ' . APP_NAME;
require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-3xl p-8 mb-8 text-white">
        <div class="flex items-start space-x-6">
            <div class="w-32 h-32 rounded-full bg-white/20 flex items-center justify-center text-5xl font-bold">
                <?= strtoupper(substr($creator['first_name'], 0, 1) . substr($creator['last_name'], 0, 1)) ?>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-4xl font-bold"><?= escape_output($creator['first_name'] . ' ' . $creator['last_name']) ?></h1>
                    <?php if ($creator['verified_badge']): ?>
                        <svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <p class="text-xl text-white/90 mb-3"><?= escape_output($creator['headline'] ?? 'Professional ' . ucfirst($creator['creator_type'])) ?></p>
                <div class="flex flex-wrap items-center gap-4 text-white/80">
                    <?php if ($creator['location']): ?>
                        <div class="flex items-center space-x-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><?= escape_output($creator['location']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($creator['hourly_rate']): ?>
                        <div class="flex items-center space-x-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            <span>$<?= $creator['hourly_rate'] ?>/hr</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($creator['rating_average'] > 0): ?>
                        <div class="flex items-center space-x-1">
                            <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span><?= number_format($creator['rating_average'], 1) ?> (<?= $creator['rating_count'] ?>)</span>
                        </div>
                    <?php endif; ?>
                    <span>• Member since <?= format_date($creator['member_since'], 'Y') ?></span>
                </div>
            </div>
            <?php if (is_authenticated() && get_user_type() === 'client'): ?>
                <button class="px-6 py-3 bg-white text-purple-600 rounded-full font-semibold hover:bg-gray-100 transition">
                    Contact Creator
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- About -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">About</h2>
                <p class="text-gray-600 whitespace-pre-line"><?= escape_output($creator['bio'] ?? 'No bio available.') ?></p>
            </div>

            <!-- Portfolio -->
            <?php if (!empty($portfolioItems)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Portfolio</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($portfolioItems as $item): ?>
                            <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?= escape_output($item['image_url']) ?>" alt="<?= escape_output($item['title']) ?>" class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gradient-to-br from-purple-400 to-purple-600"></div>
                                <?php endif; ?>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-2"><?= escape_output($item['title']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= escape_output(truncate($item['description'], 80)) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stats -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Projects Completed</span>
                        <span class="font-bold text-gray-900"><?= $creator['total_projects'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Earned</span>
                        <span class="font-bold text-gray-900"><?= format_money($creator['total_earnings']) ?></span>
                    </div>
                    <?php if ($creator['response_time_hours']): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Response Time</span>
                            <span class="font-bold text-gray-900"><?= $creator['response_time_hours'] ?>h</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Links -->
            <?php if ($creator['website_url'] || $creator['github_url'] || $creator['linkedin_url']): ?>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Links</h3>
                    <div class="space-y-3">
                        <?php if ($creator['website_url']): ?>
                            <a href="<?= escape_output($creator['website_url']) ?>" target="_blank" class="flex items-center space-x-2 text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <span>Website</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($creator['github_url']): ?>
                            <a href="<?= escape_output($creator['github_url']) ?>" target="_blank" class="flex items-center space-x-2 text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                                </svg>
                                <span>GitHub</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($creator['linkedin_url']): ?>
                            <a href="<?= escape_output($creator['linkedin_url']) ?>" target="_blank" class="flex items-center space-x-2 text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                                <span>LinkedIn</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
