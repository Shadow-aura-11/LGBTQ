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
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
