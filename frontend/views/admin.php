<?php
include __DIR__ . '/header.php';

// Access Control - Admin role only
if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    echo "<div class='glass-panel p-12 rounded-3xl text-center border border-white/60 my-12 max-w-xl mx-auto shadow-xl'>";
    echo "<span class='text-4xl'>⚠️</span>";
    echo "<h3 class='text-2xl font-black text-gray-900 mt-4'>Access Denied</h3>";
    echo "<p class='text-gray-600 text-sm mt-1'>Administrative privileges are required to view this panel.</p>";
    echo "<a href='/discovery' class='text-pink-600 hover:underline mt-4 inline-block'>&larr; Back to matches</a>";
    echo "</div>";
    include __DIR__ . '/footer.php';
    exit;
}

// Fetch Reports from moderation-service API
$reportResponse = makeApiRequest('GET', '/api/v1/moderation/admin/reports', [], $token);
$reports = [];
if ($reportResponse['status'] === 200 && isset($reportResponse['data']['reports'])) {
    $reports = $reportResponse['data']['reports'];
}

$pendingCount = 0;
$reviewedCount = 0;
foreach ($reports as $r) {
    if ($r['status'] === 'pending') $pendingCount++;
    else $reviewedCount++;
}
?>

<div class="max-w-6xl mx-auto my-8">
    <!-- Admin Statistics Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4">
            <span class="text-3xl bg-orange-100 p-3 rounded-2xl">⏳</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Reports</span>
                <h4 class="text-3xl font-extrabold text-gray-800"><?= $pendingCount ?></h4>
            </div>
        </div>
        
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4">
            <span class="text-3xl bg-green-100 p-3 rounded-2xl">✅</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Reviewed Actions</span>
                <h4 class="text-3xl font-extrabold text-gray-800"><?= $reviewedCount ?></h4>
            </div>
        </div>

        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm flex items-center gap-4">
            <span class="text-3xl bg-pink-100 p-3 rounded-2xl">🛡️</span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Queue Health</span>
                <h4 class="text-lg font-extrabold text-green-600">Secure &amp; Safe</h4>
            </div>
        </div>
    </div>

    <!-- Moderation reports list -->
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60">
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
</div>

<script>
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
</script>

<?php include __DIR__ . '/footer.php'; ?>
