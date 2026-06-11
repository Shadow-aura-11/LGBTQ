<?php
include __DIR__ . '/header.php';

// Access Control
if (!$currentUser) {
    header('Location: /login');
    exit;
}
?>

<div class="max-w-4xl mx-auto my-8">
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60">
        <!-- Dashboard Header & Progress indicator -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-gray-200/50 pb-6">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 serif-font">👤 Profile Setup</h2>
                <p class="text-gray-600 text-sm">Refine your preferences to match with compatible queer singles.</p>
            </div>
            
            <div class="w-full md:w-64 space-y-1.5 shrink-0">
                <div class="flex justify-between text-xs font-bold text-gray-600">
                    <span>Completeness</span>
                    <span class="text-pink-600"><?= $score ?>%</span>
                </div>
                <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-pink-400 to-indigo-600 h-full rounded-full transition-all duration-500" style="width: <?= $score ?>%"></div>
                </div>
            </div>
        </div>

        <div id="status-box" class="hidden px-4 py-2.5 rounded-2xl text-sm mb-6 shadow-sm"></div>

        <form id="profile-setup-form" class="space-y-8">
            <!-- Section 1: Bio Pitch -->
            <div class="space-y-4">
                <h3 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">1</span>
                    Personal Pitch
                </h3>
                
                <div class="grid grid-cols-1 gap-4 bg-white/40 p-5 rounded-2xl border border-gray-200/40">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Catchy Headline</label>
                        <input type="text" id="headline" value="<?= htmlspecialchars($profile['headline'] ?? '') ?>" placeholder="e.g. Free-spirited artist seeking a lifelong partner."
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">About Me</label>
                        <textarea id="about_me" rows="4" placeholder="Share your passions, hobbies, values, and relationship intent..."
                                  class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm"><?= htmlspecialchars($profile['about_me'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Personal Details -->
            <div class="space-y-4">
                <h3 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">2</span>
                    Attributes &amp; Lifestyle
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/40 p-5 rounded-2xl border border-gray-200/40">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Pronouns</label>
                        <input type="text" id="pronouns" value="<?= htmlspecialchars($profile['pronouns'] ?? '') ?>" placeholder="e.g. they/them, she/her"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Height (cm)</label>
                        <input type="number" id="height" value="<?= htmlspecialchars($profile['height'] ?? '') ?>" placeholder="e.g. 170"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Religion / Affiliation</label>
                        <input type="text" id="religion" value="<?= htmlspecialchars($profile['religion'] ?? '') ?>" placeholder="e.g. Secular"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Mother Tongue</label>
                        <input type="text" id="mother_tongue" value="<?= htmlspecialchars($profile['mother_tongue'] ?? '') ?>" placeholder="e.g. English"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Education Level</label>
                        <input type="text" id="education" value="<?= htmlspecialchars($profile['education'] ?? '') ?>" placeholder="e.g. Master's in Design"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Profession</label>
                        <input type="text" id="profession" value="<?= htmlspecialchars($profile['profession'] ?? '') ?>" placeholder="e.g. Web Developer"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Relationship Intent</label>
                        <select id="relationship_intent"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/60 text-sm">
                            <option value="">Select intent...</option>
                            <option value="friendship">Friendship</option>
                            <option value="dating">Dating</option>
                            <option value="long-term">Long-Term partnership</option>
                            <option value="marriage">Marriage</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Photo Manager -->
            <div class="space-y-4">
                <h3 class="font-extrabold text-gray-800 text-base flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">3</span>
                    Media Manager (Max 5 photos)
                </h3>

                <div class="bg-white/40 p-5 rounded-2xl border border-gray-200/40">
                    <div class="flex flex-wrap gap-4" id="photo-preview-container">
                        <!-- Loaded dynamically via js previews -->
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full btn-primary py-4 rounded-xl font-bold shadow-md hover:shadow-lg transition">
                    Save and Update Discovery Feed
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let activePhotos = <?= $profile_photos_json ?>;

    async function uploadPhoto(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        const formData = new FormData();
        formData.append('photo', file);

        const statusBox = document.getElementById('status-box');
        statusBox.className = "bg-blue-100 border border-blue-200 text-blue-700 px-4 py-2.5 rounded-xl text-sm mb-6";
        statusBox.innerText = "Compressing and uploading photo...";
        statusBox.classList.remove('hidden');

        try {
            const res = await fetch('/api/v1/media/upload', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                activePhotos.push(data.url);
                renderPreviews();
                statusBox.className = "bg-green-100 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = "Photo uploaded and optimized.";
            } else {
                statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.error || "Upload failed.";
            }
        } catch (err) {
            statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
            statusBox.innerText = "Upload failed.";
        }
    }

    function removePhoto(button) {
        const item = button.closest('.photo-item');
        const url = item.dataset.url;
        activePhotos = activePhotos.filter(p => p !== url);
        renderPreviews();
    }

    function renderPreviews() {
        const container = document.getElementById('photo-preview-container');
        container.innerHTML = '';

        activePhotos.forEach(url => {
            const div = document.createElement('div');
            div.className = 'relative w-24 h-24 rounded-2xl overflow-hidden shadow border border-gray-200 photo-item';
            div.dataset.url = url;
            div.innerHTML = `
                <img src="${url}" class="w-full h-full object-cover">
                <button type="button" onclick="removePhoto(this)" class="absolute top-1.5 right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 shadow">&times;</button>
            `;
            container.appendChild(div);
        });

        if (activePhotos.length < 5) {
            const label = document.createElement('label');
            label.id = 'upload-label';
            label.className = 'w-24 h-24 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer hover:border-pink-400 transition bg-white/50 hover:bg-white';
            label.innerHTML = `
                <span class="text-2xl text-gray-400 font-bold">+</span>
                <span class="text-[10px] text-gray-400 font-semibold mt-1">Upload</span>
                <input type="file" id="photo-uploader" accept="image/*" class="hidden" onchange="uploadPhoto(this)">
            `;
            container.appendChild(label);
        }
    }

    // Set dropdown selects on load
    document.getElementById('relationship_intent').value = "<?= $profile['relationship_intent'] ?? '' ?>";

    // Initial load call
    renderPreviews();

    document.getElementById('profile-setup-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const statusBox = document.getElementById('status-box');
        statusBox.classList.add('hidden');

        const payload = {
            headline: document.getElementById('headline').value,
            about_me: document.getElementById('about_me').value,
            pronouns: document.getElementById('pronouns').value,
            height: document.getElementById('height').value,
            religion: document.getElementById('religion').value,
            mother_tongue: document.getElementById('mother_tongue').value,
            education: document.getElementById('education').value,
            profession: document.getElementById('profession').value,
            relationship_intent: document.getElementById('relationship_intent').value,
            photos: activePhotos
        };

        try {
            const res = await fetch('/api/v1/profiles/me', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                statusBox.className = "bg-green-100 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = "Profile saved successfully!";
                statusBox.classList.remove('hidden');
                setTimeout(() => window.location.href = '/dashboard', 1000);
            } else {
                statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
                statusBox.innerText = data.error || "Save error.";
                statusBox.classList.remove('hidden');
            }
        } catch (err) {
            statusBox.className = "bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6";
            statusBox.innerText = "Connection error.";
            statusBox.classList.remove('hidden');
        }
    });
</script>

<?php include __DIR__ . '/footer.php'; ?>
