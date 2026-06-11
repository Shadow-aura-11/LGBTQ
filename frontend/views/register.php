<?php include __DIR__ . '/header.php'; ?>

<div class="max-w-3xl mx-auto my-8">
    <div class="glass-panel p-8 md:p-12 rounded-3xl shadow-xl border border-white/60">
        <!-- Steps Indicator Header -->
        <div class="flex justify-between items-center mb-10 border-b border-gray-100 pb-6">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 serif-font">Join PrideUnion</h2>
                <p class="text-gray-500 text-xs mt-1">Complete the onboarding to calculate your compatibility rating.</p>
            </div>
            <span class="text-pink-500 font-bold text-sm" id="step-indicator">Step 1 of 4</span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-150 h-1.5 rounded-full overflow-hidden mb-8 shadow-inner">
            <div id="progress-bar-fill" class="bg-gradient-to-r from-pink-400 to-indigo-600 h-full rounded-full transition-all duration-300" style="width: 25%"></div>
        </div>

        <div id="error-box" class="hidden bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6"></div>

        <form id="wizard-form" class="space-y-6">
            <!-- STEP 1: Basic Info -->
            <div id="step-1" class="space-y-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">1</span>
                    Account Credentials
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/40 p-6 rounded-2xl border border-gray-200/40">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full Name</label>
                        <input type="text" id="name" required placeholder="Alex Mercer" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" id="email" required placeholder="alex@domain.com" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                        <input type="password" id="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                        <input type="date" id="date_of_birth" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                </div>
            </div>

            <!-- STEP 2: Profile Details -->
            <div id="step-2" class="hidden space-y-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">2</span>
                    Identity &amp; Bio
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/40 p-6 rounded-2xl border border-gray-200/40">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Gender Identity</label>
                        <select id="gender_identity" required onchange="toggleCustomGender(this.value)" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                            <option value="">Select gender...</option>
                            <option value="man">Man</option>
                            <option value="woman">Woman</option>
                            <option value="non-binary">Non-Binary</option>
                            <option value="transgender man">Transgender Man</option>
                            <option value="transgender woman">Transgender Woman</option>
                            <option value="genderqueer">Genderqueer</option>
                            <option value="other">Other / Custom</option>
                        </select>
                    </div>
                    <div id="custom-gender-container" class="hidden">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Custom Gender</label>
                        <input type="text" id="gender_custom" placeholder="e.g. Agender" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Pronouns</label>
                        <input type="text" id="pronouns" placeholder="e.g. they/them" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Sexual Orientation</label>
                        <select id="sexual_orientation" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                            <option value="">Select orientation...</option>
                            <option value="gay">Gay</option>
                            <option value="lesbian">Lesbian</option>
                            <option value="bisexual">Bisexual</option>
                            <option value="pansexual">Pansexual</option>
                            <option value="queer">Queer</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Headline Pitch</label>
                        <input type="text" id="headline" placeholder="Express yourself in a few words..." class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>
                    <div class="col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">City</label>
                            <input type="text" id="city" placeholder="e.g. London" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Country</label>
                            <input type="text" id="country" placeholder="e.g. UK" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Partner Preferences (Looking For) -->
            <div id="step-3" class="hidden space-y-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">3</span>
                    Dating Preferences
                </h3>
                <div class="grid grid-cols-1 gap-4 bg-white/40 p-6 rounded-2xl border border-gray-200/40">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Relationship Goal</label>
                        <select id="relationship_intent" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                            <option value="dating">Dating &amp; Romance</option>
                            <option value="long-term">Long-Term Connection</option>
                            <option value="marriage">Matrimonial Bond</option>
                            <option value="friendship">Friendship First</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Preferred Matching Partner Genders</label>
                        <select id="pref_gender" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                            <option value="">Any Gender</option>
                            <option value="man">Men</option>
                            <option value="woman">Women</option>
                            <option value="non-binary">Non-Binary Singles</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Identity Verification -->
            <div id="step-4" class="hidden space-y-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs flex items-center justify-center font-bold">4</span>
                    Identity Verification
                </h3>
                
                <div class="bg-white/40 p-6 rounded-2xl border border-gray-200/40 space-y-4">
                    <p class="text-sm text-gray-600 leading-relaxed">
                        To maintain a safe matrimonial space, we request mock mobile number SMS verification. Click below to retrieve a secure token.
                    </p>

                    <div class="flex gap-2">
                        <input type="text" id="phone" placeholder="e.g. +1 (555) 019-9832" class="flex-grow px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                        <button type="button" onclick="sendMockOtp()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-3 rounded-xl transition shadow">Send Code</button>
                    </div>

                    <div class="hidden" id="otp-input-container">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Enter Code (Seeded: 123456)</label>
                        <input type="text" id="otp_code" placeholder="123456" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 outline-none transition bg-white/60 text-sm">
                    </div>

                    <div class="flex items-start gap-3.5 pt-4 border-t border-gray-100">
                        <input type="checkbox" id="terms" required class="mt-1 w-4 h-4 rounded text-pink-500 border-gray-300 focus:ring-pink-400">
                        <label for="terms" class="text-xs text-gray-500 leading-normal">
                            I verify that all entered pronouns and sexual orientations reflect my profile data, and I agree to community rules.
                        </label>
                    </div>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-100">
                <button type="button" id="back-btn" class="hidden px-5 py-2.5 rounded-xl text-gray-500 font-semibold hover:bg-gray-100 transition text-sm">
                    Back
                </button>
                <button type="button" id="next-btn" class="ml-auto btn-primary px-8 py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition text-sm">
                    Continue
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 4;

    function updateProgressBar() {
        const fill = document.getElementById('progress-bar-fill');
        const percentage = (currentStep / totalSteps) * 100;
        fill.style.width = percentage + '%';

        document.getElementById('step-indicator').innerText = `Step ${currentStep} of ${totalSteps}`;

        const backBtn = document.getElementById('back-btn');
        if (currentStep === 1) {
            backBtn.classList.add('hidden');
        } else {
            backBtn.classList.remove('hidden');
        }

        const nextBtn = document.getElementById('next-btn');
        if (currentStep === totalSteps) {
            nextBtn.innerText = "Register Account";
        } else {
            nextBtn.innerText = "Continue";
        }
    }

    function toggleCustomGender(value) {
        const container = document.getElementById('custom-gender-container');
        if (value === 'other') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            document.getElementById('gender_custom').value = '';
        }
    }

    function sendMockOtp() {
        const phone = document.getElementById('phone').value;
        if (!phone) {
            alert('Please enter a phone number.');
            return;
        }
        document.getElementById('otp-input-container').classList.remove('hidden');
        alert('Mock Verification OTP token sent! Enter code: 123456');
    }

    // Step verification checks
    function validateStep() {
        const errBox = document.getElementById('error-box');
        errBox.classList.add('hidden');

        if (currentStep === 1) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const dob = document.getElementById('date_of_birth').value;

            if (!name || !email || !password || !dob) {
                errBox.innerText = "All Step 1 credentials must be filled.";
                errBox.classList.remove('hidden');
                return false;
            }

            // Age check (18+)
            const birth = new Date(dob);
            const age = new Date().getFullYear() - birth.getFullYear();
            if (age < 18) {
                errBox.innerText = "You must be 18 years or older to register.";
                errBox.classList.remove('hidden');
                return false;
            }
        } else if (currentStep === 2) {
            const gender = document.getElementById('gender_identity').value;
            const orientation = document.getElementById('sexual_orientation').value;
            if (!gender || !orientation) {
                errBox.innerText = "Please select your gender identity and sexual orientation.";
                errBox.classList.remove('hidden');
                return false;
            }
        } else if (currentStep === 4) {
            const verified = document.getElementById('otp_code').value;
            const terms = document.getElementById('terms').checked;
            if (verified !== '123456') {
                errBox.innerText = "Invalid verification code. Enter '123456'.";
                errBox.classList.remove('hidden');
                return false;
            }
            if (!terms) {
                errBox.innerText = "Please accept the community rules.";
                errBox.classList.remove('hidden');
                return false;
            }
        }
        return true;
    }

    document.getElementById('next-btn').addEventListener('click', async () => {
        if (!validateStep()) return;

        if (currentStep < totalSteps) {
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            currentStep++;
            document.getElementById(`step-${currentStep}`).classList.remove('hidden');
            updateProgressBar();
        } else {
            // Register execution
            const payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                date_of_birth: document.getElementById('date_of_birth').value,
                gender_identity: document.getElementById('gender_identity').value,
                gender_custom: document.getElementById('gender_custom').value,
                sexual_orientation: document.getElementById('sexual_orientation').value,
                city: document.getElementById('city').value,
                country: document.getElementById('country').value
            };

            try {
                const res = await fetch('/api/v1/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    document.cookie = `jwt_token=${data.tokens.access_token}; path=/; max-age=${data.tokens.expires_in}; SameSite=Lax`;
                    window.location.href = '/dashboard';
                } else {
                    const errBox = document.getElementById('error-box');
                    errBox.innerText = data.error || 'Signup failed.';
                    errBox.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    document.getElementById('back-btn').addEventListener('click', () => {
        if (currentStep > 1) {
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            currentStep--;
            document.getElementById(`step-${currentStep}`).classList.remove('hidden');
            updateProgressBar();
        }
    });
</script>

<?php include __DIR__ . '/footer.php'; ?>
