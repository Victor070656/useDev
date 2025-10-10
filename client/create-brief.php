<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();
$db = get_db_connection();

// Get client profile
$stmt = db_prepare("SELECT id FROM client_profiles WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$clientProfile = $result->fetch_assoc();
$stmt->close();

if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/client/create-brief.php');
        exit;
    }

    $title = sanitize_input(get_post('title'));
    $description = sanitize_input(get_post('description'));
    $budget = sanitize_input(get_post('budget'));
    $timeline = sanitize_input(get_post('timeline'));
    $skills = sanitize_input(get_post('skills'));

    // Convert budget to cents for storage
    $budgetCents = $budget * 100;

    $stmt = db_prepare("
        INSERT INTO project_briefs (client_profile_id, title, description, project_type, budget_type, budget_min, budget_max, timeline, required_skills, status)
        VALUES (?, ?, ?, 'both', 'fixed', ?, ?, ?, ?, 'open')
    ");
    $stmt->bind_param('issiiss', $clientProfile['id'], $title, $description, $budgetCents, $budgetCents, $timeline, $skills);

    if ($stmt->execute()) {
        set_flash('success', 'Brief posted successfully!');
        redirect('/client/briefs.php');
    } else {
        set_flash('error', 'Failed to post brief');
    }
    $stmt->close();
}

$pageTitle = 'Post New Brief - ' . APP_NAME;
include_once '../includes/header-client.php';

?>

<div class="min-h-screen flex bg-[#0f0e16] text-gray-100">
    <?php include_once '../includes/sidebar-client.php'; ?>
    <!-- Dashboard Container -->
    <div class="flex-1 flex flex-col transition-all duration-300 md:ml-64">
        <?php
        include_once '../includes/topbar-client.php';
        ?>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Post a New Project Brief</h1>
                <p class="text-gray-400 mt-2">Create a detailed brief to attract the best creators</p>
            </div>

            <div class="max-w-4xl mx-auto">

                <form method="POST" class="bg-white rounded-2xl shadow-lg p-8">
                    <?= csrf_field() ?>

                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Project Title *</label>
                            <input type="text" id="title" name="title" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                                   placeholder="e.g., Build a React Native Mobile App">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Project Description *</label>
                            <textarea id="description" name="description" rows="6" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                                      placeholder="Describe your project requirements..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="budget" class="block text-sm font-semibold text-gray-700 mb-2">Budget (USD) *</label>
                                <input type="number" id="budget" name="budget" min="0" step="100" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                                       placeholder="5000">
                            </div>

                            <div>
                                <label for="timeline" class="block text-sm font-semibold text-gray-700 mb-2">Timeline *</label>
                                <input type="text" id="timeline" name="timeline" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                                       placeholder="e.g., 2-3 months">
                            </div>
                        </div>

                        <div>
                            <label for="skills" class="block text-sm font-semibold text-gray-700 mb-2">Required Skills (comma-separated)</label>
                            <input type="text" id="skills" name="skills"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                                   placeholder="React Native, Node.js, MongoDB">
                        </div>

                        <div class="flex justify-between items-center pt-6">
                            <a href="<?= url('/client/briefs.php') ?>" class="text-gray-600 hover:text-gray-900">← Cancel</a>
                            <button type="submit"
                                    class="px-8 py-3 rounded-full text-white font-semibold hover:scale-105 transition-transform"
                                    style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                                Post Brief
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer2.php'; ?>
