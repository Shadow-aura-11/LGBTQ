<?php
include __DIR__ . '/header.php';

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

    <!-- User Management Section -->
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 mt-8 bg-white">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 serif-font">👥 User Accounts Management</h2>
                <p class="text-gray-500 text-xs mt-0.5">Toggle user account status (active/suspended), toggle subscription tiers, or delete accounts.</p>
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
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $u['tier'] === 'premium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= htmlspecialchars($u['tier']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4">
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
</script>

<?php include __DIR__ . '/footer.php'; ?>
