<?php
require_once '../includes/init.php';

// Require authentication and creator role
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

// Get user data
$user = find_user_by_id($userId);

// Handle profile update
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/creator/profile.php');
        exit;
    }

    $bio = sanitize_input(get_post('bio'));
    $headline = sanitize_input(get_post('headline'));
    $hourlyRate = sanitize_input(get_post('hourly_rate'));
    $location = sanitize_input(get_post('location'));
    $websiteUrl = sanitize_input(get_post('website_url'));
    $githubUrl = sanitize_input(get_post('github_url'));
    $linkedinUrl = sanitize_input(get_post('linkedin_url'));

    // Update profile
    $stmt = db_prepare("
        UPDATE creator_profiles SET
            bio = ?,
            headline = ?,
            hourly_rate = ?,
            location = ?,
            website_url = ?,
            github_url = ?,
            linkedin_url = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param('ssissssi', $bio, $headline, $hourlyRate, $location, $websiteUrl, $githubUrl, $linkedinUrl, $userId);

    if ($stmt->execute()) {
        set_flash('success', 'Profile updated successfully!');
    } else {
        set_flash('error', 'Failed to update profile');
    }
    $stmt->close();

    redirect('/creator/profile.php');
    exit;
}

$pageTitle = 'Edit Profile - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Your Profile</h1>
        <p class="text-gray-600 mt-2">Update your professional information to attract more clients</p>
    </div>

    <!-- Profile Picture Upload -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Profile Picture</h2>
        <div class="flex items-center space-x-6">
            <div class="w-24 h-24 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?= escape_output($user['profile_picture']) ?>" alt="Profile" class="w-full h-full object-cover">
                <?php else: ?>
                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="upload-profile-picture.php" enctype="multipart/form-data" class="flex-1">
                <?= csrf_field() ?>
                <div class="flex items-center space-x-3">
                    <input type="file" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/webp" required class="flex-1 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition">
                        Upload
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">JPG, PNG or WEBP. Max 2MB.</p>
            </form>
        </div>
    </div>

    <form method="POST" class="bg-white rounded-2xl shadow-lg p-8">
        <?= csrf_field() ?>

        <div class="space-y-6">
            <!-- Headline -->
            <div>
                <label for="headline" class="block text-sm font-semibold text-gray-700 mb-2">Professional Headline</label>
                <input type="text" id="headline" name="headline" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="e.g., Full-Stack Developer | React & Node.js Expert"
                       value="<?= escape_output($profile['headline'] ?? '') ?>">
            </div>

            <!-- Bio -->
            <div>
                <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">Bio</label>
                <textarea id="bio" name="bio" rows="5"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Tell clients about your experience..."><?= escape_output($profile['bio'] ?? '') ?></textarea>
            </div>

            <!-- Row: Hourly Rate and Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="hourly_rate" class="block text-sm font-semibold text-gray-700 mb-2">Hourly Rate (USD)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="5"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="50"
                           value="<?= escape_output($profile['hourly_rate'] ?? '') ?>">
                </div>

                <div>
                    <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                    <input type="text" id="location" name="location"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g., San Francisco, CA"
                           value="<?= escape_output($profile['location'] ?? '') ?>">
                </div>
            </div>

            <!-- Links -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Links & Portfolio</h3>

                <div>
                    <label for="website_url" class="block text-sm font-semibold text-gray-700 mb-2">Website/Portfolio URL</label>
                    <input type="url" id="website_url" name="website_url"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="https://yourportfolio.com"
                           value="<?= escape_output($profile['website_url'] ?? '') ?>">
                </div>

                <div>
                    <label for="github_url" class="block text-sm font-semibold text-gray-700 mb-2">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="https://github.com/yourusername"
                           value="<?= escape_output($profile['github_url'] ?? '') ?>">
                </div>

                <div>
                    <label for="linkedin_url" class="block text-sm font-semibold text-gray-700 mb-2">LinkedIn URL</label>
                    <input type="url" id="linkedin_url" name="linkedin_url"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="https://linkedin.com/in/yourusername"
                           value="<?= escape_output($profile['linkedin_url'] ?? '') ?>">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-between items-center pt-6">
                <a href="<?= url('/creator/') ?>" class="text-gray-600 hover:text-gray-900">← Back to Dashboard</a>
                <button type="submit"
                        class="px-8 py-3 rounded-full text-white font-semibold transition hover:scale-105 shadow-md"
                        style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
