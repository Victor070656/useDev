<header
    class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3 border-b border-gray-800 bg-[#0f0e16] sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <!-- Mobile hamburger -->
        <button id="sidebar-toggle" class="md:hidden p-2 rounded-md bg-[#14121f] text-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="sr-only">Open menu</span>
        </button>

    </div>

    <div class="flex items-center gap-3">
        <!-- Search (responsive) -->
        <div class="hidden sm:flex items-center bg-[#14121f] border border-gray-800 rounded-lg px-3 py-2 w-56 md:w-80">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
            </svg>
            <input type="text" placeholder="Search projects, contracts..."
                class="bg-transparent focus:outline-none text-sm text-gray-300 w-full" />
        </div>

        <!-- Notifications -->
        <button class="p-2 rounded-md bg-[#14121f] border border-gray-800 hover:bg-[#1b1730]"
            aria-label="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C8.67 6.165 8 7.388 8 8.75v5.408c0 .387-.214.74-.553.928L6 17h9z" />
            </svg>
        </button>

        <!-- Profile menu -->
        <div class="relative" id="profile-menu-root">
            <button id="profile-menu-button"
                class="flex items-center gap-3 bg-[#14121f] px-3 py-1 rounded-full border border-gray-800"
                aria-haspopup="true" aria-expanded="false">
                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr(get_user_first_name(), 0, 1) . substr(get_user_last_name(), 0, 1)) ?>
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-sm font-medium text-white"><?= escape_output(get_user_first_name() . ' ' . get_user_last_name()) ?></div>
                    <div class="text-xs text-gray-400">Creator</div>
                </div>
                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <div id="profile-menu"
                class="hidden absolute right-0 mt-2 w-56 bg-[#0f0e16] border border-gray-800 rounded-lg shadow-lg py-2 z-30">
                <a href="<?= url('/creator/profile.php') ?>"
                    class="block px-4 py-2 text-sm text-gray-300 hover:bg-[#14121f] hover:text-white">Profile</a>
                <a href="<?= url('/creator/portfolio.php') ?>"
                    class="block px-4 py-2 text-sm text-gray-300 hover:bg-[#14121f] hover:text-white">Portfolio</a>
                <a href="<?= url('/creator/earnings.php') ?>"
                    class="block px-4 py-2 text-sm text-gray-300 hover:bg-[#14121f] hover:text-white">Earnings</a>
                <div class="border-t border-gray-800 my-1"></div>
                <a href="<?= url('/logout.php') ?>" class="block px-4 py-2 text-sm text-red-500 hover:bg-[#14121f]">Sign
                    out</a>
            </div>
        </div>
    </div>

</header>

<script>
    // Profile menu dropdown toggle
    const profileMenuButton = document.getElementById('profile-menu-button');
    const profileMenu = document.getElementById('profile-menu');

    if (profileMenuButton && profileMenu) {
        profileMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileMenuButton.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });
    }
</script>
