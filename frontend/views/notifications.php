<?php
include __DIR__ . '/header.php';

if (!$currentUser) {
    header('Location: /login');
    exit;
}

$notifResponse = makeApiRequest('GET', '/api/v1/notifications', [], $token);
$notifications = [];
if ($notifResponse['status'] === 200 && isset($notifResponse['data']['notifications'])) {
    $notifications = $notifResponse['data']['notifications'];
}
?>

<div class="max-w-3xl mx-auto my-8">
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60">
        <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-5">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 serif-font">🔔 Match Notifications</h2>
                <p class="text-gray-600 text-sm">Review profile views, mutual interest alerts, and incoming message logs.</p>
            </div>
            <span class="text-2xl">⚡</span>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="text-center py-16 space-y-3">
                <span class="text-5xl block animate-pulse">📭</span>
                <h4 class="font-bold text-gray-700 text-base">All Caught Up!</h4>
                <p class="text-gray-500 text-xs max-w-xs mx-auto">When other users view your profile, send interests, or match back, they will appear here in real-time.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($notifications as $n): ?>
                    <div id="notif-card-<?= $n['id'] ?>" class="flex justify-between items-center p-4.5 rounded-2xl border transition duration-300 <?= $n['is_read'] ? 'bg-white/30 border-gray-200/50 opacity-60' : 'bg-white border-pink-100 shadow-sm hover:shadow-md' ?>">
                        <div class="flex items-start gap-4">
                            <span class="text-2xl mt-1"><?= ($n['type'] === 'message') ? '💬' : (($n['type'] === 'profile_view') ? '👁️' : '❤️') ?></span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full <?= ($n['type'] === 'message') ? 'bg-blue-100 text-blue-700' : (($n['type'] === 'profile_view') ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700') ?>">
                                        <?= htmlspecialchars($n['type']) ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium"><?= date('M d, H:i', strtotime($n['created_at'])) ?></span>
                                </div>
                                <h4 class="font-bold text-gray-800 text-sm mt-1.5"><?= htmlspecialchars($n['title']) ?></h4>
                                <p class="text-gray-600 text-xs mt-1 leading-relaxed"><?= htmlspecialchars($n['message']) ?></p>
                            </div>
                        </div>

                        <?php if (!$n['is_read']): ?>
                            <button onclick="markRead(<?= $n['id'] ?>)" class="bg-pink-100 hover:bg-pink-200 text-pink-700 text-xs px-4 py-2 rounded-xl font-bold transition shadow-sm shrink-0">
                                Mark Read
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    async function markRead(id) {
        try {
            const res = await fetch(`/api/v1/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + '<?= $token ?>' }
            });
            const data = await res.json();

            if (data.success) {
                const card = document.getElementById(`notif-card-${id}`);
                card.className = "flex justify-between items-center p-4.5 rounded-2xl border transition duration-300 bg-white/30 border-gray-200/50 opacity-60";
                const btn = card.querySelector('button');
                if (btn) btn.remove();
            }
        } catch (err) {
            console.error(err);
        }
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
