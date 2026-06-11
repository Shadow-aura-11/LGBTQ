<?php
include __DIR__ . '/header.php';

// Access Control
if (!$currentUser) {
    header('Location: /login');
    exit;
}

$targetId = (int)($viewTargetId ?? 0);
?>

<div id="status-message" class="hidden max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm font-semibold shadow-sm"></div>

<?php if (!$profile): ?>
    <div class="glass-panel p-12 rounded-3xl text-center border border-white/60 my-12 max-w-xl mx-auto shadow-md">
        <span class="text-5xl">🛡️</span>
        <h3 class="text-2xl font-black text-gray-900 mt-4 serif-font">Profile Suspended or Not Found</h3>
        <p class="text-gray-500 text-sm mt-1 mb-6">This profile is currently suspended by our moderation queue or does not exist.</p>
        <a href="/discovery" class="text-pink-600 hover:underline font-bold text-sm">&larr; Back to matches</a>
    </div>
<?php else: ?>
    <div class="max-w-5xl mx-auto my-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Sidebar: Profile Summary Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-panel p-6 rounded-3xl border border-white/60 text-center shadow-md">
                <!-- Profile Photo -->
                <div class="relative overflow-hidden rounded-2xl aspect-[3/4] bg-gray-200 shadow mb-5 group">
                    <img src="<?= htmlspecialchars($displayPhoto) ?>" class="w-full h-full object-cover">
                </div>

                <!-- Profile Overview Header -->
                <h3 class="text-2xl font-extrabold text-gray-900 serif-font">
                    <?= htmlspecialchars($profile['name']) ?>
                </h3>
                <p class="text-xs text-pink-600 font-extrabold uppercase mt-1.5 tracking-wider">
                    <?= htmlspecialchars($profile['gender_identity']) ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Pronouns: <?= htmlspecialchars($profile['pronouns']) ?>
                </p>
                <p class="text-xs text-gray-400 mt-1 font-medium">
                    📍 <?= htmlspecialchars($profile['city']) ?>, <?= htmlspecialchars($profile['country']) ?>
                </p>

                <!-- Interactive Matching Controls -->
                <div class="mt-6 space-y-3">
                    <button onclick="sendInterest(<?= $targetId ?>)" class="w-full btn-primary py-3 rounded-xl font-bold shadow hover:shadow-lg transition text-xs">
                        ❤️ Express Interest
                    </button>
                    <a href="/chat?recipient_id=<?= $targetId ?>" class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition shadow text-xs">
                        💬 Start Live Chat
                    </a>
                </div>
            </div>

            <!-- Safety widget -->
            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm space-y-3">
                <h4 class="font-extrabold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-1">
                    <span>🛡️</span> Security Control
                </h4>
                <p class="text-[11px] text-gray-500 leading-normal">Feel unsafe? You can block this single profile or submit a report to the admin review queue.</p>
                <div class="flex gap-2">
                    <button onclick="toggleReportModal()" class="flex-1 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 text-xs py-2 rounded-xl font-bold transition">
                        Report User
                    </button>
                    <button onclick="blockUser(<?= $targetId ?>)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-2 rounded-xl font-bold transition">
                        Block User
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Side: Attribute grids -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Headline -->
            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Headline Pitch</h4>
                <p class="text-gray-800 text-lg font-bold italic serif-font">
                    "<?= htmlspecialchars($profile['headline']) ?>"
                </p>
            </div>

            <!-- Detail Attributes -->
            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm relative overflow-hidden">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">About Me</h4>
                
                <div>
                    <p class="text-gray-700 text-sm leading-relaxed mb-6 bg-white/30 p-4 rounded-xl border border-gray-150">
                        <?= nl2br(htmlspecialchars($profile['about_me'])) ?>
                    </p>

                    <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 text-xs">
                        <div>
                            <span class="text-gray-400 uppercase font-semibold">Height</span>
                            <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['height']) ?> cm</p>
                        </div>
                        <div>
                            <span class="text-gray-400 uppercase font-semibold">Religion</span>
                            <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['religion']) ?></p>
                        </div>
                        <div>
                            <span class="text-gray-400 uppercase font-semibold">Mother Tongue</span>
                            <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['mother_tongue']) ?></p>
                        </div>
                        <div>
                            <span class="text-gray-400 uppercase font-semibold">Education</span>
                            <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['education']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contacts Panel -->
            <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm relative overflow-hidden">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Verified Contacts</h4>
                
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-400">Email Address</span>
                            <p class="text-gray-800 font-bold text-sm mt-0.5"><?= htmlspecialchars($profile['email']) ?></p>
                        </div>
                        <div>
                            <span class="text-gray-400">Phone/UPI Identifier</span>
                            <p class="text-gray-800 font-bold text-sm mt-0.5"><?= htmlspecialchars($profile['phone']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal Overlay -->
    <div id="report-modal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass-panel bg-white p-6 rounded-3xl max-w-md w-full border border-white shadow-2xl">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Report Profile</h3>
            <textarea id="report-reason" rows="4" placeholder="Describe the reason for flagging this profile..."
                      class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-400 outline-none transition text-sm bg-white/50"></textarea>
            
            <div class="flex justify-end gap-3 mt-4">
                <button onclick="toggleReportModal()" class="px-4 py-2 rounded-xl text-gray-600 text-xs font-semibold hover:bg-gray-100 transition">Cancel</button>
                <button onclick="submitReport(<?= $targetId ?>)" class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-xl text-xs font-bold shadow transition">Submit Report</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    async function sendInterest(targetId) {
        const msg = document.getElementById('status-message');
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

            msg.classList.remove('hidden');
            if (data.success) {
                msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-green-100 border border-green-200 text-green-700 font-semibold shadow-sm";
                msg.innerText = data.mutual ? "🎉 Mutual Match! You can now chat directly in real-time!" : data.message;
            } else {
                msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-red-100 border border-red-200 text-red-700 font-semibold";
                msg.innerText = data.error || "Failed to send interest.";
            }
        } catch (err) {
            msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-red-100 border border-red-200 text-red-700 font-semibold";
            msg.innerText = "Error sending request.";
            msg.classList.remove('hidden');
        }
    }

    async function blockUser(targetId) {
        const msg = document.getElementById('status-message');
        if (!confirm('Are you sure you want to block this user? You will no longer see each other.')) return;

        try {
            const res = await fetch('/api/v1/moderation/block', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify({ blocked_id: targetId })
            });
            const data = await res.json();

            msg.classList.remove('hidden');
            if (data.success) {
                msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-gray-100 border border-gray-200 text-gray-700 font-semibold shadow-sm";
                msg.innerText = data.message;
                setTimeout(() => window.location.href = '/dashboard', 1000);
            } else {
                msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-red-100 border border-red-200 text-red-700 font-semibold";
                msg.innerText = data.error;
            }
        } catch (err) {
            msg.innerText = "Network error.";
        }
    }

    function toggleReportModal() {
        const modal = document.getElementById('report-modal');
        modal.classList.toggle('hidden');
        document.getElementById('report-reason').value = '';
    }

    async function submitReport(targetId) {
        const reason = document.getElementById('report-reason').value;
        const msg = document.getElementById('status-message');

        if (!reason.trim()) {
            alert('Please specify a reason.');
            return;
        }

        try {
            const res = await fetch('/api/v1/moderation/report', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify({ reported_id: targetId, reason })
            });
            const data = await res.json();

            toggleReportModal();
            msg.classList.remove('hidden');
            if (data.success) {
                msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-orange-100 border border-orange-200 text-orange-700 font-semibold shadow-sm";
                msg.innerText = data.message;
            } else {
                msg.className = "max-w-5xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm bg-red-100 border border-red-200 text-red-700 font-semibold";
                msg.innerText = data.error;
            }
        } catch (err) {
            msg.innerText = "Network error.";
        }
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
