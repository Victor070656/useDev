<?php
require_once 'includes/init.php';

$pageTitle = 'Find Top Developers & Designers - ' . APP_NAME;
require_once 'includes/header.php';
?>

<!-- HERO — Developer-styled -->
<section class="relative overflow-hidden bg-gradient-to-br from-[#0f1724] via-[#240046] to-[#3b1054]">
    <!-- subtle grid / dots -->
    <div class="absolute inset-0 opacity-6 pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <pattern id="dev-dots" width="36" height="36" patternUnits="userSpaceOnUse">
                    <circle cx="18" cy="18" r="0.9" fill="rgba(255,255,255,0.06)" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dev-dots)" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <!-- Left content -->
            <div class="lg:col-span-7">
                <div
                    class="inline-flex items-center gap-3 text-sm bg-white/10 backdrop-blur-sm text-white rounded-full px-4 py-2 mb-6">
                    <span class="text-lg">⚡</span>
                    <span class="font-semibold">A.I. matching · Verified creators</span>
                </div>

                <h1 class="text-white text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight mb-5">
                    Find & Hire top developers and designers —<br class="hidden sm:block" />
                    or <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-200 to-white">Monetize as
                        a Creator</span>
                </h1>

                <p class="text-indigo-100/90 max-w-2xl text-base sm:text-lg mb-8">
                    A developer-focused marketplace that combines AI discovery, verified profiles, and developer-first
                    tools — built to get projects shipped faster.
                </p>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <a href="<?= url('/browse.php') ?>"
                        class="inline-flex items-center justify-center px-8 py-3 rounded-full font-semibold shadow-lg bg-white text-purple-700 hover:scale-[1.02] transition-transform">
                        Browse Developers →
                    </a>

                    <a href="<?= url('/register.php?type=creator') ?>"
                        class="inline-flex items-center justify-center px-8 py-3 rounded-full font-semibold border border-white/20 text-white bg-white/5 hover:bg-white/10 transition">
                        Create profile
                    </a>
                </div>

                <ul class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-indigo-200">
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-indigo-300" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 10l3 3L15 6" />
                        </svg>
                        <span>Verified reviews & work history</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-indigo-300" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 10l3 3L15 6" />
                        </svg>
                        <span>Milestone payments & contracts</span>
                    </li>
                </ul>
            </div>

            <!-- Right mockup -->
            <div class="lg:col-span-5">
                <div
                    class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/10 bg-gradient-to-br from-white/5 to-white/3 p-6">
                    <div class="mb-4 rounded-lg overflow-hidden border border-white/10">
                        <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=60"
                            alt="Developer dashboard" class="w-full h-48 object-cover"
                            onerror="this.style.display='none';">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="text-sm text-indigo-100">Verified</div>
                            <div class="text-lg font-semibold text-white">512+</div>
                            <div class="text-xs text-indigo-200">Active creators</div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="text-sm text-indigo-100">Avg. Rating</div>
                            <div class="text-lg font-semibold text-white">4.9★</div>
                            <div class="text-xs text-indigo-200">From client reviews</div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="text-sm text-indigo-100">Response Time</div>
                            <div class="text-lg font-semibold text-white">48h</div>
                            <div class="text-xs text-indigo-200">Avg. reply</div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="text-sm text-indigo-100">Success</div>
                            <div class="text-lg font-semibold text-white">95%</div>
                            <div class="text-xs text-indigo-200">Project matches</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- soft bottom overlay -->
    <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/20 to-transparent pointer-events-none">
    </div>
</section>

<!-- FEATURED CREATORS — Cleaner developer cards -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Featured Creators</h2>
            <p class="text-sm text-gray-600">Top-rated developers & designers ready for hire</p>
        </div>
        <div class="hidden sm:flex gap-3">
            <a href="<?= url('/browse.php') ?>"
                class="px-4 py-2 rounded-full text-sm bg-indigo-50 text-indigo-700 font-semibold">View all</a>
            <a href="<?= url('/register.php?type=creator') ?>"
                class="px-4 py-2 rounded-full text-sm border border-gray-200">Become a creator</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
                <a href="<?= url('/creator/' . $creator['id']) ?>"
                    class="group block bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 overflow-hidden">
                    <!-- header accent -->
                    <div class="h-16" style="background: linear-gradient(90deg,#2b076e,#8a2387);"></div>

                    <div class="p-5 -mt-12">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <img src="<?= $avatarUrl ?>" alt="<?= escape_output($creator['display_name']) ?>"
                                    class="w-16 h-16 rounded-full border-4 border-white shadow-md object-cover"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div
                                    class="w-16 h-16 rounded-full bg-gray-50 border-4 border-white shadow-md hidden items-center justify-center text-sm font-semibold text-gray-700">
                                    <?= strtoupper(substr($creator['first_name'], 0, 1) . substr($creator['last_name'], 0, 1)) ?>
                                </div>
                            </div>

                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900 group-hover:text-indigo-600 transition">
                                    <?= escape_output($creator['display_name']) ?>
                                    <?php if ($creator['verified_badge']): ?>
                                        <span class="inline-block ml-1 text-indigo-500">✓</span>
                                    <?php endif; ?>
                                </h3>
                                <p class="text-sm text-gray-500 truncate"><?= escape_output($creator['headline']) ?></p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg class="w-4 h-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 .364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 .00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 .364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 .951-.69l1.07-3.292z" />
                                </svg>
                                <span class="font-semibold"><?= number_format($creator['rating_average'], 1) ?></span>
                                <span class="text-gray-400">(<?= $creator['rating_count'] ?>)</span>
                            </div>

                            <div class="text-sm font-bold text-gray-900">
                                <?= format_money($creator['hourly_rate']) ?><span class="text-gray-400 font-normal">/hr</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                <?= ucfirst($creator['creator_type']) ?>
                            </span>
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
        <a href="<?= url('/browse.php') ?>"
            class="inline-flex items-center px-8 py-3 rounded-full text-white font-semibold bg-indigo-600 hover:bg-indigo-700 transition">
            View All Creators
            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </a>
    </div>
</section>

<!-- WAYS TO EARN — Accordion (Alpine attributes preserved) -->
<section class="bg-gray-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Ways to Earn</h2>
            <p class="text-sm text-gray-600">Multiple revenue streams designed for builders and creators</p>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <!-- Reuse your accordion content; styling updated -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 1 ? null : 1"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">💼 Project Deals & Client Work</span>
                    <svg :class="open === 1 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Work directly with brands and startups on development and design projects.
                        Set your own rates and work on projects you're passionate about.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 2 ? null : 2"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">🎓 Course Hosting</span>
                    <svg :class="open === 2 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Create and sell online courses teaching your expertise. Build passive
                        income by sharing your knowledge with aspiring developers and designers.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 3 ? null : 3"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">🛍️ Digital Products</span>
                    <svg :class="open === 3 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Sell templates, UI kits, code snippets, plugins, and other digital
                        products. Turn your work into scalable revenue streams.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 4 ? null : 4"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">🔗 Affiliate Marketing</span>
                    <svg :class="open === 4 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Earn commissions by recommending tools and services you use. Perfect for
                        content creators with engaged audiences.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 5 ? null : 5"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">👥 Communities</span>
                    <svg :class="open === 5 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Build and monetize exclusive communities. Offer premium memberships with
                        access to resources, networking, and your expertise.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 6 ? null : 6"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">🏆 Online Challenges</span>
                    <svg :class="open === 6 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 6" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Host coding challenges, design sprints, and skill-building programs. Engage
                        your community while creating new revenue.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <button @click="open = open === 7 ? null : 7"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="text-md font-semibold text-gray-900">💳 Payment Links</span>
                    <svg :class="open === 7 ? 'rotate-180' : ''" class="w-6 h-6 text-gray-500 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === 7" x-collapse class="px-6 pb-5">
                    <p class="text-gray-600">Create custom payment links for consultations, quick jobs, or any service.
                        Get paid instantly for one-off services.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOR BRANDS — Developer-oriented -->
<section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                    For Brands & <br />
                    <span
                        class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-pink-600">Agencies</span>
                </h2>
                <p class="text-base text-gray-600 mb-6">Find verified developers and designers with the skills you need.
                    Shortlist, interview, and start work — fast.</p>

                <ul class="space-y-4 mb-6 text-sm text-gray-700">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>AI-powered matching with verified developers & designers</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>Review portfolios and past work instantly</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-600 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>Secure payments and milestone-based billing</div>
                    </li>
                </ul>

                <a href="<?= url('/register.php?type=client') ?>"
                    class="inline-block px-8 py-3 rounded-full text-white font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 shadow">
                    Find Creators Now →
                </a>
            </div>

            <div class="relative">
                <div class="absolute -top-8 -right-8 w-56 h-56 bg-indigo-200 rounded-full opacity-20 blur-2xl"></div>
                <div class="absolute -bottom-8 -left-8 w-56 h-56 bg-pink-200 rounded-full opacity-20 blur-2xl"></div>

                <div class="relative bg-gradient-to-br from-indigo-50 to-pink-50 rounded-3xl p-6">
                    <div class="mb-6 rounded-2xl overflow-hidden shadow-md border border-white/40">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1000&q=60"
                            alt="Dashboard" class="w-full h-44 object-cover" onerror="this.style.display='none';">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-2xl p-4 shadow">
                            <div class="text-sm text-indigo-600 font-semibold">500+</div>
                            <div class="text-xs text-gray-500">Verified Creators</div>
                        </div>
                        <div class="bg-white rounded-2xl p-4 shadow">
                            <div class="text-sm text-indigo-600 font-semibold">95%</div>
                            <div class="text-xs text-gray-500">Match Success</div>
                        </div>
                        <div class="bg-white rounded-2xl p-4 shadow">
                            <div class="text-sm text-indigo-600 font-semibold">48h</div>
                            <div class="text-xs text-gray-500">Avg. Response</div>
                        </div>
                        <div class="bg-white rounded-2xl p-4 shadow">
                            <div class="text-sm text-indigo-600 font-semibold">4.9★</div>
                            <div class="text-xs text-gray-500">Avg. Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI TOOLS -->
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-extrabold text-gray-900">A.I Tools</h2>
            <p class="text-sm text-gray-600">Tools to speed up proposals, outreach and delivery</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="rounded-2xl p-6 transform hover:scale-105 transition bg-gradient-to-br from-yellow-400 to-orange-500 text-white">
                <div class="text-2xl mb-3">🎯</div>
                <h3 class="font-semibold mb-2">Funnela</h3>
                <p class="text-sm">AI-powered project funnel optimization to convert more clients</p>
            </div>
            <div
                class="rounded-2xl p-6 transform hover:scale-105 transition bg-gradient-to-br from-pink-500 to-rose-600 text-white">
                <div class="text-2xl mb-3">🧲</div>
                <h3 class="font-semibold mb-2">Magneta</h3>
                <p class="text-sm">Attract the right clients with AI-generated proposals and outreach</p>
            </div>
            <div
                class="rounded-2xl p-6 transform hover:scale-105 transition bg-gradient-to-br from-blue-500 to-cyan-600 text-white">
                <div class="text-2xl mb-3">📱</div>
                <h3 class="font-semibold mb-2">Posta</h3>
                <p class="text-sm">Auto-generate and schedule social media content for your work</p>
            </div>
            <div
                class="rounded-2xl p-6 transform hover:scale-105 transition bg-gradient-to-br from-indigo-600 to-purple-700 text-white">
                <div class="text-2xl mb-3">🤖</div>
                <h3 class="font-semibold mb-2">P.A</h3>
                <p class="text-sm">Your personal AI assistant for client communication and admin tasks</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-br from-[#240046] to-[#7103a0]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">Ready to start your project?</h2>
        <p class="text-indigo-100 mb-6">Join teams building with verified developers and designers.</p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?= url('/register.php?type=client') ?>"
                class="px-6 py-3 rounded-full bg-white text-purple-700 font-semibold shadow">Hire Talent →</a>
            <a href="<?= url('/register.php?type=creator') ?>"
                class="px-6 py-3 rounded-full border border-white/30 text-white bg-white/5 font-semibold">Offer your
                services →</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>