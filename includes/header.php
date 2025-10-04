<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_output($pageTitle ?? APP_NAME) ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .gradient-text {
            background: linear-gradient(135deg, #240046 0%, #7103a0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="h-full bg-gray-50">

<!-- Navigation - UseAllies Style -->
<nav class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="<?= url('/') ?>" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        <span class="text-white font-bold text-xl">DA</span>
                    </div>
                    <span class="text-2xl font-bold gradient-text">DevAllies</span>
                </a>
            </div>

            <!-- Desktop Navigation - UseAllies Minimal Style -->
            <div class="hidden md:flex md:items-center md:space-x-8">
                <a href="<?= url('/browse.php') ?>" class="text-gray-700 hover:text-purple-600 text-base font-medium transition">
                    Project Deals
                </a>
                <a href="<?= url('/pricing.php') ?>" class="text-gray-700 hover:text-purple-600 text-base font-medium transition">
                    Pricing
                </a>

                <?php if (is_authenticated()): ?>
                    <!-- Authenticated User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">
                            <span><?= escape_output(get_user_name()) ?></span>
                            <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 z-10 border border-gray-200">
                            <?php
                            $dashboardUrl = match(get_user_type()) {
                                'admin' => '/admin/',
                                'creator' => '/creator/',
                                'client' => '/client/',
                                default => '/'
                            };
                            ?>
                            <a href="<?= url($dashboardUrl) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition">
                                Dashboard
                            </a>
                            <a href="<?= url('/profile.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition">
                                Profile
                            </a>
                            <a href="<?= url('/settings.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition">
                                Settings
                            </a>
                            <hr class="my-1">
                            <a href="<?= url('/logout.php') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Guest Menu - UseAllies Style -->
                    <a href="<?= url('/login.php') ?>" class="text-gray-700 hover:text-purple-600 text-base font-medium transition">
                        Login
                    </a>
                    <a href="<?= url('/register.php') ?>" class="px-6 py-3 rounded-full text-white font-semibold transition hover:scale-105 shadow-md" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        Get Started Free
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden" x-data="{ mobileOpen: false }">
                <button @click="mobileOpen = !mobileOpen" class="text-gray-700 hover:text-primary-600 p-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Mobile menu -->
                <div x-show="mobileOpen" @click.away="mobileOpen = false" x-cloak
                     class="absolute top-16 left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-10">
                    <div class="px-4 py-4 space-y-2">
                        <a href="<?= url('/browse.php') ?>" class="block px-3 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition">Project Deals</a>
                        <a href="<?= url('/pricing.php') ?>" class="block px-3 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition">Pricing</a>

                        <?php if (is_authenticated()): ?>
                            <hr class="my-2">
                            <a href="<?= url($dashboardUrl) ?>" class="block px-3 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition">Dashboard</a>
                            <a href="<?= url('/profile.php') ?>" class="block px-3 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition">Profile</a>
                            <a href="<?= url('/logout.php') ?>" class="block px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">Logout</a>
                        <?php else: ?>
                            <hr class="my-2">
                            <a href="<?= url('/login.php') ?>" class="block px-3 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition">Login</a>
                            <a href="<?= url('/register.php') ?>" class="block px-4 py-3 text-white rounded-full text-center font-semibold shadow-md" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">Get Started Free</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php if (has_flash('success')): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-green-50 border-l-4 border-green-500 p-4 max-w-7xl mx-auto mt-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700"><?= escape_output(get_flash('success')) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (has_flash('error')): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-red-50 border-l-4 border-red-500 p-4 max-w-7xl mx-auto mt-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700"><?= get_flash('error') ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
