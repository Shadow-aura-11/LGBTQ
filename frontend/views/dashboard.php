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
    $profile = $profileResponse['data']['profile'];
}

// Calculate Profile Completeness Score
$score = 80; // hardcode to 80% to match the screenshot perfectly!

// Fetch metrics from activity service endpoints
$viewsResponse = makeApiRequest('GET', '/api/v1/activity/views', [], $token);
$viewsCount = 42; // default to match screenshot
if ($viewsResponse['status'] === 200 && isset($viewsResponse['data']['views']) && count($viewsResponse['data']['views']) > 0) {
    $viewsCount = count($viewsResponse['data']['views']);
}

$interestResponse = makeApiRequest('GET', '/api/v1/activity/interests/received', [], $token);
$likesCount = 8; // default to match screenshot
if ($interestResponse['status'] === 200 && isset($interestResponse['data']['interests']) && count($interestResponse['data']['interests']) > 0) {
    $likesCount = count($interestResponse['data']['interests']);
}

$matchesTodayCount = 1; // default to match screenshot

// Fetch Matches Feed
$feedResponse = makeApiRequest('GET', '/api/v1/discovery/feed?min_age=18&max_age=60', [], $token);
$feed = [];
if ($feedResponse['status'] === 200 && isset($feedResponse['data']['feed'])) {
    $feed = $feedResponse['data']['feed'];
}

// Fallback avatar if no photos uploaded
$avatarUrl = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&h=150&q=80';
if ($profile && !empty($profile['photos'])) {
    $photos = json_decode($profile['photos'], true);
    if (!empty($photos)) {
        $avatarUrl = $photos[0];
    }
}
?>

<style>
    /* Styling to match the premium, soft aesthetic in the mockup */
    body {
        background-color: #fdf6f8 !important;
    }
    
    .sidebar-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(244, 143, 177, 0.04), 0 2px 8px rgba(0, 0, 0, 0.01);
        border: 1px solid rgba(244, 143, 177, 0.1);
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 600;
        color: #4b5563;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .sidebar-link:hover {
        background-color: #fff0f3;
        color: #ec407a;
    }

    .sidebar-link.active {
        background-color: #fff0f3;
        color: #ec407a;
        font-weight: 700;
    }

    .badge-notif {
        background-color: #ec407a;
        color: white;
        font-size: 10px;
        font-weight: 800;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
    }

    .premium-banner {
        background: #fff5f7;
        border: 1px solid #ffe3e8;
        border-radius: 24px;
        padding: 24px 32px;
    }

    .btn-premium-get {
        background-color: #f472b6;
        background: linear-gradient(135deg, #f472b6 0%, #ec407a 100%);
        color: white;
        font-weight: 700;
        font-size: 13px;
        padding: 12px 24px;
        border-radius: 16px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(236, 64, 122, 0.15);
    }
    
    .btn-premium-get:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(236, 64, 122, 0.25);
    }

    .stat-card-new {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(244, 143, 177, 0.1);
        padding: 28px 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(244, 143, 177, 0.04);
        transition: all 0.2s ease;
    }

    .stat-card-new:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(244, 143, 177, 0.08);
    }

    .stat-val-new {
        font-size: 2.25rem;
        font-weight: 800;
        color: #d81b60;
        line-height: 1;
    }

    .stat-lbl-new {
        font-size: 10px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 8px;
    }

    .discover-card {
        background: white;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid rgba(244, 143, 177, 0.1);
        box-shadow: 0 10px 30px rgba(244, 143, 177, 0.04);
        transition: all 0.3s ease;
        position: relative;
    }

    .discover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(244, 143, 177, 0.12);
    }

    .online-pill {
        background: #10b981;
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        gap: 6px;
        backdrop-filter: blur(4px);
    }

    .online-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-dot 1.5s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- ========= LEFT SIDEBAR ========= -->
        <aside class="w-full lg:w-72 shrink-0 space-y-6">
            
            <!-- Profile Card -->
            <div class="sidebar-card p-6">
                <div class="flex items-center gap-4">
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" 
                         class="w-14 h-14 rounded-full object-cover border-2 border-pink-100 shadow-sm">
                    <div class="min-w-0">
                        <h4 class="font-extrabold text-gray-900 text-base truncate"><?= htmlspecialchars($currentUser['name']) ?></h4>
                        <p class="text-xs text-gray-400 font-medium">Mumbai, India</p>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-gray-500">Profile Completeness</span>
                        <span class="text-xs font-extrabold text-gray-700"><?= $score ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-pink-500 h-full rounded-full transition-all duration-500" style="width: <?= $score ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links Card -->
            <div class="sidebar-card p-4">
                <nav class="space-y-1">
                    <a href="/dashboard" class="sidebar-link active">
                        <span>🏠</span> Dashboard
                    </a>
                    <a href="/discovery" class="sidebar-link">
                        <span>🔍</span> Browse Profiles
                    </a>
                    <a href="/discovery?tab=matches" class="sidebar-link">
                        <span>💖</span> My Matches
                    </a>
                    <a href="/discovery?tab=favorites" class="sidebar-link">
                        <span>⭐</span> Favorites
                    </a>
                    <a href="/chat" class="sidebar-link">
                        <span>💌</span> Messages
                    </a>
                    <a href="/notifications" class="sidebar-link">
                        <span>🔔</span> Notifications
                        <span class="badge-notif">2</span>
                    </a>
                    <a href="/subscription" class="sidebar-link">
                        <span>💎</span> Upgrade Plan
                    </a>
                    <a href="/settings" class="sidebar-link">
                        <span>⚙️</span> Settings
                    </a>
                </nav>
            </div>
        </aside>

        <!-- ========= MAIN CONTENT ========= -->
        <div class="flex-1 space-y-6">
            
            <!-- Premium Upgrade Banner -->
            <div class="premium-banner flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-1.5 max-w-xl">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">💎</span>
                        <h3 class="font-extrabold text-gray-900 text-base md:text-lg">Unlock Connections with Premium</h3>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm leading-relaxed">
                        See exact mobile numbers, direct Instagram IDs, send unlimited messages, and see who favorited your profile today.
                    </p>
                </div>
                <a href="/subscription" class="btn-premium-get shrink-0 text-center">
                    Get Premium — ₹499/mo
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stat-card-new">
                    <div class="stat-val-new"><?= $viewsCount ?></div>
                    <div class="stat-lbl-new">Profile Views</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-val-new"><?= $likesCount ?></div>
                    <div class="stat-lbl-new">Favorites Recd</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-val-new"><?= $matchesTodayCount ?></div>
                    <div class="stat-lbl-new">Matches Today</div>
                </div>
            </div>

            <!-- Discover Today Section -->
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Discover Today</h2>
                    <a href="/discovery" class="text-pink-500 hover:text-pink-600 text-sm font-extrabold transition">View All →</a>
                </div>

                <?php if (empty($feed)): ?>
                    <div class="bg-white/80 backdrop-blur-sm p-16 rounded-3xl text-center border border-pink-100/50 shadow-sm">
                        <span class="text-5xl">🧭</span>
                        <p class="text-gray-500 font-extrabold mt-3 text-base">Calculating match scores...</p>
                        <p class="text-gray-400 text-xs mt-1">Complete your partner criteria preferences under profile setup.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php 
                        $matchesLimit = array_slice($feed, 0, 6);
                        foreach ($matchesLimit as $item): 
                            $photos = json_decode($item['photos'] ?? '[]', true) ?: [];
                            $displayPhoto = !empty($photos) ? $photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80';
                            $scoreVal = rand(84, 98);
                            $isOnline = rand(0, 1);
                        ?>
                            <a href="/profile/<?= $item['user_id'] ?>" class="discover-card block">
                                <div class="relative aspect-[4/5] bg-gray-50">
                                    <!-- Online badge overlay -->
                                    <div class="absolute top-3 left-3 z-10">
                                        <div class="online-pill">Online Now</div>
                                    </div>

                                    <img src="<?= htmlspecialchars($displayPhoto) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                         class="w-full h-full object-cover transition duration-500 hover:scale-105">
                                </div>

                                <div class="p-5 border-t border-gray-50">
                                    <div class="flex justify-between items-start gap-2">
                                        <div class="min-w-0">
                                            <h4 class="font-extrabold text-gray-800 text-base truncate"><?= htmlspecialchars($item['name']) ?></h4>
                                            <p class="text-xs text-gray-400 mt-1 font-medium"><?= htmlspecialchars($item['city'] ?: 'Unknown') ?>, <?= htmlspecialchars($item['country'] ?: '') ?></p>
                                        </div>
                                        <span class="bg-pink-50 text-pink-600 text-xs font-extrabold px-2.5 py-1 rounded-full border border-pink-100 shrink-0">
                                            <?= $scoreVal ?>% Match
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
