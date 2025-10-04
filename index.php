<?php
require_once 'includes/init.php';

$pageTitle = 'Find Top Developers & Designers - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- Hero Section - UseAllies Style -->
<div style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);" class="relative overflow-hidden">
    <!-- Decorative background pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="1" fill="white"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center relative z-10">
            <!-- AI Badge -->
            <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-semibold mb-6">
                <span class="mr-2">✨</span> A.I-Powered Platform
            </div>

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-white mb-6 leading-tight">
                A.I Powered Find & Hire<br/>
                <span class="bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">Developers & Designers</span><br/>
                <span class="text-white">as a Brand or Monetize as a Creator</span>
            </h1>

            <p class="text-lg md:text-xl text-white/90 mb-10 max-w-3xl mx-auto font-medium">
                Our platform uses AI to match brands with the perfect developers and designers,<br class="hidden md:block"/>
                while empowering creators to showcase their work and monetize their skills.
            </p>

            <!-- CTA Button -->
            <a href="<?= url('/browse.php') ?>"
               class="inline-block px-10 py-5 bg-white text-purple-600 rounded-full font-bold text-lg shadow-2xl hover:shadow-xl hover:scale-105 transition-all duration-200">
                See All →
            </a>
        </div>
    </div>

    <!-- Decorative gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/5 pointer-events-none"></div>
</div>


<!-- Featured Creators -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Creators</h2>
        <p class="text-lg text-gray-600">Top-rated developers and designers ready to bring your vision to life</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        // Fetch featured creators
        $db = get_db_connection();
        $result = $db->query("
            SELECT cp.*, u.first_name, u.last_name
            FROM creator_profiles cp
            JOIN users u ON cp.user_id = u.id
            WHERE cp.verified_badge = TRUE AND u.is_active = TRUE
            ORDER BY cp.rating_average DESC
            LIMIT 6
        ");

        $avatarIndex = 0;
        if ($result && $result->num_rows > 0):
            while ($creator = $result->fetch_assoc()):
                $avatarIndex++;
                // Use a variety of professional avatar images
                $avatarUrl = "https://randomuser.me/api/portraits/" . ($creator['creator_type'] === 'designer' ? 'women' : 'men') . "/" . (($creator['id'] % 50) + 1) . ".jpg";
        ?>
            <a href="<?= url('/creator/' . $creator['id']) ?>" class="group block bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                <!-- Gradient Header -->
                <div class="h-24" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);"></div>

                <!-- Card Content -->
                <div class="p-6 -mt-12">
                    <!-- Avatar with actual image -->
                    <img src="<?= $avatarUrl ?>"
                         alt="<?= escape_output($creator['display_name']) ?>"
                         class="w-20 h-20 rounded-full border-4 border-white shadow-lg mb-4 object-cover"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-20 h-20 rounded-full bg-white border-4 border-white shadow-lg mb-4 hidden items-center justify-center text-2xl font-bold text-purple-600">
                        <?= strtoupper(substr($creator['first_name'], 0, 1) . substr($creator['last_name'], 0, 1)) ?>
                    </div>

                    <!-- Name & Badge -->
                    <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-purple-600 transition">
                        <?= escape_output($creator['display_name']) ?>
                        <?php if ($creator['verified_badge']): ?>
                            <span class="inline-block ml-1 text-purple-500">✓</span>
                        <?php endif; ?>
                    </h3>

                    <!-- Headline -->
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?= escape_output($creator['headline']) ?></p>

                    <!-- Stats -->
                    <div class="flex items-center justify-between text-sm mb-4">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="font-semibold"><?= number_format($creator['rating_average'], 1) ?></span>
                            <span class="text-gray-500 ml-1">(<?= $creator['rating_count'] ?>)</span>
                        </div>
                        <div class="font-bold text-gray-900"><?= format_money($creator['hourly_rate']) ?><span class="text-gray-500 font-normal">/hr</span></div>
                    </div>

                    <!-- Type Badge -->
                    <div class="inline-flex px-3 py-1.5 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 rounded-full text-xs font-semibold">
                        <?= ucfirst($creator['creator_type']) ?>
                    </div>
                </div>
            </a>
        <?php
            endwhile;
        else:
        ?>
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500">No featured creators available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-12">
        <a href="<?= url('/browse.php') ?>" class="inline-flex items-center px-8 py-4 rounded-full text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
            View All Creators
            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</div>

<!-- Ways to Earn Section - UseAllies Style Accordion -->
<div class="bg-gray-50 py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">Ways to Earn</h2>
            <p class="text-xl text-gray-600">Multiple revenue streams for creators</p>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <!-- Project Deals -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">💼 Project Deals & Client Work</span>
                    <svg :class="open === 1 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Work directly with brands and startups on development and design projects. Set your own rates and work on projects you're passionate about.</p>
                </div>
            </div>

            <!-- Course Hosting -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">🎓 Course Hosting</span>
                    <svg :class="open === 2 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Create and sell online courses teaching your expertise. Build passive income by sharing your knowledge with aspiring developers and designers.</p>
                </div>
            </div>

            <!-- Digital Products -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">🛍️ Digital Products</span>
                    <svg :class="open === 3 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Sell templates, UI kits, code snippets, plugins, and other digital products. Turn your work into scalable revenue streams.</p>
                </div>
            </div>

            <!-- Affiliate Marketing -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">🔗 Affiliate Marketing</span>
                    <svg :class="open === 4 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Earn commissions by recommending tools and services you use. Perfect for content creators with engaged audiences.</p>
                </div>
            </div>

            <!-- Communities -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 5 ? null : 5" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">👥 Communities</span>
                    <svg :class="open === 5 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Build and monetize exclusive communities. Offer premium memberships with access to resources, networking, and your expertise.</p>
                </div>
            </div>

            <!-- Online Challenges -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 6 ? null : 6" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">🏆 Online Challenges</span>
                    <svg :class="open === 6 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 6" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Host coding challenges, design sprints, and skill-building programs. Engage your community while creating new revenue.</p>
                </div>
            </div>

            <!-- Payment Links -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 7 ? null : 7" class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-lg font-bold text-gray-900">💳 Payment Links</span>
                    <svg :class="open === 7 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 7" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Create custom payment links for consultations, quick jobs, or any service. Get paid instantly for one-off services.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- For Brands Section -->
<div class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6">
                    For Brands &<br/>
                    <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Agencies</span>
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Our AI-powered platform helps you find the perfect developers and designers for your projects in minutes, not weeks.
                </p>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">AI-powered matching with verified developers & designers</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Review portfolios and past work instantly</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Secure payments and milestone-based billing</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">24/7 support and project management tools</span>
                    </li>
                </ul>
                <a href="<?= url('/register.php?type=client') ?>" class="inline-block px-10 py-5 rounded-full text-white font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-200" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
                    Find Creators Now →
                </a>
            </div>
            <div class="relative">
                <!-- Background image for visual appeal -->
                <div class="absolute -top-8 -right-8 w-64 h-64 bg-purple-200 rounded-full opacity-20 blur-3xl"></div>
                <div class="absolute -bottom-8 -left-8 w-64 h-64 bg-pink-200 rounded-full opacity-20 blur-3xl"></div>

                <div class="relative bg-gradient-to-br from-purple-100 to-pink-100 rounded-3xl p-8">
                    <!-- Dashboard mockup image -->
                    <div class="mb-6 rounded-2xl overflow-hidden shadow-2xl border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80"
                             alt="Dashboard Preview"
                             class="w-full h-auto object-cover"
                             onerror="this.style.display='none';">
                    </div>

                    <div class="grid grid-cols-2 gap-6 text-center">
                        <div class="bg-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition">
                            <div class="text-4xl font-bold text-purple-600 mb-2">500+</div>
                            <div class="text-gray-600 text-sm">Verified Creators</div>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition">
                            <div class="text-4xl font-bold text-purple-600 mb-2">95%</div>
                            <div class="text-gray-600 text-sm">Match Success</div>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition">
                            <div class="text-4xl font-bold text-purple-600 mb-2">48h</div>
                            <div class="text-gray-600 text-sm">Avg. Response</div>
                        </div>
                        <div class="bg-white rounded-2xl p-6 shadow-lg transform hover:scale-105 transition">
                            <div class="text-4xl font-bold text-purple-600 mb-2">4.9★</div>
                            <div class="text-gray-600 text-sm">Avg. Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- A.I Tools Section -->
<div class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">A.I Tools</h2>
            <p class="text-xl text-gray-600">Powered by artificial intelligence to boost your productivity</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Funnela -->
            <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-3xl p-8 text-white transform hover:scale-105 transition-all duration-200">
                <div class="text-4xl mb-4">🎯</div>
                <h3 class="text-2xl font-bold mb-3">Funnela</h3>
                <p class="text-white/90">AI-powered project funnel optimization to convert more clients</p>
            </div>

            <!-- Magneta -->
            <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-3xl p-8 text-white transform hover:scale-105 transition-all duration-200">
                <div class="text-4xl mb-4">🧲</div>
                <h3 class="text-2xl font-bold mb-3">Magneta</h3>
                <p class="text-white/90">Attract the right clients with AI-generated proposals and outreach</p>
            </div>

            <!-- Posta -->
            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-3xl p-8 text-white transform hover:scale-105 transition-all duration-200">
                <div class="text-4xl mb-4">📱</div>
                <h3 class="text-2xl font-bold mb-3">Posta</h3>
                <p class="text-white/90">Auto-generate and schedule social media content for your work</p>
            </div>

            <!-- P.A -->
            <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-3xl p-8 text-white transform hover:scale-105 transition-all duration-200">
                <div class="text-4xl mb-4">🤖</div>
                <h3 class="text-2xl font-bold mb-3">P.A</h3>
                <p class="text-white/90">Your personal AI assistant for client communication and admin tasks</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section - UseAllies Style -->
<div class="py-20" style="background: linear-gradient(135deg, #240046 0%, #7103a0 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">Ready to Start Your Project?</h2>
        <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto">Join thousands of teams building amazing products with DevAllies</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/register.php?type=client') ?>" class="px-10 py-5 bg-white text-purple-600 rounded-full hover:bg-gray-50 font-bold text-lg transition hover:scale-105 shadow-xl">
                Hire Talent →
            </a>
            <a href="<?= url('/register.php?type=creator') ?>" class="px-10 py-5 bg-white/10 backdrop-blur-sm text-white rounded-full hover:bg-white/20 font-bold text-lg transition hover:scale-105 border-2 border-white">
                Offer Your Services →
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
