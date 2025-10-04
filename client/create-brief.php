<?php
require_once '../includes/init.php';
require_auth();
require_role('client');

$userId = get_user_id();

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

    $stmt = db_prepare("
        INSERT INTO project_briefs (client_id, title, description, budget, timeline, required_skills, status)
        VALUES (?, ?, ?, ?, ?, ?, 'open')
    ");
    $stmt->bind_param('ississ', $userId, $title, $description, $budget, $timeline, $skills);

    if ($stmt->execute()) {
        set_flash('success', 'Brief posted successfully!');
        redirect('/client/briefs.php');
    } else {
        set_flash('error', 'Failed to post brief');
    }
    $stmt->close();
}

$pageTitle = 'Post New Brief - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Post a New Project Brief</h1>

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
                        class="px-8 py-3 rounded-full text-white font-semibold"
                        style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Post Brief
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
