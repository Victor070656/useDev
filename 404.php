<?php
require_once 'includes/init.php';

http_response_code(404);
$pageTitle = '404 - Page Not Found - ' . APP_NAME;
require_once 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-extrabold gradient-text">404</h1>
            <h2 class="text-3xl font-bold text-gray-900 mt-4 mb-2">Page Not Found</h2>
            <p class="text-gray-600 mb-8">
                The page you're looking for doesn't exist or has been moved.
            </p>
        </div>

        <div class="space-y-4">
            <a href="<?= url('/') ?>"
               class="inline-block px-8 py-4 rounded-full text-white font-semibold transition hover:scale-105 shadow-md"
               style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                Go to Homepage
            </a>

            <div class="pt-4">
                <a href="javascript:history.back()" class="text-purple-600 hover:text-purple-700 font-medium">
                    ← Go Back
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
