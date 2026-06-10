<?php
include __DIR__ . '/header.php';

if (!$currentUser) {
    header('Location: /login');
    exit;
}

// Fetch Profile from profile-service
$profileResponse = makeApiRequest('GET', '/api/v1/profiles/me', [], $token);
$profile = null;
if ($profileResponse['status'] === 200 && isset($profileResponse['data']['profile'])) {
    $profile = $apiResponse['data']['profile'] ?? $profileResponse['data']['profile'];
}

// Calculate Profile Completeness Score
$score = 30; // base register score
if (!empty($profile['headline'])) $score += 15;
if (!empty($profile['about_me'])) $score += 20;
if (!empty($profile['pronouns'])) $score += 15;
if (!empty($profile['photos']) && count(json_decode($profile['photos'], true)) > 0) $score += 20;

// Fetch metrics from activity service endpoints
$viewsResponse = makeApiRequest('GET', '/api/v1/activity/views', [], $token);
$viewsCount = 0;
if ($viewsResponse['status'] === 200 && isset($viewsResponse['data']['views'])) {
    $viewsCount = count($viewsResponse['data']['views']);
}

$interestResponse = makeApiRequest('GET', '/api/v1/activity/interests/received', [], $token);
$likesCount = 0;
if ($interestResponse['status'] === 200 && isset($interestResponse['data']['interests'])) {
    $likesCount = count($interestResponse['data']['interests']);
}

// Fetch Matches Feed
$feedResponse = makeApiRequest('GET', '/api/v1/discovery/feed?min_age=18&max_age=60', [], $token);
$feed = [];
if ($feedResponse['status'] === 200 && isset($feedResponse['data']['feed'])) {
    $feed = $feedResponse['data']['feed'];
}
?>

<style>
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
        transition: all 0.2s ease;
        text-decoration: none;
        position: relative;
    }
    .sidebar-link:hover {
        background: rgba(244, 143, 177, 0.08);
        color: #ec407a;
    }
    .sidebar-link.active {
        background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);
        color: #c2185b;
        font-weight: 700;
        border-left: 3px solid #ec407a;
    }
    .sidebar-link .badge {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: #ef4444;
        color: white;
        font-size: 9px;
        font-weight: 800;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-card {
        background: white;
        border: 1px solid #f3f4f6;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        box-shadow: 0 8px 24px -8px rgba(244, 143, 177, 0.15);
        transform: translateY(-2px);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1;
    }
    .stat-label {
        font-size: 10px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-top: 6px;
    }
    .premium-banner {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #3730a3 100%);
        border-radius: 20px;
        padding: 20px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: white;
    }
    .premium-btn {
        background: linear-gradient(135deg, #ec407a 0%, #f06292 100%);
        color: white;
        padding: 10px 24px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    .premium-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px -6px rgba(236, 64, 122, 0.5);
    }
    .profile-card-dash {
        background: white;
        border: 1px solid #f3f4f6;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.25s ease;
        position: relative;
    }
    .profile-card-dash:hover {
        box-shadow: 0 12px 32px -8px rgba(244, 143, 177, 0.18);
        transform: translateY(-4px);
    }
    .online-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(16, 185, 129, 0.9);
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 8px;
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 5;
        backdrop-filter: blur(4px);
    }
    .online-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: white;
        display: block;
        animation: pulse-dot 1.5s infinite;
    }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .progress-bar-track {
        background: #f3f4f6;
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar-fill {
        background: linear-gradient(90deg, #f48fb1 0%, #ec407a 100%);
        height: 100%;
        border-radius: 3px;
        transition: width 0.6s ease;
    }
</style>

<div class="flex gap-6 max-w-7xl mx-auto my-4">
    <!-- ========= SIDEBAR ========= -->
    <aside class="w-64 shrink-0 hidden lg:block">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-24">
            <!-- Profile Avatar & Info -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-400 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
                    <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-gray-900 text-sm truncate"><?= htmlspecialchars($currentUser['name']) ?></h4>
                    <p class="text-xs text-gray-400 truncate">Mumbai, India</p>
                </div>
            </div>

            <!-- Profile Completeness -->
            <div class="mb-5">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-[10px] font-semibold text-gray-500">Profile Completeness</span>
                    <span class="text-[10px] font-bold text-gray-700"><?= $score ?>%</span>
                </div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" style="width: <?= $score ?>%"></div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1">
                <a href="/dashboard" class="sidebar-link active">
                    <span>🏠</span> Dashboard
                </a>
                <a href="/discovery" class="sidebar-link">
                    <span>🔍</span> Browse Profiles
                </a>
                <a href="/discovery?tab=matches" class="sidebar-link">
                    <span>❤️</span> My Matches
                </a>
                <a href="/discovery?tab=favorites" class="sidebar-link">
                    <span>⭐</span> Favorites
                </a>
                <a href="/chat" class="sidebar-link">
                    <span>💌</span> Messages
                </a>
                <a href="/notifications" class="sidebar-link">
                    <span>🔔</span> Notifications
                    <span class="badge">2</span>
                </a>
                <a href="/subscription" class="sidebar-link">
                    <span>💎</span> Upgrade Plan
                </a>
                <a href="/settings" class="sidebar-link">
                    <span>⚙️</span> Settings
                </a>
            </nav>

            <!-- Tier Status -->
            <?php if (($currentUser['tier'] ?? 'free') === 'premium'): ?>
                <div class="mt-5 bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-xl p-3 text-center">
                    <span class="text-xs font-bold text-amber-700">👑 Premium Active</span>
                </div>
            <?php else: ?>
                <div class="mt-5 bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-200 rounded-xl p-3 text-center">
                    <a href="/demo-premium" class="text-xs font-bold text-pink-600 hover:text-pink-700 transition">🔓 Activate Demo Premium</a>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- ========= MAIN CONTENT ========= -->
    <div class="flex-1 min-w-0 space-y-6">
        <!-- Premium Upgrade Banner -->
        <?php if (($currentUser['tier'] ?? 'free') !== 'premium'): ?>
            <div class="premium-banner">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">💎</span>
                    <div>
                        <h3 class="font-bold text-base">Unlock Connections with Premium</h3>
                        <p class="text-indigo-200 text-xs mt-0.5">See exact mobile numbers, direct Instagram IDs, send unlimited messages, and see who favorited your profile today.</p>
                    </div>
                </div>
                <a href="/subscription" class="premium-btn shrink-0">Get Premium — ₹499/mo</a>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card">
                <div class="stat-number text-indigo-600"><?= $viewsCount ?></div>
                <div class="stat-label">Profile Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-pink-600"><?= $likesCount ?></div>
                <div class="stat-label">Favorites Recd</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-emerald-600">
                    <?= ($currentUser['tier'] ?? 'free') === 'premium' ? '1' : '🔒' ?>
                </div>
                <div class="stat-label">Matches Today</div>
            </div>
        </div>

        <!-- Discover Today Section -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-extrabold text-gray-900">Discover Today</h2>
                <a href="/discovery" class="text-pink-600 hover:text-pink-700 text-xs font-bold transition">View All →</a>
            </div>

            <?php if (empty($feed)): ?>
                <div class="bg-white/80 backdrop-blur-sm p-12 rounded-2xl text-center border border-gray-100">
                    <span class="text-4xl">🧭</span>
                    <p class="text-gray-500 font-bold mt-2">Calculating match scores...</p>
                    <p class="text-gray-400 text-xs mt-1">Complete your partner criteria preferences under profile setup.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <?php 
                    $matchesLimit = array_slice($feed, 0, 6);
                    foreach ($matchesLimit as $item): 
                        $photos = json_decode($item['photos'] ?? '[]', true) ?: [];
                        $displayPhoto = !empty($photos) ? $photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
                        $scoreVal = rand(84, 98);
                        $isOnline = rand(0, 1);
                    ?>
                        <a href="/profile/<?= $item['user_id'] ?>" class="profile-card-dash block">
                            <?php if ($isOnline): ?>
                                <div class="online-badge">Online Now</div>
                            <?php endif; ?>

                            <div class="aspect-[4/5] bg-gray-100 overflow-hidden">
                                <img src="<?= htmlspecialchars($displayPhoto) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                     class="w-full h-full object-cover transition duration-500 hover:scale-105">
                            </div>

                            <div class="p-4">
                                <div class="flex justify-between items-start">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-gray-800 text-sm truncate"><?= htmlspecialchars($item['name']) ?></h4>
                                        <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($item['city'] ?: 'Unknown') ?>, <?= htmlspecialchars($item['country'] ?: '') ?></p>
                                    </div>
                                    <span class="bg-pink-50 text-pink-600 text-[10px] font-bold px-2 py-0.5 rounded-full border border-pink-100 shrink-0"><?= $scoreVal ?>%</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 text-base mb-4">Recent Activity</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-8 h-8 rounded-full bg-pink-50 flex items-center justify-center text-sm">👁️</span>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium text-xs">Someone viewed your profile</p>
                        <p class="text-gray-400 text-[10px]">2 hours ago</p>
                    </div>
                    <?php if (($currentUser['tier'] ?? 'free') !== 'premium'): ?>
                        <span class="text-[10px] font-bold text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full">Premium to see who</span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center text-sm">❤️</span>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium text-xs">You received a new interest</p>
                        <p class="text-gray-400 text-[10px]">5 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-sm">🎉</span>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium text-xs">Profile completeness improved to <?= $score ?>%</p>
                        <p class="text-gray-400 text-[10px]">Yesterday</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
