<?php
include __DIR__ . '/header.php';

// Fetch Reports from moderation-service API
$reportResponse = makeApiRequest('GET', '/api/v1/moderation/admin/reports', [], $token);
$reports = [];
if ($reportResponse['status'] === 200 && isset($reportResponse['data']['reports'])) {
    $reports = $reportResponse['data']['reports'];
}

// Fetch Users from auth/user service
$userResponse = makeApiRequest('GET', '/api/v1/admin/users', [], $token);
$users = [];
if ($userResponse['status'] === 200 && isset($userResponse['data']['users'])) {
    $users = $userResponse['data']['users'];
}

$pendingCount = 0;
$reviewedCount = 0;
foreach ($reports as $r) {
    if ($r['status'] === 'pending') $pendingCount++;
    else $reviewedCount++;
}

$totalActiveUsers = 0;
$totalSubscribers = 0;
foreach ($users as $u) {
    if (($u['status'] ?? 'active') !== 'suspended') {
        $totalActiveUsers++;
    }
    if (($u['tier'] ?? 'free') === 'premium') {
        $totalSubscribers++;
    }
}
?>

<div class="max-w-6xl mx-auto my-8 px-4">
    <!-- Admin Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4 bg-white/40 backdrop-blur-md">
            <span class="text-3xl bg-blue-100 p-3 rounded-2xl">👥</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Users</span>
                <h4 class="text-3xl font-extrabold text-gray-800"><?= $totalActiveUsers ?></h4>
            </div>
        </div>
        
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4 bg-white/40 backdrop-blur-md">
            <span class="text-3xl bg-yellow-100 p-3 rounded-2xl">👑</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Subscribers</span>
                <h4 class="text-3xl font-extrabold text-gray-800"><?= $totalSubscribers ?></h4>
            </div>
        </div>

        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4 bg-white/40 backdrop-blur-md">
            <span class="text-3xl bg-orange-100 p-3 rounded-2xl">⏳</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Reports</span>
                <h4 class="text-3xl font-extrabold text-gray-800"><?= $pendingCount ?></h4>
            </div>
        </div>
        
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4 bg-white/40 backdrop-blur-md">
            <span class="text-3xl bg-green-100 p-3 rounded-2xl">✅</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Reviewed Actions</span>
                <h4 class="text-3xl font-extrabold text-gray-800"><?= $reviewedCount ?></h4>
            </div>
        </div>
    </div>

    <!-- Visual Analytics Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Signups Growth Chart -->
        <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 bg-white/80 backdrop-blur-md">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 serif-font">📈 Signups Growth (Last 7 Days)</h3>
                <p class="text-xs text-gray-400">Daily registration velocity for LGBTQ+ Matrimony platform.</p>
            </div>
            <div class="relative h-64 w-full pt-4">
                <svg class="w-full h-full" viewBox="0 0 500 200" preserveAspectRatio="none">
                    <!-- Grid Lines -->
                    <line x1="0" y1="50" x2="500" y2="50" stroke="#f3f4f6" stroke-width="1" />
                    <line x1="0" y1="100" x2="500" y2="100" stroke="#f3f4f6" stroke-width="1" />
                    <line x1="0" y1="150" x2="500" y2="150" stroke="#f3f4f6" stroke-width="1" />
                    
                    <!-- Gradients -->
                    <defs>
                        <linearGradient id="chart-grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#ec4899" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="chart-line-grad" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#8b5cf6"/>
                            <stop offset="100%" stop-color="#ec4899"/>
                        </linearGradient>
                    </defs>
                    
                    <!-- Area under line -->
                    <path d="M 0 200 L 0 150 L 83 120 L 166 160 L 249 90 L 332 70 L 415 110 L 500 40 L 500 200 Z" fill="url(#chart-grad)" />
                    
                    <!-- Line Chart -->
                    <path d="M 0 150 L 83 120 L 166 160 L 249 90 L 332 70 L 415 110 L 500 40" fill="none" stroke="url(#chart-line-grad)" stroke-width="3.5" stroke-linecap="round" />
                    
                    <!-- Data Points -->
                    <circle cx="0" cy="150" r="5" fill="#8b5cf6" stroke="#fff" stroke-width="2" />
                    <circle cx="83" cy="120" r="5" fill="#8b5cf6" stroke="#fff" stroke-width="2" />
                    <circle cx="166" cy="160" r="5" fill="#8b5cf6" stroke="#fff" stroke-width="2" />
                    <circle cx="249" cy="90" r="5" fill="#a78bfa" stroke="#fff" stroke-width="2" />
                    <circle cx="332" cy="70" r="5" fill="#f472b6" stroke="#fff" stroke-width="2" />
                    <circle cx="415" cy="110" r="5" fill="#ec4899" stroke="#fff" stroke-width="2" />
                    <circle cx="500" cy="40" r="6" fill="#db2777" stroke="#fff" stroke-width="2" />
                </svg>
                <!-- Labels -->
                <div class="flex justify-between text-[10px] font-semibold text-gray-400 mt-2 px-1">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>
            </div>
        </div>

        <!-- Premium Conversion Funnel -->
        <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 bg-white/80 backdrop-blur-md">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 serif-font">🎯 Premium Conversion Funnel</h3>
                <p class="text-xs text-gray-400">Visitor progression from landing to premium checkout.</p>
            </div>
            <div class="space-y-4 pt-2">
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-500">1. Total Visitors</span>
                        <span class="text-gray-800 font-bold">12,450 (100%)</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gray-400 h-full rounded-full" style="width: 100%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-500">2. Registered Profiles</span>
                        <span class="text-gray-800 font-bold">4,120 (33.1%)</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-indigo-500 h-full rounded-full" style="width: 33.1%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-500">3. Active Matches Searchers</span>
                        <span class="text-gray-800 font-bold">2,850 (22.8%)</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-purple-500 h-full rounded-full" style="width: 22.8%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-gray-500">4. Initiated Chat Conversation</span>
                        <span class="text-gray-800 font-bold">1,420 (11.4%)</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-pink-500 h-full rounded-full" style="width: 11.4%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-pink-600 font-bold">5. Upgraded to Premium (Subscribers)</span>
                        <span class="text-pink-600 font-bold">385 (3.1%)</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-pink-500 to-yellow-500 h-full rounded-full animate-pulse" style="width: 3.1%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Moderation reports list -->
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 bg-white/80 backdrop-blur-md mb-8">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 serif-font">🛡️ Moderation Queue</h2>
                <p class="text-gray-500 text-xs mt-0.5">Approve suspend actions for reported accounts or dismiss false flags.</p>
            </div>
        </div>

        <div id="status-box" class="hidden px-4 py-2.5 rounded-xl text-sm mb-6"></div>

        <?php if (empty($reports)): ?>
            <div class="text-center py-16 space-y-2">
                <span class="text-5xl block">🎉</span>
                <h4 class="font-bold text-gray-700 text-sm">No flags registered.</h4>
                <p class="text-xs text-gray-400">Profiles are fully aligned with safety rules.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-2xl border border-gray-200/50">
                <table class="w-full text-left border-collapse bg-white/30">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Report ID</th>
                            <th class="py-3.5 px-4">Reporter</th>
                            <th class="py-3.5 px-4">Reported</th>
                            <th class="py-3.5 px-4">Reason</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($reports as $r): ?>
                            <tr id="report-row-<?= $r['id'] ?>" class="hover:bg-white/40 transition">
                                <td class="py-4 px-4 font-mono font-bold text-gray-800">#<?= $r['id'] ?></td>
                                <td class="py-4 px-4 text-gray-600">User #<?= $r['reporter_id'] ?></td>
                                <td class="py-4 px-4 text-red-600 font-semibold">User #<?= $r['reported_id'] ?></td>
                                <td class="py-4 px-4 text-gray-700 max-w-xs truncate font-medium"><?= htmlspecialchars($r['reason']) ?></td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $r['status'] === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right space-x-1">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <button onclick="resolveReport(<?= $r['id'] ?>, 'dismissed')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs px-3.5 py-1.5 rounded-xl font-bold transition shadow-sm border border-gray-200">
                                            Dismiss
                                        </button>
                                        <button onclick="resolveReport(<?= $r['id'] ?>, 'suspended')" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3.5 py-1.5 rounded-xl font-bold transition shadow-md">
                                            Suspend User
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 font-bold capitalize">Resolved: <?= htmlspecialchars($r['action_taken'] ?: 'none') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- User Management Section -->
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 bg-white/80 backdrop-blur-md mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 serif-font">👥 User Accounts Management</h2>
                <p class="text-gray-500 text-xs mt-0.5">Toggle user account status (active/suspended), toggle subscription tiers, or delete accounts.</p>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1">
                <input type="text" id="user-search" placeholder="Search by name or email..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200/80 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white/60 text-sm font-semibold" />
            </div>
            <div class="flex gap-4">
                <select id="filter-tier" class="px-4 py-2.5 rounded-xl border border-gray-200/80 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white/60 text-sm font-semibold">
                    <option value="all">All Tiers</option>
                    <option value="free">Free</option>
                    <option value="premium">Premium</option>
                </select>
                <select id="filter-status" class="px-4 py-2.5 rounded-xl border border-gray-200/80 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white/60 text-sm font-semibold">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200/50">
            <table class="w-full text-left border-collapse bg-white/30">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">User ID</th>
                        <th class="py-3.5 px-4">Name</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Subscription Tier</th>
                        <th class="py-3.5 px-4">Account Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($users as $u): ?>
                        <tr id="user-row-<?= $u['id'] ?>" class="hover:bg-white/40 transition">
                            <td class="py-4 px-4 font-mono font-bold text-gray-800">#<?= $u['id'] ?></td>
                            <td class="py-4 px-4 font-semibold text-gray-800"><?= htmlspecialchars($u['name']) ?></td>
                            <td class="py-4 px-4 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="py-4 px-4 uppercase text-xs font-bold text-gray-500"><?= htmlspecialchars($u['role']) ?></td>
                            <td class="py-4 px-4 font-bold">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $u['tier'] === 'premium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= htmlspecialchars($u['tier']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 font-bold">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= ($u['status'] ?? 'active') === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                    <?= htmlspecialchars($u['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right space-x-1 whitespace-nowrap">
                                <button onclick="toggleUserTier(<?= $u['id'] ?>)" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1.5 rounded-xl font-bold transition shadow-sm">
                                    Toggle Tier
                                </button>
                                <button onclick="toggleUserStatus(<?= $u['id'] ?>)" class="bg-gray-800 hover:bg-black text-white text-xs px-3 py-1.5 rounded-xl font-bold transition shadow-sm">
                                    Toggle Status
                                </button>
                                <button onclick="deleteUser(<?= $u['id'] ?>)" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-xl font-bold transition shadow-sm">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- WordPress-style CMS Page/Blog Creator with Yoast/RankMath SEO Grader -->
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 bg-white/85 backdrop-blur-md mb-8">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 serif-font">📝 WordPress-style Page &amp; Blog Editor</h2>
                <p class="text-gray-500 text-xs mt-0.5">Manage public pages, write blog posts, and grade SEO in real-time with RankMath &amp; Yoast integration.</p>
            </div>
            <button onclick="openCmsModal()" class="bg-gradient-to-r from-pink-500 to-indigo-500 hover:from-pink-600 hover:to-indigo-600 text-white text-xs px-4 py-2.5 rounded-xl font-bold shadow-md transition">
                + Add Page or Post
            </button>
        </div>

        <div id="cms-status-box" class="hidden px-4 py-2.5 rounded-xl text-sm mb-6"></div>

        <!-- CMS Items List Table -->
        <div class="overflow-x-auto rounded-2xl border border-gray-200/50 mb-8 bg-white/30">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Title</th>
                        <th class="py-3.5 px-4">Slug</th>
                        <th class="py-3.5 px-4">Focus Keyword</th>
                        <th class="py-3.5 px-4">SEO Score</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="cms-items-list" class="divide-y divide-gray-100 text-sm">
                    <!-- Loaded dynamically via JS -->
                </tbody>
            </table>
        </div>

        <!-- WordPress Editor & SEO Grader Grid (Hidden by default, shown when adding/editing) -->
        <div id="cms-editor-container" class="hidden border-t border-gray-150 pt-6">
            <h3 id="cms-editor-title" class="text-lg font-bold text-gray-800 mb-4">Create New Page or Post</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Editor Form (2/3 width) -->
                <div class="lg:col-span-2 space-y-4">
                    <input type="hidden" id="cms-id" value="" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Content Type</label>
                            <select id="cms-type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white text-sm font-semibold">
                                <option value="post">Blog Post</option>
                                <option value="page">Custom Page</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Focus Keyword</label>
                            <input type="text" id="cms-focus-keyword" placeholder="e.g. safe dating" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white text-sm font-semibold" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Title</label>
                        <input type="text" id="cms-title" placeholder="Enter title..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white text-sm font-semibold" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Custom Slug</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-55 text-gray-500 text-xs font-semibold">
                                /blog/ or /page/
                            </span>
                            <input type="text" id="cms-slug" placeholder="e.g. safe-dating-tips" class="w-full px-4 py-2.5 rounded-r-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white text-sm font-semibold" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Meta Description</label>
                        <textarea id="cms-meta-desc" rows="2" placeholder="Write meta description for search engines..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white text-sm font-semibold"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Content Body</label>
                        <textarea id="cms-content" rows="12" placeholder="Write page content in HTML or plain text..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white text-sm font-mono"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeCmsEditor()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs px-4 py-2 rounded-xl font-bold transition">
                            Cancel
                        </button>
                        <button type="button" onclick="saveCmsContent()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-4 py-2.5 rounded-xl font-bold shadow-md transition">
                            Publish &amp; Save
                        </button>
                    </div>
                </div>

                <!-- Yoast/RankMath Real-time SEO Grader (1/3 width) -->
                <div class="glass-panel p-6 rounded-2xl border border-gray-200/50 bg-gray-50/50 flex flex-col space-y-6">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 flex items-center justify-between">
                            <span>🚦 Yoast &amp; RankMath SEO Grader</span>
                            <span id="seo-score-badge" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-red-100 text-red-700">0/100</span>
                        </h4>
                        <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden mt-3">
                            <div id="seo-score-bar" class="bg-red-500 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Grader Checklist -->
                    <div class="space-y-3 text-xs">
                        <div id="check-keyword" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Focus keyword is defined</span>
                        </div>
                        <div id="check-title" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Keyword exists in title</span>
                        </div>
                        <div id="check-slug" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Keyword exists in slug URL</span>
                        </div>
                        <div id="check-meta" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Keyword exists in Meta Description</span>
                        </div>
                        <div id="check-first-para" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Keyword is in first paragraph</span>
                        </div>
                        <div id="check-density" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Keyword density (ideal: 0.8% - 2.5%)</span>
                        </div>
                        <div id="check-length-title" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Title length (ideal: 40-60 chars)</span>
                        </div>
                        <div id="check-length-meta" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Meta Description length (ideal: 120-160 chars)</span>
                        </div>
                        <div id="check-wordcount" class="flex items-start gap-2 text-gray-500">
                            <span class="status-icon text-sm">❌</span>
                            <span class="label font-medium">Content contains at least 300 words</span>
                        </div>
                    </div>

                    <!-- SEO Advice Snippet -->
                    <div class="border-t border-gray-200/50 pt-4 mt-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Google Search Preview</span>
                        <div class="mt-2 bg-white border border-gray-200 p-3.5 rounded-xl space-y-1 shadow-sm">
                            <span id="preview-url" class="text-[10px] text-gray-550 block truncate">https://lgbtqmatrimony.local/blog/</span>
                            <span id="preview-title" class="text-sm text-indigo-700 font-semibold hover:underline cursor-pointer block leading-snug">Please enter a title</span>
                            <span id="preview-desc" class="text-xs text-gray-600 block leading-normal">Please write a meta description to see how it appears in Google SERP results.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- USER MANAGEMENT FILTER LOGIC ---
    function filterUsers() {
        const query = document.getElementById('user-search').value.toLowerCase();
        const tier = document.getElementById('filter-tier').value;
        const status = document.getElementById('filter-status').value;
        
        const rows = document.querySelectorAll('tbody tr[id^="user-row-"]');
        rows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const rowTier = row.cells[4].textContent.trim().toLowerCase();
            const rowStatus = row.cells[5].textContent.trim().toLowerCase();
            
            const matchesSearch = name.includes(query) || email.includes(query);
            const matchesTier = (tier === 'all') || (rowTier === tier);
            const matchesStatus = (status === 'all') || (rowStatus === status);
            
            if (matchesSearch && matchesTier && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // --- CMS HANDLERS & REAL-TIME SEO GRADER ---
    let cmsItems = [];

    async function loadCmsContents() {
        try {
            const res = await fetch('/api/v1/cms/contents', {
                headers: {
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                }
            });
            const data = await res.json();
            if (data.success && data.contents) {
                cmsItems = data.contents;
                renderCmsTable();
            }
        } catch (err) {
            console.error("Failed to load CMS contents:", err);
        }
    }

    function calculateSeoScore(item) {
        let score = 0;
        const title = (item.title || '').toLowerCase();
        const slug = (item.slug || '').toLowerCase();
        const meta = (item.meta_description || '').toLowerCase();
        const content = (item.content || '').toLowerCase();
        const kw = (item.focus_keyword || '').trim().toLowerCase();

        if (!kw) return 0;

        // 1. Keyword exists: +10
        score += 10;
        
        // 2. Keyword in title: +15
        if (title.includes(kw)) score += 15;
        
        // 3. Keyword in slug: +15
        if (slug.includes(kw)) score += 15;
        
        // 4. Keyword in meta description: +10
        if (meta.includes(kw)) score += 10;
        
        // 5. Keyword in first paragraph: +10
        const firstPara = content.split('\n')[0] || '';
        if (firstPara.includes(kw)) score += 10;
        
        // 6. Keyword density check: total matches / total words
        const words = content.split(/\s+/).filter(w => w.length > 0);
        if (words.length > 0 && kw) {
            const kwWords = kw.split(/\s+/).length;
            let matches = 0;
            let idx = content.indexOf(kw);
            while (idx !== -1) {
                matches++;
                idx = content.indexOf(kw, idx + 1);
            }
            const density = (matches * kwWords) / words.length;
            if (density >= 0.008 && density <= 0.025) {
                score += 15; // Ideal density
            } else if (density > 0) {
                score += 8; // Too low or too high
            }
        }
        
        // 7. Title length check (ideal: 40-60 chars): +10
        if (title.length >= 40 && title.length <= 60) {
            score += 10;
        } else if (title.length > 0) {
            score += 5;
        }

        // 8. Meta length check (ideal: 120-160 chars): +10
        if (meta.length >= 120 && meta.length <= 160) {
            score += 10;
        } else if (meta.length > 0) {
            score += 5;
        }

        // 9. Word count (ideal >= 300 words): +10
        if (words.length >= 300) {
            score += 10;
        } else if (words.length > 0) {
            score += 5;
        }

        return score;
    }

    function renderCmsTable() {
        const tbody = document.getElementById('cms-items-list');
        tbody.innerHTML = '';
        if (cmsItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-gray-400 font-semibold">No pages or posts created yet.</td></tr>`;
            return;
        }

        cmsItems.forEach(item => {
            const score = calculateSeoScore(item);
            let scoreClass = 'bg-red-100 text-red-700';
            if (score >= 85) scoreClass = 'bg-green-100 text-green-700';
            else if (score >= 50) scoreClass = 'bg-yellow-100 text-yellow-800';

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-white/40 transition';
            tr.innerHTML = `
                <td class="py-4 px-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${item.type === 'page' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700'}">${item.type}</span></td>
                <td class="py-4 px-4 font-semibold text-gray-800">${escapeHtml(item.title)}</td>
                <td class="py-4 px-4 text-gray-500 font-mono text-xs">/${item.type === 'page' ? 'page' : 'blog'}/${escapeHtml(item.slug)}</td>
                <td class="py-4 px-4 text-gray-600 font-semibold">${escapeHtml(item.focus_keyword || 'none')}</td>
                <td class="py-4 px-4"><span class="px-2 py-0.5 rounded-full text-xs font-black ${scoreClass}">${score}/100</span></td>
                <td class="py-4 px-4 text-right space-x-1 whitespace-nowrap">
                    <a href="/${item.type === 'page' ? 'page' : 'blog'}/${item.slug}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs px-3 py-1.5 rounded-xl font-bold transition inline-block">
                        View
                    </a>
                    <button onclick="editCmsItem(${item.id})" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-xl font-bold transition shadow-sm">
                        Edit
                    </button>
                    <button onclick="deleteCmsItem(${item.id})" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-xl font-bold transition shadow-sm">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updateLiveSeoGrader() {
        const title = document.getElementById('cms-title').value;
        const slug = document.getElementById('cms-slug').value;
        const meta = document.getElementById('cms-meta-desc').value;
        const content = document.getElementById('cms-content').value;
        const kw = document.getElementById('cms-focus-keyword').value.trim().toLowerCase();
        const type = document.getElementById('cms-type').value;

        const tempItem = { title, slug, meta_description: meta, content, focus_keyword: kw, type };
        const score = calculateSeoScore(tempItem);

        const badge = document.getElementById('seo-score-badge');
        badge.innerText = `${score}/100`;
        badge.className = `px-2.5 py-0.5 rounded-full text-xs font-black ${score >= 85 ? 'bg-green-100 text-green-700' : (score >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700')}`;

        const bar = document.getElementById('seo-score-bar');
        bar.style.width = `${score}%`;
        bar.className = `h-full rounded-full transition-all duration-300 ${score >= 85 ? 'bg-green-500' : (score >= 50 ? 'bg-yellow-500' : 'bg-red-500')}`;

        document.getElementById('preview-title').innerText = title || 'Please enter a title';
        document.getElementById('preview-desc').innerText = meta || 'Please write a meta description to see how it appears in Google SERP results.';
        document.getElementById('preview-url').innerText = `https://lgbtqmatrimony.local/${type === 'page' ? 'page' : 'blog'}/${slug || 'your-slug'}`;

        const setCheck = (id, passed) => {
            const el = document.getElementById(id);
            if (passed) {
                el.querySelector('.status-icon').innerText = '✅';
                el.classList.remove('text-gray-500');
                el.classList.add('text-green-600', 'font-semibold');
            } else {
                el.querySelector('.status-icon').innerText = '❌';
                el.classList.remove('text-green-600', 'font-semibold');
                el.classList.add('text-gray-500');
            }
        };

        const titleLower = title.toLowerCase();
        const slugLower = slug.toLowerCase();
        const metaLower = meta.toLowerCase();
        const contentLower = content.toLowerCase();

        setCheck('check-keyword', !!kw);
        setCheck('check-title', !!kw && titleLower.includes(kw));
        setCheck('check-slug', !!kw && slugLower.includes(kw));
        setCheck('check-meta', !!kw && metaLower.includes(kw));
        
        const firstPara = contentLower.split('\n')[0] || '';
        setCheck('check-first-para', !!kw && firstPara.includes(kw));

        const words = contentLower.split(/\s+/).filter(w => w.length > 0);
        let densityPassed = false;
        if (words.length > 0 && kw) {
            const kwWords = kw.split(/\s+/).length;
            let matches = 0;
            let idx = contentLower.indexOf(kw);
            while (idx !== -1) {
                matches++;
                idx = contentLower.indexOf(kw, idx + 1);
            }
            const density = (matches * kwWords) / words.length;
            densityPassed = (density >= 0.008 && density <= 0.025);
            document.getElementById('check-density').querySelector('.label').innerText = `Keyword density: ${(density * 100).toFixed(1)}% (ideal: 0.8% - 2.5%)`;
        } else {
            document.getElementById('check-density').querySelector('.label').innerText = 'Keyword density (ideal: 0.8% - 2.5%)';
        }
        setCheck('check-density', densityPassed);

        setCheck('check-length-title', title.length >= 40 && title.length <= 60);
        setCheck('check-length-meta', meta.length >= 120 && meta.length <= 160);
        setCheck('check-wordcount', words.length >= 300);
        document.getElementById('check-wordcount').querySelector('.label').innerText = `Content contains at least 300 words (${words.length} words)`;
    }

    function openCmsModal() {
        document.getElementById('cms-id').value = '';
        document.getElementById('cms-type').value = 'post';
        document.getElementById('cms-title').value = '';
        document.getElementById('cms-slug').value = '';
        document.getElementById('cms-slug').dataset.customized = '';
        document.getElementById('cms-focus-keyword').value = '';
        document.getElementById('cms-meta-desc').value = '';
        document.getElementById('cms-content').value = '';
        
        document.getElementById('cms-editor-title').innerText = 'Create New Page or Post';
        document.getElementById('cms-editor-container').classList.remove('hidden');
        document.getElementById('cms-editor-container').scrollIntoView({ behavior: 'smooth' });
        updateLiveSeoGrader();
    }

    function closeCmsEditor() {
        document.getElementById('cms-editor-container').classList.add('hidden');
    }

    function editCmsItem(id) {
        const item = cmsItems.find(c => c.id === id);
        if (!item) return;

        document.getElementById('cms-id').value = item.id;
        document.getElementById('cms-type').value = item.type;
        document.getElementById('cms-title').value = item.title;
        document.getElementById('cms-slug').value = item.slug;
        document.getElementById('cms-slug').dataset.customized = 'true';
        document.getElementById('cms-focus-keyword').value = item.focus_keyword || '';
        document.getElementById('cms-meta-desc').value = item.meta_description || '';
        document.getElementById('cms-content').value = item.content || '';

        document.getElementById('cms-editor-title').innerText = `Edit Content: ${item.title}`;
        document.getElementById('cms-editor-container').classList.remove('hidden');
        document.getElementById('cms-editor-container').scrollIntoView({ behavior: 'smooth' });
        updateLiveSeoGrader();
    }

    async function saveCmsContent() {
        const id = document.getElementById('cms-id').value;
        const type = document.getElementById('cms-type').value;
        const title = document.getElementById('cms-title').value.trim();
        const slug = document.getElementById('cms-slug').value.trim();
        const focus_keyword = document.getElementById('cms-focus-keyword').value.trim();
        const meta_description = document.getElementById('cms-meta-desc').value.trim();
        const content = document.getElementById('cms-content').value.trim();

        if (!title || !slug || !content) {
            alert("Title, slug, and content body are required!");
            return;
        }

        const payload = { id, type, title, slug, focus_keyword, meta_description, content };
        const statusBox = document.getElementById('cms-status-box');
        statusBox.classList.add('hidden');

        try {
            const res = await fetch('/api/v1/cms/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                statusBox.className = "bg-green-100 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.message || "CMS content saved successfully.";
                statusBox.classList.remove('hidden');
                
                closeCmsEditor();
                loadCmsContents();
            } else {
                statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.error || "Failed to save CMS content.";
                statusBox.classList.remove('hidden');
            }
        } catch (err) {
            statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
            statusBox.innerText = "Connection error.";
            statusBox.classList.remove('hidden');
        }
    }

    async function deleteCmsItem(id) {
        if (!confirm("Are you sure you want to delete this page/post? This action is permanent!")) return;
        
        const statusBox = document.getElementById('cms-status-box');
        statusBox.classList.add('hidden');

        try {
            const res = await fetch('/api/v1/cms/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                statusBox.className = "bg-green-100 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.message || "CMS content deleted.";
                statusBox.classList.remove('hidden');
                loadCmsContents();
            } else {
                statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.error || "Failed to delete CMS content.";
                statusBox.classList.remove('hidden');
            }
        } catch (err) {
            statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
            statusBox.innerText = "Connection error.";
            statusBox.classList.remove('hidden');
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // --- STANDARD RESOLVE FUNCTIONS ---
    async function resolveReport(id, action) {
        if (!confirm(`Are you sure you want to resolve report #${id} as ${action}?`)) return;

        const statusBox = document.getElementById('status-box');
        statusBox.classList.add('hidden');

        try {
            const res = await fetch(`/api/v1/moderation/admin/reports/${id}/resolve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify({ action })
            });
            const data = await res.json();

            if (data.success) {
                statusBox.className = "bg-green-100 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.message;
                statusBox.classList.remove('hidden');
                
                const row = document.getElementById(`report-row-${id}`);
                row.cells[5].innerHTML = `<span class="text-xs text-gray-400 font-bold capitalize">Resolved: ${action}</span>`;
                row.cells[4].innerHTML = `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700">reviewed</span>`;
            } else {
                statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.error || "Action failed.";
                statusBox.classList.remove('hidden');
            }
        } catch (err) {
            statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
            statusBox.innerText = "Connection error.";
            statusBox.classList.remove('hidden');
        }
    }

    async function toggleUserTier(id) {
        try {
            const res = await fetch(`/admin/users/${id}/toggle-tier`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                }
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || "Failed to toggle tier");
            }
        } catch (err) {
            alert("Connection error.");
        }
    }

    async function toggleUserStatus(id) {
        try {
            const res = await fetch(`/admin/users/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                }
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || "Failed to toggle status");
            }
        } catch (err) {
            alert("Connection error.");
        }
    }

    async function deleteUser(id) {
        if (!confirm("Are you sure you want to delete this user? This will also remove their profile.")) return;
        try {
            const res = await fetch(`/admin/users/${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                }
            });
            const data = await res.json();
            if (data.success) {
                const row = document.getElementById(`user-row-${id}`);
                if (row) row.remove();
            } else {
                alert(data.error || "Failed to delete user");
            }
        } catch (err) {
            alert("Connection error.");
        }
    }

    // --- DOMContentLoaded INIT ---
    document.addEventListener('DOMContentLoaded', () => {
        loadCmsContents();
        
        document.getElementById('user-search').addEventListener('input', filterUsers);
        document.getElementById('filter-tier').addEventListener('change', filterUsers);
        document.getElementById('filter-status').addEventListener('change', filterUsers);

        // SEO Grader automatic keyup registers
        document.getElementById('cms-title').addEventListener('keyup', updateLiveSeoGrader);
        document.getElementById('cms-slug').addEventListener('keyup', updateLiveSeoGrader);
        document.getElementById('cms-focus-keyword').addEventListener('keyup', updateLiveSeoGrader);
        document.getElementById('cms-meta-desc').addEventListener('keyup', updateLiveSeoGrader);
        document.getElementById('cms-content').addEventListener('keyup', updateLiveSeoGrader);
        
        document.getElementById('cms-title').addEventListener('change', updateLiveSeoGrader);
        document.getElementById('cms-slug').addEventListener('change', updateLiveSeoGrader);
        document.getElementById('cms-focus-keyword').addEventListener('change', updateLiveSeoGrader);
        document.getElementById('cms-meta-desc').addEventListener('change', updateLiveSeoGrader);
        document.getElementById('cms-content').addEventListener('change', updateLiveSeoGrader);
    });
</script>

<?php include __DIR__ . '/footer.php'; ?>
