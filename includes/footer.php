
<!-- Footer - UseAllies Style -->
<footer class="bg-gray-50 border-t border-gray-200 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <!-- Brand -->
            <div class="col-span-1">
                <a href="<?= url('/') ?>" class="flex items-center space-x-2 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                        <span class="text-white font-bold text-xl">DA</span>
                    </div>
                    <span class="text-2xl font-bold gradient-text">DevAllies</span>
                </a>
                <p class="text-sm text-gray-600 mb-6">
                    Connect with expert developers and designers. Build amazing products with top talent.
                </p>
                <!-- Social Links -->
                <div class="flex space-x-4">
                    <a href="https://twitter.com/devallies" class="text-gray-500 hover:text-purple-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                        </svg>
                    </a>
                    <a href="https://github.com/devallies" class="text-gray-500 hover:text-purple-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                    </a>
                    <a href="https://linkedin.com/company/devallies" class="text-gray-500 hover:text-purple-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Explore -->
            <div>
                <h3 class="text-gray-900 font-bold mb-4">Explore</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="<?= url('/browse.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Find Talent</a></li>
                    <li><a href="<?= url('/courses.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Courses</a></li>
                    <li><a href="<?= url('/products.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Digital Products</a></li>
                    <li><a href="<?= url('/communities.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Communities</a></li>
                </ul>
            </div>

            <!-- For Creators -->
            <div>
                <h3 class="text-gray-900 font-bold mb-4">For Creators</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="<?= url('/register.php?type=creator') ?>" class="text-gray-600 hover:text-purple-600 transition">Join as Creator</a></li>
                    <li><a href="<?= url('/creator/profile.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Build Profile</a></li>
                    <li><a href="<?= url('/creator/create-course.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Create Course</a></li>
                    <li><a href="<?= url('/communities/create.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Start Community</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h3 class="text-gray-900 font-bold mb-4">Company</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="<?= url('/about.php') ?>" class="text-gray-600 hover:text-purple-600 transition">About</a></li>
                    <li><a href="<?= url('/contact.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Contact</a></li>
                    <li><a href="<?= url('/terms.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Terms</a></li>
                    <li><a href="<?= url('/privacy.php') ?>" class="text-gray-600 hover:text-purple-600 transition">Privacy</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-200 mt-12 pt-8 text-sm text-center text-gray-600">
            <p>&copy; <?= date('Y') ?> DevAllies. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
