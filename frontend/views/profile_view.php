<?php
include __DIR__ . '/header.php';

// Access Control
if (!$currentUser) {
    header('Location: /login');
    exit;
}

$targetId = (int)($viewTargetId ?? 0);
if (!$targetId) {
    echo "<p class='text-red-500'>Invalid Profile ID</p>";
    include __DIR__ . '/footer.php';
    exit;
}

// Fetch Profile from profile-service API
$profileResponse = makeApiRequest('GET', "/api/v1/profiles/{$targetId}", [], $token);
$profile = null;
if ($profileResponse['status'] === 200 && isset($profileResponse['data']['profile'])) {
    $profile = $profileResponse['data']['profile'];
} else {
    echo "<div class='glass-panel p-12 rounded-3xl text-center border border-white/60 my-12 max-w-xl mx-auto shadow-md'>";
    echo "<span class='text-5xl'>🛡️</span>";
    echo "<h3 class='text-2xl font-black text-gray-900 mt-4 serif-font'>Profile Suspended</h3>";
    echo "<p class='text-gray-500 text-sm mt-1 mb-6'>This profile is currently suspended by our moderation queue for violating community safety standards.</p>";
    echo "<a href='/discovery' class='text-pink-600 hover:underline font-bold text-sm'>&larr; Back to matches</a>";
    echo "</div>";
    include __DIR__ . '/footer.php';
    exit;
}

$photos = json_decode($profile['photos'] ?? '[]', true) ?: [];
$displayPhoto = !empty($photos) ? $photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80';
$isPremium = ($currentUser['tier'] ?? 'free') === 'premium';
?>

<div id="status-message" class="hidden max-w-4xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm font-semibold shadow-sm"></div>

<div class="max-w-5xl mx-auto my-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Sidebar: Profile Summary Card -->
    <div class="lg:col-span-1 space-y-6">
        <div class="glass-panel p-6 rounded-3xl border border-white/60 text-center shadow-md">
            <!-- Profile Photo -->
            <div class="relative overflow-hidden rounded-2xl aspect-[3/4] bg-gray-200 shadow mb-5 group">
                <img src="<?= htmlspecialchars($displayPhoto) ?>" class="w-full h-full object-cover">
                
                <?php if ($profile['contact_hidden'] ?? true): ?>
                    <!-- Gated Overlay with Lock Icon (Eharmony style) -->
                    <div class="absolute inset-0 bg-white/20 backdrop-blur flex flex-col items-center justify-center p-4">
                        <div class="bg-black/80 backdrop-blur-sm text-white text-xs px-4 py-2 rounded-full font-bold shadow-md flex items-center gap-1.5 border border-white/10">
                            <span>🔒</span> Unblur Photos
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Overview Header -->
            <h3 class="text-2xl font-extrabold text-gray-900 serif-font">
                <?= htmlspecialchars($profile['name']) ?>
            </h3>
            <p class="text-xs text-pink-600 font-extrabold uppercase mt-1.5 tracking-wider">
                <?= htmlspecialchars($profile['gender_identity'] === 'other' ? ($profile['gender_custom'] ?: 'Other') : $profile['gender_identity']) ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Pronouns: <?= htmlspecialchars($profile['pronouns'] ?: 'they/them') ?>
            </p>
            <p class="text-xs text-gray-400 mt-1 font-medium">
                📍 <?= htmlspecialchars($profile['city'] ?: 'Locked') ?>, <?= htmlspecialchars($profile['country'] ?: 'Locked') ?>
            </p>

            <!-- Interactive Matching Controls -->
            <div class="mt-6 space-y-3">
                <?php if ($isPremium): ?>
                    <button onclick="sendInterest(<?= $targetId ?>)" class="w-full btn-primary py-3 rounded-xl font-bold shadow hover:shadow-lg transition text-xs">
                        ❤️ Express Interest
                    </button>
                    <a href="/chat?recipient_id=<?= $targetId ?>" class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition shadow text-xs">
                        💬 Start Live Chat
                    </a>
                <?php else: ?>
                    <a href="/subscription" class="w-full block text-center bg-gradient-to-r from-pink-500 to-indigo-600 text-white py-3.5 rounded-xl font-bold transition shadow-lg animate-pulse text-xs">
                        👑 Upgrade to Express Interest
                    </a>
                <?php endif; ?>
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
                "<?= htmlspecialchars($profile['headline'] ?: 'Exploring matrimonial matches') ?>"
            </p>
        </div>

        <!-- Detail Attributes (Conditional Blur) -->
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm relative overflow-hidden">
            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">About Me</h4>
            
            <div class="<?= ($profile['contact_hidden'] ?? true) ? 'filter blur-[5px] select-none pointer-events-none' : '' ?>">
                <p class="text-gray-700 text-sm leading-relaxed mb-6 bg-white/30 p-4 rounded-xl border border-gray-150">
                    <?= nl2br(htmlspecialchars($profile['about_me'] ?: 'No biography details provided.')) ?>
                </p>

                <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 text-xs">
                    <div>
                        <span class="text-gray-400 uppercase font-semibold">Height</span>
                        <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['height'] ?? 'N/A') ?> cm</p>
                    </div>
                    <div>
                        <span class="text-gray-400 uppercase font-semibold">Religion</span>
                        <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['religion'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <span class="text-gray-400 uppercase font-semibold">Mother Tongue</span>
                        <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['mother_tongue'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <span class="text-gray-400 uppercase font-semibold">Education</span>
                        <p class="text-gray-800 font-bold mt-0.5"><?= htmlspecialchars($profile['education'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <?php if ($profile['contact_hidden'] ?? true): ?>
                <!-- Lock screen panel (eHarmony / PrideUnion style) -->
                <div class="absolute inset-0 bg-white/30 flex flex-col items-center justify-center p-6 text-center">
                    <span class="text-4xl">🔒</span>
                    <h5 class="font-extrabold text-gray-800 text-base mt-2">Dating Profile Gated</h5>
                    <p class="text-gray-500 text-xs mt-1 max-w-xs leading-relaxed">Detailed lifestyle attributes, education, and bios are reserved for premium members.</p>
                    <a href="/subscription" class="btn-primary text-xs px-5 py-2.5 rounded-full font-bold shadow mt-4">Upgrade Now</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contacts Panel (Conditional Blur) -->
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm relative overflow-hidden">
            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Verified Contacts</h4>
            
            <div class="<?= ($profile['contact_hidden'] ?? true) ? 'filter blur-[6px] select-none pointer-events-none' : '' ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400">Email Address</span>
                        <p class="text-gray-800 font-bold text-sm mt-0.5"><?= htmlspecialchars($profile['email'] ?? '') ?></p>
                    </div>
                    <div>
                        <span class="text-gray-400">Phone/UPI Identifier</span>
                        <p class="text-gray-800 font-bold text-sm mt-0.5"><?= htmlspecialchars($profile['phone'] ?? '') ?></p>
                    </div>
                </div>
            </div>

            <?php if ($profile['contact_hidden'] ?? true): ?>
                <!-- Lock Screen -->
                <div class="absolute inset-0 bg-white/30 flex flex-col items-center justify-center p-6 text-center">
                    <span class="text-3xl">🔑</span>
                    <h5 class="font-extrabold text-gray-800 text-sm mt-2">Contact Gated</h5>
                    <p class="text-gray-500 text-xs max-w-xs leading-normal">Unlock verified contact handles directly by upgrading your subscription plan.</p>
                </div>
            <?php endif; ?>
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
