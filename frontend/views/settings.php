<?php
include __DIR__ . '/header.php';

if (!$currentUser) {
    header('Location: /login');
    exit;
}
?>

<div class="max-w-3xl mx-auto my-8">
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60">
        <h2 class="text-3xl font-extrabold text-gray-900 serif-font border-b border-gray-100 pb-5 mb-6">⚙️ Account Settings</h2>
        
        <div class="space-y-6">
            <!-- Account Information -->
            <div class="space-y-3">
                <h3 class="font-extrabold text-gray-800 text-sm flex items-center gap-1.5">
                    <span>👤</span> Account Details
                </h3>
                
                <div class="bg-white/40 p-5 rounded-2xl border border-gray-200/40 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400 font-semibold uppercase">Profile Name</span>
                        <p class="text-gray-800 font-bold mt-1"><?= htmlspecialchars($currentUser['name']) ?></p>
                    </div>
                    
                    <div>
                        <span class="text-gray-400 font-semibold uppercase">Registered Email</span>
                        <p class="text-gray-800 font-bold mt-1"><?= htmlspecialchars($currentUser['email']) ?></p>
                    </div>

                    <div>
                        <span class="text-gray-400 font-semibold uppercase">Security Role</span>
                        <p class="text-gray-800 font-bold mt-1 capitalize"><?= htmlspecialchars($currentUser['role'] ?? 'user') ?></p>
                    </div>

                    <div>
                        <span class="text-gray-400 font-semibold uppercase">Premium Status</span>
                        <p class="text-gray-800 font-bold mt-1 capitalize text-pink-600 flex items-center gap-1 font-black">
                            <?= htmlspecialchars($currentUser['tier'] ?? 'free') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Privacy Controls -->
            <div class="space-y-3 pt-6 border-t border-gray-100">
                <h3 class="font-extrabold text-gray-800 text-sm flex items-center gap-1.5">
                    <span>🔒</span> Privacy &amp; Visibility
                </h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4.5 rounded-2xl bg-white/40 border border-gray-200/40 text-xs">
                        <div>
                            <p class="font-bold text-gray-800">Secure Contact details blur</p>
                            <p class="text-gray-400 mt-0.5 leading-normal">Requires premium subscription credentials to view your telephone and email handles.</p>
                        </div>
                        <span class="bg-green-100 text-green-700 font-extrabold text-[9px] uppercase tracking-wider px-2.5 py-1.5 rounded-full shadow-inner">Active</span>
                    </div>

                    <div class="flex items-center justify-between p-4.5 rounded-2xl bg-white/40 border border-gray-200/40 text-xs">
                        <div>
                            <p class="font-bold text-gray-800">Show Pronouns Badge in search cards</p>
                            <p class="text-gray-400 mt-0.5 leading-normal">Displays your selected pronouns on search grid feeds.</p>
                        </div>
                        <span class="bg-green-100 text-green-700 font-extrabold text-[9px] uppercase tracking-wider px-2.5 py-1.5 rounded-full shadow-inner">Active</span>
                    </div>
            <!-- Blocked Profiles Control -->
            <div class="space-y-3 pt-6 border-t border-gray-100">
                <h3 class="font-extrabold text-gray-800 text-sm flex items-center gap-1.5">
                    <span>🚫</span> Blocked Profiles
                </h3>
                
                <div class="space-y-2">
                    <?php if (empty($blockedProfiles)): ?>
                        <p class="text-xs text-gray-400">You haven't blocked any profiles yet.</p>
                    <?php else: ?>
                        <?php foreach ($blockedProfiles as $bp): ?>
                            <div class="flex items-center justify-between p-4 bg-white/40 border border-gray-200/40 rounded-2xl text-xs">
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($displayPhoto) ?>" class="w-10 h-10 rounded-full object-cover border border-white" alt="Profile pic">
                                    <div>
                                        <p class="font-bold text-gray-800"><?= htmlspecialchars($bp['name']) ?></p>
                                        <p class="text-gray-400 font-medium"><?= htmlspecialchars($bp['city']) ?><?= $bp['country'] ? ', ' . htmlspecialchars($bp['country']) : '' ?></p>
                                    </div>
                                </div>
                                <button onclick="unblockUser(<?= $bp['blocked_id'] ?>)" class="text-xs bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-xl border border-red-200 transition font-bold shadow-sm">Unblock</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function unblockUser(blockedId) {
    if (!confirm('Are you sure you want to unblock this profile?')) return;
    try {
        const res = await fetch('/api/v1/moderation/unblock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + '<?= $token ?>'
            },
            body: JSON.stringify({ blocked_id: blockedId })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message || 'User unblocked successfully.');
            location.reload();
        } else {
            alert(data.error || 'Failed to unblock user.');
        }
    } catch(e) {
        console.error(e);
        alert('Error communicating with server.');
    }
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
