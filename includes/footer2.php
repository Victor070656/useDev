
<!-- Footer - UseAllies Style -->
<footer class="bg-gray-50 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
        
        <div class="border-t border-gray-200 py-8 text-sm text-center text-gray-600">
            <p>&copy; <?= date('Y') ?> DevAllies. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Elements for mobile drawer
        const drawerOverlay = document.getElementById('mobile-drawer-overlay');
        const drawer = document.getElementById('mobile-drawer');
        const drawerContent = document.getElementById('mobile-drawer-content');
        const desktopSidebar = document.getElementById('dashboard-sidebar-desktop');
        
        const openBtn = document.getElementById('mobile-drawer-open'); // This ID should be on your topbar hamburger button
        const closeBtn = document.getElementById('mobile-drawer-close');
        const backdrop = document.getElementById('mobile-drawer-backdrop');

        // Clone desktop sidebar content into mobile drawer
        if (desktopSidebar && drawerContent) {
            drawerContent.innerHTML = desktopSidebar.innerHTML;
        }

        function openDrawer() {
            if (!drawerOverlay || !drawer) return;
            drawerOverlay.classList.remove('hidden');
            requestAnimationFrame(() => {
                drawer.classList.remove('-translate-x-full');
                drawer.classList.add('translate-x-0');
            });
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            if (!drawerOverlay || !drawer) return;
            drawer.classList.remove('translate-x-0');
            drawer.classList.add('-translate-x-full');
            setTimeout(() => {
                drawerOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }

        openBtn?.addEventListener('click', openDrawer);
        closeBtn?.addEventListener('click', closeDrawer);
        backdrop?.addEventListener('click', closeDrawer);

        // Profile menu toggle (if it exists)
        const profileButton = document.getElementById('profile-menu-button');
        const profileMenu = document.getElementById('profile-menu');

        if (profileButton && profileMenu) {
            profileButton.addEventListener('click', function (e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                profileButton.setAttribute('aria-expanded', !profileMenu.classList.contains('hidden'));
            });

            document.addEventListener('click', function (e) {
                if (!profileMenu.classList.contains('hidden') && !profileButton.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                    profileButton.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
</script>

</body>
</html>
