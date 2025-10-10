<!--
    DESKTOP SIDEBAR
    - Always visible on medium screens and larger (md:)
    - Uses `fixed` positioning to stay in place
    - `w-64` sets the width
    - `flex flex-col` creates the main vertical layout
-->
<aside id="dashboard-sidebar-desktop"
    class="hidden md:flex md:flex-col md:fixed md:inset-y-0 md:w-64 bg-[#14121f] border-r border-gray-800 p-4">

    <!-- Logo -->
    <div class="flex items-center justify-between mb-6">
        <a href="<?= url('/creator/index.php') ?>" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                <span class="font-bold text-white">DA</span>
            </div>
            <div>
                <div class="font-semibold text-white"><?= APP_NAME ?></div>
                <div class="text-xs text-gray-400">Creator</div>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1">
        <?php
        $navLinks = [
            ['url' => '/creator/index.php', 'icon' => 'bx-grid-alt', 'label' => 'Dashboard', 'page' => 'index.php'],
            ['url' => '/creator/profile.php', 'icon' => 'bx-user', 'label' => 'Profile', 'page' => 'profile.php'],
            ['url' => '/creator/proposals.php', 'icon' => 'bx-file', 'label' => 'Proposals', 'page' => 'proposals.php'],
            ['url' => '/creator/contracts.php', 'icon' => 'bx-briefcase', 'label' => 'Contracts', 'page' => 'contracts.php'],
            ['url' => '/creator/earnings.php', 'icon' => 'bx-wallet', 'label' => 'Earnings', 'page' => 'earnings.php'],
            ['url' => '/messages/inbox.php', 'icon' => 'bx-message-square-detail', 'label' => 'Messages', 'page' => 'inbox.php'],
        ];
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

        <?php foreach ($navLinks as $link): ?>
            <a href="<?= url($link['url']) ?>"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors <?= $currentPage === $link['page'] ? 'bg-[#1f1b2b] text-white' : 'text-gray-400 hover:bg-[#1f1b2b] hover:text-white' ?>">
                <i class='bx <?= $link['icon'] ?> text-xl'></i>
                <span><?= $link['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Logout -->
    <div class="mt-auto">
        <a href="<?= url('/logout.php') ?>"
            class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 transition">
            <i class='bx bx-log-out text-xl'></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!--
    MOBILE DRAWER
    - Hidden by default, shown on mobile
    - `fixed inset-0 z-40` makes it a full-screen overlay
    - `transition-transform` for the slide-in effect
-->
<div id="mobile-drawer-overlay" class="fixed inset-0 z-30 bg-black/50 hidden md:hidden">
    <!-- Backdrop -->
    <div id="mobile-drawer-backdrop" class="absolute inset-0"></div>

    <!-- Drawer -->
    <aside id="mobile-drawer"
        class="absolute top-0 left-0 h-full w-64 bg-[#14121f] border-r border-gray-800 p-4 transform -translate-x-full transition-transform duration-300 ease-in-out z-40">
        <!-- Content will be cloned here by JS -->
        <div id="mobile-drawer-content"></div>

        <!-- Explicit close button for mobile -->
        <button id="mobile-drawer-close" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-white">
            <i class='bx bx-x text-2xl'></i>
        </button>
    </aside>
</div>
