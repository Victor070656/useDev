<?php
require_once '../includes/init.php';
require_auth();
require_role('creator');

$userId = get_user_id();
$db = get_db_connection();

// Handle adding portfolio item
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/creator/portfolio.php');
        exit;
    }

    $title = sanitize_input(get_post('title'));
    $description = sanitize_input(get_post('description'));
    $projectUrl = sanitize_input(get_post('project_url'));
    $imageUrl = sanitize_input(get_post('image_url'));

    $stmt = db_prepare("
        INSERT INTO portfolio_items (creator_id, title, description, project_url, image_url)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('issss', $userId, $title, $description, $projectUrl, $imageUrl);

    if ($stmt->execute()) {
        set_flash('success', 'Portfolio item added successfully!');
    } else {
        set_flash('error', 'Failed to add portfolio item');
    }
    $stmt->close();
    redirect('/creator/portfolio.php');
    exit;
}

// Get portfolio items
$stmt = db_prepare("SELECT * FROM portfolio_items WHERE creator_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$portfolioItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Portfolio - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Portfolio</h1>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="px-6 py-3 rounded-full text-white font-semibold"
                style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
            + Add Portfolio Item
        </button>
    </div>

    <!-- Portfolio Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($portfolioItems)): ?>
            <div class="col-span-full bg-white rounded-2xl shadow-lg p-12 text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Portfolio Items Yet</h3>
                <p class="text-gray-600 mb-6">Showcase your best work to attract clients</p>
            </div>
        <?php else: ?>
            <?php foreach ($portfolioItems as $item): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                    <?php if ($item['image_url']): ?>
                        <img src="<?= escape_output($item['image_url']) ?>" alt="<?= escape_output($item['title']) ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= escape_output($item['title']) ?></h3>
                        <p class="text-gray-600 text-sm mb-4"><?= escape_output(truncate($item['description'], 100)) ?></p>
                        <?php if ($item['project_url']): ?>
                            <a href="<?= escape_output($item['project_url']) ?>" target="_blank" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                View Project →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Portfolio Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-2xl w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Add Portfolio Item</h2>
        
        <form method="POST">
            <?= csrf_field() ?>
            
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Project Title *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                           placeholder="E-commerce Website Redesign">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                              placeholder="Describe the project, your role, and the results..."></textarea>
                </div>

                <div>
                    <label for="project_url" class="block text-sm font-semibold text-gray-700 mb-2">Project URL</label>
                    <input type="url" id="project_url" name="project_url"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500"
                           placeholder="https://example.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Portfolio Image</label>
                    <div id="uploadArea" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-purple-500 transition">
                        <input type="file" id="portfolioImageInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="hidden">
                        <input type="hidden" id="image_url" name="image_url">

                        <div id="uploadPrompt">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <button type="button" onclick="document.getElementById('portfolioImageInput').click()" class="text-purple-600 font-semibold hover:text-purple-700">
                                Click to upload
                            </button>
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF or WEBP. Max 5MB.</p>
                        </div>

                        <div id="uploadPreview" class="hidden">
                            <img id="previewImage" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg mb-3">
                            <button type="button" onclick="removeImage()" class="text-sm text-red-600 hover:text-red-700 font-semibold">
                                Remove Image
                            </button>
                        </div>

                        <div id="uploadProgress" class="hidden">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
                            <p class="text-sm text-gray-600 mt-3">Uploading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-6 py-3 border border-gray-300 rounded-full text-gray-700 font-semibold hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-3 rounded-full text-white font-semibold"
                        style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Add Portfolio Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Handle portfolio image upload
document.getElementById('portfolioImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Show progress
    document.getElementById('uploadPrompt').classList.add('hidden');
    document.getElementById('uploadProgress').classList.remove('hidden');

    // Create FormData
    const formData = new FormData();
    formData.append('portfolio_image', file);
    formData.append('csrf_token', '<?= generate_csrf_token() ?>');

    // Upload via AJAX
    fetch('upload-portfolio-image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('uploadProgress').classList.add('hidden');

        if (data.success) {
            // Show preview
            document.getElementById('previewImage').src = data.file_path;
            document.getElementById('image_url').value = data.file_path;
            document.getElementById('uploadPreview').classList.remove('hidden');
        } else {
            alert('Upload failed: ' + data.error);
            document.getElementById('uploadPrompt').classList.remove('hidden');
        }
    })
    .catch(error => {
        document.getElementById('uploadProgress').classList.add('hidden');
        document.getElementById('uploadPrompt').classList.remove('hidden');
        alert('Upload failed: ' + error.message);
    });
});

function removeImage() {
    document.getElementById('portfolioImageInput').value = '';
    document.getElementById('image_url').value = '';
    document.getElementById('uploadPreview').classList.add('hidden');
    document.getElementById('uploadPrompt').classList.remove('hidden');
}
</script>

<?php require_once '../includes/footer.php'; ?>
