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

// Handle registration submission
if (is_post()) {
    if (!verify_csrf_token(get_post('csrf_token'))) {
        set_flash('error', 'Invalid request');
        redirect('/register.php');
        exit;
    }

    $data = [
        'email' => sanitize_input(get_post('email')),
        'password' => get_post('password'),
        'password_confirmation' => get_post('password_confirmation'),
        'first_name' => sanitize_input(get_post('first_name')),
        'last_name' => sanitize_input(get_post('last_name')),
        'user_type' => sanitize_input(get_post('user_type'))
    ];

    // Validation
    $errors = [];

    if (!validate_required($data['first_name'])) {
        $errors[] = 'First name is required';
    }

    if (!validate_required($data['last_name'])) {
        $errors[] = 'Last name is required';
    }

    if (!validate_email($data['email'])) {
        $errors[] = 'Invalid email address';
    } elseif (email_exists($data['email'])) {
        $errors[] = 'Email already registered';
    }

    if (!validate_min_length($data['password'], 8)) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if ($data['password'] !== $data['password_confirmation']) {
        $errors[] = 'Passwords do not match';
    }

    if (!in_array($data['user_type'], ['creator', 'client'])) {
        $errors[] = 'Invalid user type';
    }

    // If creator, validate creator type
    if ($data['user_type'] === 'creator') {
        $data['creator_type'] = sanitize_input(get_post('creator_type', 'developer'));
        if (!in_array($data['creator_type'], ['developer', 'designer'])) {
            $errors[] = 'Invalid creator type';
        }
    }

    if (!empty($errors)) {
        set_flash('error', implode('<br>', $errors));
        set_old_input();
        redirect('/register.php');
        exit;
    }

    // Create user
    $result = create_user($data);

    if ($result['success']) {
        // Send verification email (in production)
        if (APP_ENV === 'production') {
            $verificationUrl = url('/verify-email.php?token=' . $result['verification_token']);
            $subject = 'Verify Your Email - ' . APP_NAME;
            $body = "
                <h2>Welcome to " . APP_NAME . "!</h2>
                <p>Please verify your email address by clicking the link below:</p>
                <p><a href='{$verificationUrl}'>Verify Email</a></p>
            ";
            send_email($data['email'], $subject, $body);
        } else {
            // Auto-verify in development
            verify_user_email($result['verification_token']);
        }

        set_flash('success', 'Registration successful! Please login.');
        redirect('/login.php');
    } else {
        set_flash('error', 'Registration failed. Please try again.');
        redirect('/register.php');
    }
    exit;
}

$pageTitle = 'Register - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Register - UseAllies Style -->
<div class="min-h-screen flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
    <div class="max-w-lg w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    <span class="text-white font-bold text-2xl">DA</span>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Join DevAllies
                </h2>
                <p class="text-gray-600">
                    Start your journey as a creator or client
                </p>
            </div>

            <form class="space-y-5" action="<?= url('/register.php') ?>" method="POST" x-data="{ userType: '<?= old('user_type', 'creator') ?>' }">
                <?= csrf_field() ?>

                <!-- User Type Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">I want to:</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center px-4 py-4 cursor-pointer border-2 rounded-xl transition"
                               :class="userType === 'creator' ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="user_type" value="creator" x-model="userType" class="sr-only" <?= old('user_type', 'creator') === 'creator' ? 'checked' : '' ?>>
                            <div class="text-center">
                                <div class="text-2xl mb-1">💼</div>
                                <span class="text-sm font-semibold" :class="userType === 'creator' ? 'text-purple-900' : 'text-gray-700'">
                                    Offer Services
                                </span>
                            </div>
                        </label>
                        <label class="relative flex items-center justify-center px-4 py-4 cursor-pointer border-2 rounded-xl transition"
                               :class="userType === 'client' ? 'border-purple-600 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="user_type" value="client" x-model="userType" class="sr-only" <?= old('user_type') === 'client' ? 'checked' : '' ?>>
                            <div class="text-center">
                                <div class="text-2xl mb-1">🚀</div>
                                <span class="text-sm font-semibold" :class="userType === 'client' ? 'text-purple-900' : 'text-gray-700'">
                                    Hire Talent
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Creator Type (only for creators) -->
                <div x-show="userType === 'creator'" x-cloak>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">I am a:</label>
                    <select name="creator_type"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                        <option value="developer" <?= old('creator_type') === 'developer' ? 'selected' : '' ?>>Developer</option>
                        <option value="designer" <?= old('creator_type') === 'designer' ? 'selected' : '' ?>>Designer</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                        <input id="first_name" name="first_name" type="text" required
                               class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="John"
                               value="<?= escape_output(old('first_name')) ?>">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                        <input id="last_name" name="last_name" type="text" required
                               class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="Doe"
                               value="<?= escape_output(old('last_name')) ?>">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="you@example.com"
                           value="<?= escape_output(old('email')) ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Create a strong password">
                    <p class="mt-2 text-xs text-gray-500">Minimum 8 characters</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="Re-enter your password">
                </div>

                <div class="flex items-start">
                    <input id="terms" name="terms" type="checkbox" required
                           class="h-4 w-4 mt-1 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        I agree to the <a href="<?= url('/terms') ?>" class="text-purple-600 hover:text-purple-700 font-semibold">Terms of Service</a> and <a href="<?= url('/privacy') ?>" class="text-purple-600 hover:text-purple-700 font-semibold">Privacy Policy</a>
                    </label>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full text-base font-bold text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        Create Account →
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="<?= url('/login.php') ?>" class="font-semibold text-purple-600 hover:text-purple-700 transition">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
clear_old_input();
require_once 'includes/footer.php';
?>
