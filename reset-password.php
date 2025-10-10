<?php
require_once 'includes/init.php';

// Redirect if already logged in
if (is_authenticated()) {
    redirect('/');
}

// Check for token in URL
$token = get_query('token');

if (!$token && !is_post()) {
    set_flash('error', 'Invalid reset link');
    redirect('/login.php');
    exit;
}

// Handle password reset submission
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/login.php');
        exit;
    }

    $token = get_post('token');
    $password = get_post('password');
    $passwordConfirm = get_post('password_confirm');

    if (!validate_min_length($password, 8)) {
        set_flash('error', 'Password must be at least 8 characters');
        redirect('/reset-password.php?token=' . $token);
        exit;
    }

    if ($password !== $passwordConfirm) {
        set_flash('error', 'Passwords do not match');
        redirect('/reset-password.php?token=' . $token);
        exit;
    }

    if (reset_user_password($token, $password)) {
        set_flash('success', 'Password reset successful! Please login.');
        redirect('/login.php');
    } else {
        set_flash('error', 'Invalid or expired reset link');
        redirect('/login.php');
    }
    exit;
}

$pageTitle = 'Reset Password - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Reset Password - UseAllies Style -->
<div class="min-h-screen flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
    <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    <span class="text-white font-bold text-2xl">🔐</span>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Set new password
                </h2>
                <p class="text-gray-600">
                    Enter your new password below.
                </p>
            </div>

            <form class="space-y-5" action="<?= url('/reset-password.php') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= escape_output($token) ?>">

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Enter new password">
                    <p class="mt-2 text-xs text-gray-500">Minimum 8 characters</p>
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                    <input id="password_confirm" name="password_confirm" type="password" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Re-enter new password">
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full text-base font-bold text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        Reset Password →
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
