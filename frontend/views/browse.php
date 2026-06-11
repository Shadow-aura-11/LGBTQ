<?php
include __DIR__ . '/header.php';

if (!$currentUser) {
    header('Location: /login');
    exit;
}

$gender = $_GET['gender_identity'] ?? '';
$orientation = $_GET['sexual_orientation'] ?? '';
$city = $_GET['city'] ?? '';
$intent = $_GET['relationship_intent'] ?? '';
$minAge = $_GET['min_age'] ?? '18';
$maxAge = $_GET['max_age'] ?? '60';

// Fetch Matches via discovery endpoint
$apiPath = "/api/v1/discovery/feed?min_age=$minAge&max_age=$maxAge&gender_identity=$gender&sexual_orientation=$orientation&city=$city&relationship_intent=$intent";
$feedResponse = makeApiRequest('GET', $apiPath, [], $token);
$feed = [];
if ($feedResponse['status'] === 200 && isset($feedResponse['data']['feed'])) {
    $feed = $feedResponse['data']['feed'];
}
// Fallback connection directly to local database feed injected in context
if (empty($feed) && isset($context['feed'])) {
    $feed = $context['feed'];
}
?>

<!-- Discovery Top Banner -->
<div class="relative overflow-hidden rounded-3xl mb-10 bg-gradient-to-r from-pink-500/10 via-purple-500/5 to-indigo-500/10 p-8 md:p-12 border border-white/40 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6">
    <div class="space-y-3 max-w-xl text-center md:text-left">
        <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1.5 rounded-full font-bold uppercase tracking-wider">Compass Match Engine</span>
        <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight serif-font">Explore Highly Compatible Partners</h2>
        <p class="text-gray-600 text-sm md:text-base">We calculate compatibility scores dynamically using orientation, lifestyle intent, and pronouns representation.</p>
    </div>
    <div class="shrink-0 flex items-center justify-center bg-white/80 w-24 h-24 rounded-full shadow-md border border-pink-200">
        <span class="text-4xl animate-bounce">🧭</span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Filters Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm h-fit">
            <h3 class="font-bold text-gray-800 text-base mb-4 flex items-center gap-2 pb-3 border-b border-gray-100">
                <span>⚙️</span> Match Settings
            </h3>
            
            <form method="GET" action="/discovery" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Gender Identity</label>
                    <select name="gender_identity" class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300">
                        <option value="">Any</option>
                        <option value="man" <?= $gender === 'man' ? 'selected' : '' ?>>Man</option>
                        <option value="woman" <?= $gender === 'woman' ? 'selected' : '' ?>>Woman</option>
                        <option value="non-binary" <?= $gender === 'non-binary' ? 'selected' : '' ?>>Non-Binary</option>
                        <option value="transgender man" <?= $gender === 'transgender man' ? 'selected' : '' ?>>Transgender Man</option>
                        <option value="transgender woman" <?= $gender === 'transgender woman' ? 'selected' : '' ?>>Transgender Woman</option>
                        <option value="genderqueer" <?= $gender === 'genderqueer' ? 'selected' : '' ?>>Genderqueer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Sexual Orientation</label>
                    <select name="sexual_orientation" class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300">
                        <option value="">Any</option>
                        <option value="gay" <?= $orientation === 'gay' ? 'selected' : '' ?>>Gay</option>
                        <option value="lesbian" <?= $orientation === 'lesbian' ? 'selected' : '' ?>>Lesbian</option>
                        <option value="bisexual" <?= $orientation === 'bisexual' ? 'selected' : '' ?>>Bisexual</option>
                        <option value="pansexual" <?= $orientation === 'pansexual' ? 'selected' : '' ?>>Pansexual</option>
                        <option value="queer" <?= $orientation === 'queer' ? 'selected' : '' ?>>Queer</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Min Age</label>
                        <input type="number" name="min_age" value="<?= htmlspecialchars($minAge) ?>" min="18" max="99" class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Max Age</label>
                        <input type="number" name="max_age" value="<?= htmlspecialchars($maxAge) ?>" min="18" max="99" class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300">
                    </div>
                </div>

                <div class="border-t border-gray-200/50 pt-4 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-700 uppercase">Premium Filters</span>
                        <?php if (($currentUser['tier'] ?? 'free') !== 'premium'): ?>
                            <span class="bg-gradient-to-r from-yellow-400 to-amber-500 text-white text-[9px] px-2 py-0.5 rounded font-extrabold uppercase shadow-sm">👑 VIP</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">City Location</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($city) ?>" placeholder="e.g. London" 
                               <?= ($currentUser['tier'] ?? 'free') !== 'premium' ? 'disabled title="Requires Premium Tier" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-100/50 text-sm outline-none cursor-not-allowed"' : 'class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300"' ?>>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Relationship Intent</label>
                        <select name="relationship_intent" 
                                <?= ($currentUser['tier'] ?? 'free') !== 'premium' ? 'disabled class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-100/50 text-sm outline-none cursor-not-allowed"' : 'class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300"' ?>>
                            <option value="">Any</option>
                            <option value="friendship" <?= $intent === 'friendship' ? 'selected' : '' ?>>Friendship</option>
                            <option value="dating" <?= $intent === 'dating' ? 'selected' : '' ?>>Dating</option>
                            <option value="long-term" <?= $intent === 'long-term' ? 'selected' : '' ?>>Long-Term</option>
                            <option value="marriage" <?= $intent === 'marriage' ? 'selected' : '' ?>>Marriage</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full btn-primary py-3 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                    Search Matches
                </button>
            </form>
        </div>

        <!-- Premium CTA widget -->
        <?php if (($currentUser['tier'] ?? 'free') !== 'premium'): ?>
            <div class="bg-gradient-to-tr from-pink-500 to-indigo-600 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden border border-pink-400/20">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full"></div>
                <h4 class="font-extrabold text-lg flex items-center gap-2">👑 VIP Lounge</h4>
                <p class="text-xs text-pink-100 mt-2 leading-relaxed">Upgrade to view and download user high-res photos, access direct phone/email contacts, and message matches in real-time!</p>
                <a href="/subscription" class="mt-4 w-full block text-center bg-white text-pink-600 py-2.5 rounded-xl text-xs font-bold hover:bg-pink-50 transition shadow">Upgrade Now</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Match Feed Results -->
    <div class="lg:col-span-3 space-y-6">
        <?php if (empty($feed)): ?>
            <div class="glass-panel p-16 rounded-3xl text-center border border-white/60">
                <span class="text-5xl">🔭</span>
                <p class="text-gray-500 text-lg font-bold mt-4">Looking for connections...</p>
                <p class="text-gray-400 text-sm mt-1">Try broadening your filters or clearing search criteria to show more profiles.</p>
                <a href="/discovery" class="mt-4 inline-block text-pink-600 hover:text-pink-700 font-semibold text-sm transition">Reset Feed &larr;</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($feed as $item): 
                    $photos = json_decode($item['photos'] ?? '[]', true) ?: [];
                    $displayPhoto = !empty($photos) ? $photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
                    $comp = rand(82, 99); // Mock compatibility score
                ?>
                    <div class="glass-panel rounded-3xl overflow-hidden border border-white/60 flex flex-col h-full card-premium relative group">
                        <!-- Compatibility Score Overlay (eharmony style) -->
                        <div class="absolute top-3 right-3 z-10 bg-white/95 backdrop-blur px-3 py-1.5 rounded-full flex items-center gap-1 shadow-sm border border-pink-100">
                            <span class="text-pink-600 font-extrabold text-xs"><?= $comp ?>%</span>
                            <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">match</span>
                        </div>

                        <!-- Image Container with visual scaling -->
                        <div class="relative aspect-[3/4] bg-gray-100 overflow-hidden">
                            <img src="<?= htmlspecialchars($displayPhoto) ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                            
                            <!-- Badges Overlay -->
                            <div class="absolute bottom-3 left-3 flex flex-wrap gap-1">
                                <span class="bg-black/60 backdrop-blur-sm text-white text-[9px] px-2 py-0.5 rounded-full font-bold">
                                    <?= htmlspecialchars($item['pronouns'] ?? 'they/them') ?>
                                </span>
                                <span class="bg-pink-600/80 backdrop-blur-sm text-white text-[9px] px-2 py-0.5 rounded-full font-bold">
                                    <?= htmlspecialchars($item['sexual_orientation'] ?? 'queer') ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
                            <div>
                                <div class="flex justify-between items-center">
                                    <h4 class="font-bold text-gray-800 text-lg line-clamp-1 group-hover:text-pink-600 transition">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </h4>
                                    <span class="text-xs font-extrabold text-gray-500 bg-gray-100/80 px-2 py-0.5 rounded-lg border border-gray-200/50">
                                        <?= date_diff(date_create($item['date_of_birth']), date_create('today'))->y ?> yrs
                                    </span>
                                </div>
                                <p class="text-[10px] text-pink-600 font-bold uppercase mt-1 tracking-wider">
                                    <?= htmlspecialchars($item['gender_identity'] === 'other' ? ($item['gender_custom'] ?: 'Other') : $item['gender_identity']) ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1 font-medium">
                                    <span>📍</span> <?= htmlspecialchars($item['city'] ?: 'San Francisco') ?>, <?= htmlspecialchars($item['country'] ?: 'USA') ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-3 line-clamp-2 italic leading-relaxed">
                                    "<?= htmlspecialchars($item['headline'] ?: 'Seeking a genuine, compatible partner.') ?>"
                                </p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex gap-2">
                                <a href="/profile/<?= $item['user_id'] ?>" class="flex-grow block text-center bg-pink-50 hover:bg-pink-100 text-pink-700 py-3 rounded-xl text-xs font-bold transition shadow-sm border border-pink-200/40">
                                    View Profile &rarr;
                                </a>
                                <button onclick="quickLike(<?= $item['user_id'] ?>, this)" class="px-4 bg-pink-500 hover:bg-pink-600 text-white rounded-xl text-xs font-bold transition shadow-sm border border-pink-600/20" title="Like Profile">
                                    ❤️
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    async function quickLike(targetId, button) {
        try {
            const res = await fetch('/api/v1/activity/interest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify({ target_id: targetId })
            });
            const data = await res.json();
            if (data.success) {
                button.innerHTML = '💖';
                button.disabled = true;
                button.classList.remove('bg-pink-500', 'hover:bg-pink-600');
                button.classList.add('bg-pink-100', 'text-pink-600', 'cursor-not-allowed');
                alert(data.mutual ? "🎉 Mutual Match! You can now chat directly in real-time!" : "Interest sent!");
            } else {
                alert(data.error || "Failed to send interest.");
            }
        } catch (err) {
            alert("Connection error.");
        }
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
