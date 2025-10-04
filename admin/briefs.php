<?php
require_once '../includes/init.php';
require_auth();
require_role('admin');

$db = get_db_connection();

// Get all briefs
$result = db_query("
    SELECT pb.*, u.first_name, u.last_name, u.email
    FROM project_briefs pb
    JOIN users u ON pb.client_id = u.id
    ORDER BY pb.created_at DESC
    LIMIT 50
");
$briefs = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Briefs - ' . APP_NAME;
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Briefs</h1>

    <div class="space-y-4">
        <?php foreach ($briefs as $brief): ?>
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= escape_output($brief['title']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= escape_output(truncate($brief['description'], 200)) ?></p>
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span>Client: <?= escape_output($brief['first_name'] . ' ' . $brief['last_name']) ?></span>
                            <span>Budget: <?= format_money($brief['budget']) ?></span>
                            <span>Posted: <?= format_date($brief['created_at']) ?></span>
                        </div>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <?= ucfirst(str_replace('_', ' ', $brief['status'])) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
