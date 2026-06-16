<?php
include __DIR__ . '/header.php';

// Access Control
if (!$currentUser) {
    header('Location: /login');
    exit;
}

$targetId = (int)($viewTargetId ?? 0);
?>

<div id="status-message" class="hidden max-w-4xl mx-auto mb-6 px-4 py-3 rounded-2xl text-sm font-semibold shadow-sm"></div>

<div class="max-w-4xl mx-auto my-8 space-y-8 px-4">
    <?php if (!$profile): ?>
        <div class="glass-panel p-12 rounded-3xl text-center border border-white/60 shadow-md bg-white">
            <span class="text-5xl">🛡️</span>
            <h3 class="text-2xl font-black text-gray-900 mt-4 serif-font">Profile Suspended or Not Found</h3>
            <p class="text-gray-500 text-sm mt-1 mb-6">This profile is currently suspended by our moderation queue or does not exist.</p>
            <a href="/discovery" class="text-pink-600 hover:underline font-bold text-sm">&larr; Back to matches</a>
        </div>
    <?php else: ?>
        <!-- Profile Header Block -->
        <div class="glass-panel rounded-3xl overflow-hidden border border-white/60 shadow-lg bg-white relative">
            <!-- Lavender/Pink/Indigo Top Gradient Background -->
            <div class="h-44 bg-gradient-to-r from-[#fae8ff] via-[#fdf2f8] to-[#e0e7ff] relative"></div>
            
            <div class="px-8 pb-8 relative flex flex-col md:flex-row md:items-end justify-between gap-6">
                <!-- Circular Avatar overlaying the gradient border -->
                <div class="flex flex-col md:flex-row items-center md:items-end gap-6 -mt-16 relative">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-md bg-gray-200 shrink-0">
                        <img src="<?= htmlspecialchars($displayPhoto) ?>" class="w-full h-full object-cover">
                    </div>
                    
                    <div class="text-center md:text-left space-y-2">
                        <!-- Name, Age and Online status dot -->
                        <h3 class="text-3xl font-black text-gray-900 flex items-center justify-center md:justify-start gap-2 serif-font">
                            <?= htmlspecialchars($profile['name']) ?>, <?= date_diff(date_create($profile['date_of_birth']), date_create('today'))->y ?>
                            <span class="w-3 h-3 bg-[#10b981] rounded-full inline-block" title="Online Now"></span>
                        </h3>
                        
                        <!-- Location -->
                        <p class="text-sm text-gray-500 font-medium">
                            📍 <?= htmlspecialchars($profile['city']) ?>, <?= htmlspecialchars($profile['country']) ?>
                        </p>
                        
                        <!-- Tags -->
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mt-2">
                            <?php 
                            $orColor = 'bg-pink-600';
                            $orLower = strtolower($profile['sexual_orientation'] ?? 'queer');
                            if ($orLower === 'gay') $orColor = 'bg-[#1d4ed8]';
                            else if ($orLower === 'lesbian') $orColor = 'bg-[#db2777]';
                            else if ($orLower === 'pansexual') $orColor = 'bg-[#d97706]';
                            else if ($orLower === 'bisexual') $orColor = 'bg-[#7c3aed]';
                            else if ($orLower === 'queer') $orColor = 'bg-[#4f46e5]';
                            ?>
                            <span class="<?= $orColor ?> text-white text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                                <?= htmlspecialchars($profile['sexual_orientation']) ?>
                            </span>
                            <span class="bg-[#fdf2f8] text-[#86198f] text-[10px] font-extrabold px-3 py-1 rounded-full">
                                <?= htmlspecialchars($profile['gender_identity'] === 'other' ? ($profile['gender_custom'] ?: 'Other') : $profile['gender_identity']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Interaction Actions -->
                <div class="flex gap-3 shrink-0 justify-center">
                    <button onclick="sendInterest(<?= $targetId ?>)" class="bg-[#ec4899] hover:bg-[#db2777] text-white px-5 py-2.5 rounded-xl font-bold transition shadow-sm text-xs flex items-center gap-1.5">
                        ❤️ Express Interest
                    </button>
                    <a href="/chat?recipient_id=<?= $targetId ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-sm text-xs flex items-center gap-1.5">
                        💬 Start Live Chat
                    </a>
                </div>
            </div>
        </div>

        <!-- About Me Section -->
        <div class="glass-panel p-8 rounded-3xl border border-white/60 shadow-md bg-white">
            <h4 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-4">About Me</h4>
            <p class="text-gray-700 text-sm leading-relaxed italic">
                "<?= htmlspecialchars($profile['about_me'] ?? 'No bio provided yet.') ?>"
            </p>
        </div>

        <!-- Personal Details Section -->
        <div class="glass-panel p-8 rounded-3xl border border-white/60 shadow-md bg-white">
            <h4 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-6">Personal Details</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <!-- Height -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Height</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['height'] ?: '165') ?> cm
                    </span>
                </div>
                <!-- Occupation -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Occupation</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['profession'] ?: ($profile['occupation'] ?: 'Film Director')) ?>
                    </span>
                </div>
                <!-- Education -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Education</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['education'] ?: 'Masters in Media Studies') ?>
                    </span>
                </div>
                <!-- Religion -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Religion</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['religion'] ?: 'Hindu') ?>
                    </span>
                </div>
                <!-- Mother Tongue -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Mother Tongue</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['mother_tongue'] ?: 'English') ?>
                    </span>
                </div>
                <!-- Hometown -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Hometown</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['hometown'] ?: 'San Francisco, CA') ?>
                    </span>
                </div>
                <!-- Hobbies -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Hobbies &amp; Interests</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['hobbies'] ?: 'Reading, Traveling') ?>
                    </span>
                </div>
                <!-- Lifestyle Habits -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl col-span-1 md:col-span-2 lg:col-span-3">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Lifestyle Habits</span>
                    <span class="text-gray-800 font-bold text-sm mt-1 block">
                        <?= htmlspecialchars($profile['lifestyle_habits'] ?: 'Dietary preference, Drinking, Smoking habits') ?>
                    </span>
                </div>
                <!-- Family Details -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl col-span-1 md:col-span-2 lg:col-span-3">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Family Background &amp; Core Values</span>
                    <p class="text-gray-700 text-sm mt-1.5 leading-relaxed">
                        <?= nl2br(htmlspecialchars($profile['family_details'] ?: 'Values-driven background details...')) ?>
                    </p>
                </div>
                <!-- Partner Preferences -->
                <div class="bg-[#fff8fa] border border-[#fce7f3]/30 p-5 rounded-2xl col-span-1 md:col-span-2 lg:col-span-3">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Preferred Partner Qualities</span>
                    <p class="text-gray-700 text-sm mt-1.5 leading-relaxed">
                        <?= nl2br(htmlspecialchars($profile['partner_pref'] ?: 'Looking for mutual compatibility, understanding, and similar interests.')) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="glass-panel p-8 rounded-3xl border border-white/60 shadow-md bg-white">
            <h4 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-6">Contact Information</h4>
            
            <?php if ($profile['contact_hidden'] ?? true): ?>
                <!-- Gated Lock Box for Free Tier Users -->
                <div class="border border-[#fce7f3] bg-[#fff8fa] rounded-2xl p-8 text-center flex flex-col items-center justify-center space-y-4">
                    <!-- Padlock Icon -->
                    <div class="w-12 h-12 rounded-full border border-[#fce7f3] flex items-center justify-center bg-white shadow-sm shrink-0">
                        <span class="text-[#db2777] text-lg font-bold">🔒</span>
                    </div>
                    
                    <h5 class="text-gray-800 font-bold text-base mt-2">Contact details are locked for free tier users</h5>
                    <p class="text-gray-500 text-xs max-w-md">
                        Upgrade to a premium plan to see mobile numbers, social links, and start direct messages.
                    </p>
                    
                    <a href="/subscription" class="bg-[#ec4899] hover:bg-[#db2777] text-white px-6 py-2.5 rounded-xl font-bold text-xs transition shadow-md">
                        Unlock with Premium — ₹499/mo
                    </a>
                </div>
            <?php else: ?>
                <!-- Unlocked Contact Info for Premium Users -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Email Address</span>
                        <a href="mailto:<?= htmlspecialchars($profile['email']) ?>" class="text-pink-600 font-bold hover:underline text-sm block">
                            <?= htmlspecialchars($profile['email']) ?>
                        </a>
                    </div>
                    <div class="space-y-1">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Phone/UPI Identifier</span>
                        <span class="text-gray-800 font-bold text-sm block">
                            <?= htmlspecialchars($profile['phone']) ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Security Widgets -->
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-sm space-y-3 bg-white/70">
            <h4 class="font-extrabold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-1">
                <span>🛡️</span> Security Control
            </h4>
            <p class="text-[11px] text-gray-500 leading-normal">Feel unsafe? You can block this single profile or submit a report to the admin review queue.</p>
            <div class="flex gap-2 max-w-sm">
                <button onclick="toggleReportModal()" class="flex-1 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 text-xs py-2 rounded-xl font-bold transition">
                    Report User
                </button>
                <button onclick="blockUser(<?= $targetId ?>)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-2 rounded-xl font-bold transition">
                    Block User
                </button>
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
