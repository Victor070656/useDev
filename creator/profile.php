<?php
require_once '../includes/init.php';
require_auth();
require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Get creator profile
$stmt = db_prepare("SELECT * FROM creator_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    set_flash('error', 'Creator profile not found');
    redirect('/creator/index.php');
    exit;
}

// Get user data
$user = find_user_by_id($userId);

// Define timezone options
$timezones = [
    'America/New_York' => 'Eastern Time (ET) - New York',
    'America/Chicago' => 'Central Time (CT) - Chicago',
    'America/Denver' => 'Mountain Time (MT) - Denver',
    'America/Los_Angeles' => 'Pacific Time (PT) - Los Angeles',
    'America/Anchorage' => 'Alaska Time (AKT) - Anchorage',
    'Pacific/Honolulu' => 'Hawaii Time (HT) - Honolulu',
    'Europe/London' => 'GMT - London',
    'Europe/Paris' => 'CET - Paris, Berlin, Rome',
    'Europe/Athens' => 'EET - Athens, Istanbul',
    'Asia/Dubai' => 'GST - Dubai',
    'Asia/Kolkata' => 'IST - India',
    'Asia/Bangkok' => 'ICT - Bangkok, Jakarta',
    'Asia/Singapore' => 'SGT - Singapore',
    'Asia/Shanghai' => 'CST - Beijing, Shanghai',
    'Asia/Tokyo' => 'JST - Tokyo',
    'Australia/Sydney' => 'AEDT - Sydney, Melbourne',
    'Pacific/Auckland' => 'NZDT - Auckland',
    'UTC' => 'UTC - Coordinated Universal Time',
];

// Handle profile update
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/creator/profile.php');
        exit;
    }

    // Get all form inputs
    $displayName = sanitize_input(get_post('display_name'));
    $headline = sanitize_input(get_post('headline'));
    $bio = sanitize_input(get_post('bio'));
    $location = sanitize_input(get_post('location'));
    $timezone = sanitize_input(get_post('timezone'));

    // URLs
    $websiteUrl = sanitize_input(get_post('website_url'));
    $githubUrl = sanitize_input(get_post('github_url'));
    $linkedinUrl = sanitize_input(get_post('linkedin_url'));
    $twitterUrl = sanitize_input(get_post('twitter_url'));
    $dribbbleUrl = sanitize_input(get_post('dribbble_url'));
    $behanceUrl = sanitize_input(get_post('behance_url'));

    // Rates and availability
    $hourlyRate = (int)get_post('hourly_rate') * 100; // Convert to cents
    $fixedRateAvailable = get_post('fixed_rate_available') ? 1 : 0;
    $isAvailable = get_post('is_available') ? 1 : 0;
    $responseTimeHours = (int)get_post('response_time_hours');

    // Validation
    $errors = [];
    if (empty($displayName)) {
        $errors[] = 'Display name is required';
    }
    if (empty($headline)) {
        $errors[] = 'Headline is required';
    }

    if (empty($errors)) {
        // Update profile
        $stmt = db_prepare("
            UPDATE creator_profiles SET
                display_name = ?,
                headline = ?,
                bio = ?,
                location = ?,
                timezone = ?,
                website_url = ?,
                github_url = ?,
                linkedin_url = ?,
                twitter_url = ?,
                dribbble_url = ?,
                behance_url = ?,
                hourly_rate = ?,
                fixed_rate_available = ?,
                is_available = ?,
                response_time_hours = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->bind_param(
            'sssssssssssiiiii',
            $displayName, $headline, $bio, $location, $timezone,
            $websiteUrl, $githubUrl, $linkedinUrl, $twitterUrl, $dribbbleUrl, $behanceUrl,
            $hourlyRate, $fixedRateAvailable, $isAvailable, $responseTimeHours,
            $userId
        );

        if ($stmt->execute()) {
            log_activity($userId, 'profile_updated', 'creator_profile', $profile['id']);
            set_flash('success', 'Profile updated successfully!');
        } else {
            set_flash('error', 'Failed to update profile');
        }
        $stmt->close();

        redirect('/creator/profile.php');
        exit;
    } else {
        set_flash('error', implode('<br>', $errors));
    }
}

$pageTitle = 'Edit Profile - ' . APP_NAME;
require_once '../includes/header2.php';
?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php include_once '../includes/topbar.php'; ?>
        <div class="px-4 sm:px-6 lg:px-12 py-6">

            <div class="mb-8">
                <h1 class="text-3xl font-bold">Edit Your Profile</h1>
                <p class="mt-2 text-gray-400">Update your professional information to attract more clients</p>
            </div>

            <!-- Profile & Cover Images -->
            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Profile Images</h2>
                
                <!-- Profile Picture -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Profile Picture</h3>
                    <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white text-2xl font-bold overflow-hidden flex-shrink-0">
                            <?php if ($profile['profile_image']): ?>
                                <img src="<?= url($profile['profile_image']) ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="upload-profile-picture.php" enctype="multipart/form-data" class="flex-1 w-full">
                            <?= csrf_field() ?>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                                <input type="file" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/webp" required
                                    class="flex-1 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition whitespace-nowrap">
                                    Upload
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or WEBP. Max 2MB. Square image recommended.</p>
                        </form>
                    </div>
                </div>

                <!-- Cover Image -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Cover Image (Banner)</h3>
                    <div class="space-y-4">
                        <?php if ($profile['cover_image']): ?>
                            <div class="w-full h-48 rounded-xl overflow-hidden bg-gray-100">
                                <img src="<?= url($profile['cover_image']) ?>" alt="Cover" class="w-full h-full object-cover">
                            </div>
                        <?php else: ?>
                            <div class="w-full h-48 rounded-xl bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white">
                                <p class="text-lg">No cover image uploaded</p>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="upload-cover-image.php" enctype="multipart/form-data" class="w-full">
                            <?= csrf_field() ?>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                                <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png,image/webp" required
                                    class="flex-1 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition whitespace-nowrap">
                                    Upload Cover
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or WEBP. Max 5MB. Recommended size: 1200x400px.</p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <form method="POST" class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                <?= csrf_field() ?>

                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="display_name" class="block text-sm font-semibold text-gray-700 mb-2">Display Name *</label>
                                <input type="text" id="display_name" name="display_name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                    placeholder="How you want to be known"
                                    value="<?= escape_output($profile['display_name'] ?? '') ?>">
                            </div>

                            <div>
                                <label for="headline" class="block text-sm font-semibold text-gray-700 mb-2">Professional Headline *</label>
                                <input type="text" id="headline" name="headline" required maxlength="255"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                    placeholder="e.g., Full-Stack Developer | React & Node.js Expert"
                                    value="<?= escape_output($profile['headline'] ?? '') ?>">
                                <p class="text-xs text-gray-500 mt-1">This is the first thing clients see. Make it compelling!</p>
                            </div>

                            <div>
                                <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">Bio</label>
                                <textarea id="bio" name="bio" rows="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                    placeholder="Tell clients about your experience, skills, and what makes you unique..."><?= escape_output($profile['bio'] ?? '') ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Share your story, expertise, and what you're passionate about.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Timezone -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Location & Time</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="bx bx-map"></i> Location
                                </label>
                                <input type="text" id="location" name="location"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                    placeholder="e.g., San Francisco, CA"
                                    value="<?= escape_output($profile['location'] ?? '') ?>">
                            </div>

                            <div>
                                <label for="timezone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="bx bx-time"></i> Timezone
                                </label>
                                <select id="timezone" name="timezone"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900">
                                    <option value="">Select your timezone</option>
                                    <?php foreach ($timezones as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($profile['timezone'] ?? '') === $value ? 'selected' : '' ?>>
                                            <?= escape_output($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Helps clients know your working hours</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rates & Work Preferences -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rates & Availability</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="hourly_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="bx bx-dollar"></i> Hourly Rate (USD)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500">$</span>
                                    <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="5"
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                        placeholder="50"
                                        value="<?= $profile['hourly_rate'] ? number_format($profile['hourly_rate'] / 100, 0) : '' ?>">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Your standard hourly rate</p>
                            </div>

                            <div>
                                <label for="response_time_hours" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="bx bx-time-five"></i> Typical Response Time
                                </label>
                                <select id="response_time_hours" name="response_time_hours"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900">
                                    <option value="">Select response time</option>
                                    <option value="1" <?= ($profile['response_time_hours'] ?? '') == 1 ? 'selected' : '' ?>>Within 1 hour</option>
                                    <option value="2" <?= ($profile['response_time_hours'] ?? '') == 2 ? 'selected' : '' ?>>Within 2 hours</option>
                                    <option value="6" <?= ($profile['response_time_hours'] ?? '') == 6 ? 'selected' : '' ?>>Within 6 hours</option>
                                    <option value="12" <?= ($profile['response_time_hours'] ?? '') == 12 ? 'selected' : '' ?>>Within 12 hours</option>
                                    <option value="24" <?= ($profile['response_time_hours'] ?? '') == 24 ? 'selected' : '' ?>>Within 24 hours</option>
                                    <option value="48" <?= ($profile['response_time_hours'] ?? '') == 48 ? 'selected' : '' ?>>Within 2 days</option>
                                    <option value="72" <?= ($profile['response_time_hours'] ?? '') == 72 ? 'selected' : '' ?>>Within 3 days</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">How quickly you usually respond to messages</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="checkbox" name="fixed_rate_available" value="1"
                                    <?= $profile['fixed_rate_available'] ? 'checked' : '' ?>
                                    class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Available for fixed-rate projects</span>
                            </label>

                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="checkbox" name="is_available" value="1"
                                    <?= $profile['is_available'] ? 'checked' : '' ?>
                                    class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                    <i class="bx bx-check-circle"></i> Currently available for new projects
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="bx bx-link"></i> Links & Portfolio
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label for="website_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="bx bx-globe"></i> Website/Portfolio URL
                                </label>
                                <input type="url" id="website_url" name="website_url"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                    placeholder="https://yourportfolio.com"
                                    value="<?= escape_output($profile['website_url'] ?? '') ?>">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="github_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="bx bxl-github"></i> GitHub URL
                                    </label>
                                    <input type="url" id="github_url" name="github_url"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                        placeholder="https://github.com/username"
                                        value="<?= escape_output($profile['github_url'] ?? '') ?>">
                                </div>

                                <div>
                                    <label for="linkedin_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="bx bxl-linkedin"></i> LinkedIn URL
                                    </label>
                                    <input type="url" id="linkedin_url" name="linkedin_url"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                        placeholder="https://linkedin.com/in/username"
                                        value="<?= escape_output($profile['linkedin_url'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="twitter_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="bx bxl-twitter"></i> Twitter/X URL
                                    </label>
                                    <input type="url" id="twitter_url" name="twitter_url"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                        placeholder="https://twitter.com/username"
                                        value="<?= escape_output($profile['twitter_url'] ?? '') ?>">
                                </div>

                                <?php if ($profile['creator_type'] === 'designer'): ?>
                                    <div>
                                        <label for="dribbble_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="bx bxl-dribbble"></i> Dribbble URL
                                        </label>
                                        <input type="url" id="dribbble_url" name="dribbble_url"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                            placeholder="https://dribbble.com/username"
                                            value="<?= escape_output($profile['dribbble_url'] ?? '') ?>">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($profile['creator_type'] === 'designer'): ?>
                                <div>
                                    <label for="behance_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="bx bxl-behance"></i> Behance URL
                                    </label>
                                    <input type="url" id="behance_url" name="behance_url"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900"
                                        placeholder="https://behance.net/username"
                                        value="<?= escape_output($profile['behance_url'] ?? '') ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200 space-y-3 sm:space-y-0">
                        <a href="<?= url('/creator/') ?>" class="text-gray-600 hover:text-gray-900">
                            <i class="bx bx-arrow-back"></i> Back to Dashboard
                        </a>
                        <button type="submit"
                            class="w-full sm:w-auto px-8 py-3 rounded-full text-white font-semibold transition hover:scale-105 shadow-md"
                            style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                            <i class="bx bx-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
