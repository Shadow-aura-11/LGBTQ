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

<div class="space-y-8 max-w-7xl mx-auto my-6">
    <!-- Top Dashboard Header widget -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-4xl font-extrabold text-gray-900 serif-font">Welcome back, <?= htmlspecialchars($currentUser['name']) ?></h2>
            <p class="text-gray-600 text-sm">Here is your matchmaking overview for today.</p>
        </div>
        
        <?php if (($currentUser['tier'] ?? 'free') !== 'premium'): ?>
            <a href="/subscription" class="bg-gradient-to-r from-pink-500 to-indigo-600 text-white px-6 py-3.5 rounded-full font-bold text-sm shadow-lg hover:shadow-xl transition shrink-0 animate-pulse">
                👑 Go Premium &amp; Unlock Contacts
            </a>
        <?php endif; ?>
    </div>

    <!-- Metrics Cards & Completeness block -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Profile Completeness Card -->
        <div class="lg:col-span-1 glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex flex-col justify-between items-center text-center">
            <h4 class="font-extrabold text-gray-800 text-sm mb-2">Profile Completeness</h4>
            
            <div class="relative w-28 h-28 flex items-center justify-center">
                <!-- Circular SVG Progress bar -->
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="56" cy="56" r="48" stroke="#f3f4f6" stroke-width="8" fill="transparent" />
                    <circle cx="56" cy="56" r="48" stroke="#ec7ca4" stroke-width="8" fill="transparent" 
                            stroke-dasharray="301.6" stroke-dashoffset="<?= 301.6 - (301.6 * $score) / 100 ?>" 
                            class="transition-all duration-500" />
                </svg>
                <span class="absolute text-xl font-black text-gray-800"><?= $score ?>%</span>
            </div>

            <p class="text-xs text-gray-500 mt-3">Complete your bio details to show in search priority feeds.</p>
            <a href="/profile/setup" class="mt-4 text-xs font-bold text-pink-600 hover:text-pink-700 transition">Update Profile &rarr;</a>
        </div>

        <!-- Metrics Widgets -->
        <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4">
                <span class="text-4xl bg-indigo-150 p-3.5 rounded-2xl">👁️</span>
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Profile Views</span>
                    <h4 class="text-3xl font-extrabold text-gray-800 mt-1"><?= $viewsCount ?></h4>
                    <p class="text-[10px] text-gray-400 mt-0.5">Users who checked your card</p>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4">
                <span class="text-4xl bg-pink-150 p-3.5 rounded-2xl">❤️</span>
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Interests Received</span>
                    <h4 class="text-3xl font-extrabold text-gray-800 mt-1"><?= $likesCount ?></h4>
                    <p class="text-[10px] text-gray-400 mt-0.5">Singles expressing interest</p>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4">
                <span class="text-4xl bg-purple-150 p-3.5 rounded-2xl">💍</span>
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mutual Matches</span>
                    <h4 class="text-3xl font-extrabold text-gray-800 mt-1">
                        <?= ($currentUser['tier'] ?? 'free') === 'premium' ? count($feed) > 0 ? 1 : 0 : 'Locked' ?>
                    </h4>
                    <p class="text-[10px] text-gray-400 mt-0.5">Ready to initiate chat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Match Feed Summary Section -->
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-extrabold text-gray-900 serif-font">Discover Today</h3>
            <a href="/discovery" class="text-pink-600 hover:underline text-sm font-semibold transition">See All Filters &rarr;</a>
        </div>

        <?php if (empty($feed)): ?>
            <div class="glass-panel p-12 rounded-3xl text-center border border-white/60">
                <span class="text-4xl">🧭</span>
                <p class="text-gray-500 font-bold mt-2">Calculating match scores...</p>
                <p class="text-gray-400 text-xs mt-1">Complete your partner criteria preferences under profile setup.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php 
                $matchesLimit = array_slice($feed, 0, 4);
                foreach ($matchesLimit as $item): 
                    $photos = json_decode($item['photos'] ?? '[]', true) ?: [];
                    $displayPhoto = !empty($photos) ? $photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
                    $scoreVal = rand(84, 98);
                ?>
                    <div class="glass-panel rounded-3xl overflow-hidden border border-white/60 flex flex-col justify-between h-full hover:shadow-lg transition group relative">
                        <div class="absolute top-2.5 right-2.5 z-10 bg-white/90 backdrop-blur px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm border border-pink-100">
                            <span class="text-pink-600 font-black text-xs"><?= $scoreVal ?>%</span>
                            <span class="text-[8px] text-gray-500 uppercase font-bold tracking-wider">match</span>
                        </div>

                        <div class="relative aspect-[3/4] bg-gray-100 overflow-hidden">
                            <img src="<?= htmlspecialchars($displayPhoto) ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                        </div>

                        <div class="p-4 space-y-3">
                            <div>
                                <h4 class="font-bold text-gray-800 text-base line-clamp-1"><?= htmlspecialchars($item['name']) ?></h4>
                                <p class="text-[9px] text-pink-600 font-bold uppercase tracking-wider mt-0.5"><?= htmlspecialchars($item['gender_identity']) ?></p>
                            </div>
                            <a href="/profile/<?= $item['user_id'] ?>" class="w-full block text-center bg-pink-50 hover:bg-pink-100 text-pink-700 py-2.5 rounded-xl text-xs font-bold transition">
                                View Profile
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
