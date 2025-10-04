<?php
require_once 'includes/init.php';

// Redirect if already logged in
if (is_authenticated()) {
    $redirectUrl = match(get_user_type()) {
        'admin' => '/admin/',
        'creator' => '/creator/',
        'client' => '/client/',
        default => '/'
    };
    redirect($redirectUrl);
}

// Handle login submission
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/login.php');
        exit;
    }

    $email = sanitize_input(get_post('email'));
    $password = get_post('password');

    // Validation
    if (!validate_email($email)) {
        set_flash('error', 'Invalid email address');
        redirect('/login.php');
        exit;
    }

    // Find user
    $user = find_user_by_email($email);

    if (!$user || !verify_password($password, $user['password_hash'])) {
        set_flash('error', 'Invalid credentials');
        redirect('/login.php');
        exit;
    }

    // Check if user is active
    if (!$user['is_active']) {
        set_flash('error', 'Your account has been deactivated');
        redirect('/login.php');
        exit;
    }

    // Check if email is verified
    if (!$user['email_verified']) {
        set_flash('error', 'Please verify your email address');
        redirect('/login.php');
        exit;
    }

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];

    // Update last login
    update_last_login($user['id']);

    // Log activity
    log_activity($user['id'], 'login');

    // Redirect based on user type
    $redirectUrl = match($user['user_type']) {
        'admin' => '/admin/',
        'creator' => '/creator/',
        'client' => '/client/',
        default => '/'
    };
    redirect($redirectUrl);
    exit;
}

$pageTitle = 'Login - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Login - UseAllies Style -->
<div class="min-h-screen flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
    <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    <span class="text-white font-bold text-2xl">DA</span>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Welcome Back
                </h2>
                <p class="text-gray-600">
                    Sign in to continue to DevAllies
                </p>
            </div>

            <form class="space-y-5" action="<?= url('/login.php') ?>" method="POST">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="you@example.com"
                           value="<?= escape_output(old('email')) ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox"
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= url('/forgot-password.php') ?>" class="font-semibold text-purple-600 hover:text-purple-700 transition">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full text-base font-bold text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        Sign In →
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <a href="<?= url('/register.php') ?>" class="font-semibold text-purple-600 hover:text-purple-700 transition">
                        Sign up for free
                    </a>
                </p>
            </div>

            <!-- Quick Login for Testing -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-center text-gray-500 mb-3">Quick login for testing</p>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="document.getElementById('email').value='john.dev@example.com'; document.getElementById('password').value='password';"
                            class="py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Developer
                    </button>
                    <button type="button" onclick="document.getElementById('email').value='client@startup.com'; document.getElementById('password').value='password';"
                            class="py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Client
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
clear_old_input();
require_once 'includes/footer.php';
?>
