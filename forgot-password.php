<?php
require_once 'includes/init.php';

// Redirect if already logged in
if (is_authenticated()) {
    redirect('/');
}

// Handle forgot password submission
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/forgot-password.php');
        exit;
    }

    $email = sanitize_input(get_post('email'));

    if (!validate_email($email)) {
        set_flash('error', 'Invalid email address');
        redirect('/forgot-password.php');
        exit;
    }

    $user = find_user_by_email($email);

    if ($user) {
        $token = create_password_reset_token($email);

        // Send reset email
        $resetUrl = url('/reset-password.php?token=' . $token);
        $subject = 'Password Reset - ' . APP_NAME;
        $body = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$resetUrl}'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        ";

        send_email($email, $subject, $body);
    }

    // Always show success message (security best practice)
    set_flash('success', 'If an account exists with that email, you will receive a password reset link.');
    redirect('/forgot-password.php');
    exit;
}

$pageTitle = 'Forgot Password - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Forgot Password - UseAllies Style -->
<div class="min-h-screen flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
    <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    <span class="text-white font-bold text-2xl">🔑</span>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Reset your password
                </h2>
                <p class="text-gray-600">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>

            <form class="space-y-5" action="<?= url('/forgot-password.php') ?>" method="POST">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="you@example.com">
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full text-base font-bold text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        Send Reset Link →
                    </button>
                </div>

                <div class="text-center">
                    <a href="<?= url('/login.php') ?>" class="text-sm font-semibold text-purple-600 hover:text-purple-700 transition">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
