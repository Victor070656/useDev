<!-- Creator Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <a href="<?= url('/') ?>" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    <?= APP_NAME ?>
                </h1>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4">
            <div class="space-y-1">
                <a href="<?= url('/creator/') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/', '/creator/index.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="<?= url('/creator/briefs.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/briefs.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Browse Projects</span>
                </a>

                <a href="<?= url('/creator/proposals.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/proposals.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>My Proposals</span>
                </a>

                <a href="<?= url('/creator/contracts.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/contracts.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <span>Contracts</span>
                </a>

                <a href="<?= url('/creator/earnings.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/earnings.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Earnings</span>
                </a>

                <a href="<?= url('/messages/inbox.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/messages/inbox.php', '/messages/thread.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>Messages</span>
                </a>

                <!-- Divider -->
                <div class="pt-4 pb-2">
                    <div class="px-4 text-xs font-semibold text-gray-400 uppercase">Portfolio</div>
                </div>

                <a href="<?= url('/creator/profile.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/profile.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>My Profile</span>
                </a>

                <a href="<?= url('/creator/portfolio.php') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg <?= is_current_page('/creator/portfolio.php') ? 'text-white bg-purple-600' : 'text-gray-700 hover:bg-gray-100' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Portfolio</span>
                </a>
            </div>
        </nav>

        <!-- User Info -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr(get_user_first_name(), 0, 1) . substr(get_user_last_name(), 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate"><?= escape_output(get_user_first_name() . ' ' . get_user_last_name()) ?></div>
                    <div class="text-xs text-gray-500">Creator</div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<script>
// Sidebar Toggle
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const sidebarToggle = document.getElementById('sidebar-toggle');

function toggleSidebar() {
    sidebar.classList.toggle('-translate-x-full');
    sidebarOverlay.classList.toggle('hidden');
}

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', toggleSidebar);
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', toggleSidebar);
}
</script>
