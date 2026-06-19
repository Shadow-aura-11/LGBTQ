<?php
include __DIR__ . '/header.php';
?>
<style>
    /* Sexual Orientation color badges */
    .orientation-tag-lesbian { background-color: #db2777 !important; color: white !important; }
    .orientation-tag-gay { background-color: #1d4ed8 !important; color: white !important; }
    .orientation-tag-pansexual { background-color: #d97706 !important; color: white !important; }
    .orientation-tag-bisexual { background-color: #7c3aed !important; color: white !important; }
    .orientation-tag-queer { background-color: #4f46e5 !important; color: white !important; }
</style>
<?php

if (!$currentUser) {
    header('Location: /login');
    exit;
}

$gender = $_GET['gender_identity'] ?? '';
$orientation = $_GET['sexual_orientation'] ?? '';
$city = $_GET['city'] ?? '';

$minAge = $_GET['min_age'] ?? '18';
$maxAge = $_GET['max_age'] ?? '60';

// Fetch Matches via discovery endpoint
$apiPath = "/api/v1/discovery/feed?min_age=$minAge&max_age=$maxAge&gender_identity=$gender&sexual_orientation=$orientation&city=$city";
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
                        <?php if (!$isPremium): ?>
                            <span class="bg-gradient-to-r from-yellow-400 to-amber-500 text-white text-[9px] px-2 py-0.5 rounded font-extrabold uppercase shadow-sm">👑 VIP</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">City Location</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($city) ?>" placeholder="e.g. London" 
                               <?= !$isPremium ? 'disabled title="Requires Premium Tier" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-100/50 text-sm outline-none cursor-not-allowed"' : 'class="w-full px-3 py-2.5 rounded-xl border border-gray-300 bg-white/50 text-sm outline-none transition focus:ring-2 focus:ring-pink-300"' ?>>
                    </div>


                </div>

                <button type="submit" class="w-full btn-primary py-3 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                    Search Matches
                </button>
            </form>
        </div>

        <!-- Premium CTA widget -->
        <?php if (!$isPremium): ?>
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
                    <div class="discover-card bg-white rounded-3xl overflow-hidden border border-gray-150 shadow-md flex flex-col h-full relative group">
                        <!-- Image Container -->
                        <div class="relative aspect-[4/5] bg-gray-50 overflow-hidden">
                            <!-- Online badge overlay -->
                            <div class="absolute top-4 left-4 z-10">
                                <div class="bg-[#10b981] text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm">Online Now</div>
                            </div>

                            <img src="<?= htmlspecialchars($displayPhoto) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="w-full h-full object-cover transition duration-500 hover:scale-105">
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 flex flex-col flex-grow justify-between space-y-4">
                            <div>
                                <!-- Name & Age -->
                                <h4 class="font-extrabold text-gray-900 text-lg">
                                    <?= htmlspecialchars($item['name']) ?>, <?= date_diff(date_create($item['date_of_birth']), date_create('today'))->y ?>
                                </h4>
                                <!-- Location -->
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= htmlspecialchars($item['city']) ?>, <?= htmlspecialchars($item['country']) ?>
                                </p>

                                <!-- Tags -->
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <span class="orientation-tag-<?= htmlspecialchars($item['sexual_orientation']) ?> text-[10px] font-extrabold px-2.5 py-1 rounded-md uppercase tracking-wider">
                                        <?= htmlspecialchars($item['sexual_orientation']) ?>
                                    </span>
                                    <span class="bg-[#fdf2f8] text-[#86198f] text-[10px] font-extrabold px-2.5 py-1 rounded-md">
                                        <?= htmlspecialchars($item['gender_identity'] === 'other' ? ($item['gender_custom'] ?: 'Other') : $item['gender_identity']) ?>
                                    </span>
                                </div>

                                <!-- Bio snippet -->
                                <p class="text-xs text-gray-500 mt-4 italic leading-relaxed line-clamp-2">
                                    "<?= htmlspecialchars($item['headline'] ?? 'Seeking a genuine connection.') ?>"
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-2">
                                <a href="/profile/<?= $item['user_id'] ?>" 
                                   class="flex-1 text-center bg-white border border-[#ec4899] text-[#ec4899] py-2.5 rounded-xl text-xs font-bold transition hover:bg-[#fdf2f8] shadow-sm">
                                    View Details
                                </a>
                                
                                <button onclick="quickLike(<?= $item['user_id'] ?>, this)" 
                                        class="flex-1 bg-[#ec4899] hover:bg-[#db2777] text-white py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                    Connect
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
