<?php include __DIR__ . '/header.php'; ?>

<!-- Hero Area -->
<div class="py-16 md:py-24 flex flex-col lg:flex-row items-center gap-12 max-w-7xl mx-auto relative px-6">
    <div class="absolute -top-10 -left-10 w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-25 animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-25 animate-pulse [animation-delay:2s]"></div>

    <!-- Pitch Text -->
    <div class="flex-grow flex-1 space-y-6 text-center lg:text-left z-10">
        <div class="inline-flex items-center gap-2 bg-pink-50 border border-pink-100 text-pink-700 px-4 py-2 rounded-full text-xs font-extrabold uppercase tracking-widest shadow-sm">
            <span>✨ The Safest Space for Queer Love</span>
        </div>
        
        <h1 class="text-4xl md:text-6xl font-black text-gray-900 leading-tight serif-font">
            Dating Designed For<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-indigo-600">Your True Self</span>
        </h1>
        
        <p class="text-gray-600 text-base md:text-lg max-w-xl mx-auto lg:mx-0 leading-relaxed">
            Proud Hearts is a premium matrimonial platform built to calculate real compatibility while fully respecting pronouns, orientation, and diverse gender identities.
        </p>

        <!-- Generated Hero Image Illustration -->
        <img src="/uploads/hero.png" class="w-full max-w-md h-56 object-cover rounded-3xl shadow-md border border-white/40 mb-4 mx-auto lg:mx-0">

        <!-- Social Proof Stats (eHarmony style) -->
        <div class="grid grid-cols-3 gap-4 py-4 max-w-md mx-auto lg:mx-0 border-t border-b border-gray-200/50">
            <div>
                <h4 class="text-2xl font-extrabold text-pink-600">Every 14m</h4>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">A Match is Born</p>
            </div>
            <div>
                <h4 class="text-2xl font-extrabold text-gray-800">100%</h4>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Verified Profiles</p>
            </div>
            <div>
                <h4 class="text-2xl font-extrabold text-gray-800">8+ Yrs</h4>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Trusted Safe Space</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 justify-center lg:justify-start pt-4">
            <?php if ($currentUser): ?>
                <a href="/dashboard" class="btn-primary px-8 py-4 rounded-full font-bold text-base shadow-lg hover:shadow-xl transition">
                    Go to Dashboard &rarr;
                </a>
            <?php else: ?>
                <a href="/register" class="btn-primary px-8 py-4 rounded-full font-bold text-base shadow-lg hover:shadow-xl transition">
                    Start Compatibility Onboarding
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Interactive Onboarding Quiz Widget (eHarmony style) -->
    <div class="w-full lg:w-[450px] z-10 shrink-0">
        <div class="glass-panel p-8 rounded-3xl shadow-2xl border border-white/85">
            <h3 class="font-extrabold text-gray-800 text-lg serif-font mb-4 flex items-center gap-2">
                <span>⚡</span> Quick Matching Quiz
            </h3>
            
            <div id="quiz-container" class="space-y-6">
                <!-- Question 1 -->
                <div id="quiz-q1" class="space-y-4">
                    <p class="text-sm font-bold text-gray-700">What are you looking for in a compatible relationship?</p>
                    <div class="grid grid-cols-1 gap-2.5">
                        <button onclick="nextQuizStep('marriage')" class="w-full text-left p-3.5 rounded-xl border border-gray-200 bg-white/60 hover:bg-pink-50 hover:border-pink-300 font-semibold text-xs transition shadow-sm">
                            💍 Marriage &amp; Matrimonial commitment
                        </button>
                        <button onclick="nextQuizStep('dating')" class="w-full text-left p-3.5 rounded-xl border border-gray-200 bg-white/60 hover:bg-pink-50 hover:border-pink-300 font-semibold text-xs transition shadow-sm">
                            💑 Long-term dating &amp; romance
                        </button>
                        <button onclick="nextQuizStep('friendship')" class="w-full text-left p-3.5 rounded-xl border border-gray-200 bg-white/60 hover:bg-pink-50 hover:border-pink-300 font-semibold text-xs transition shadow-sm">
                            🤝 Friendly networking &amp; community
                        </button>
                    </div>
                </div>

                <!-- Result Screen -->
                <div id="quiz-result" class="hidden text-center py-6 space-y-4">
                    <span class="text-4xl">🎉</span>
                    <h4 class="font-extrabold text-gray-800 text-lg">Onboarding Check Complete</h4>
                    <p class="text-xs text-gray-500 max-w-xs mx-auto">We have found over 12 highly compatible queer profiles in your city area. Proceed to setup your pronouns and view matches.</p>
                    <a href="/register" class="w-full block btn-primary py-3.5 rounded-xl font-bold shadow text-xs">Claim My Compatibility Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Segmented Pride Flag Divider -->
<div class="flex w-full h-3 shadow-sm my-8">
    <div class="flex-1 bg-[#FF0000]"></div>
    <div class="flex-1 bg-[#FF8F00]"></div>
    <div class="flex-1 bg-[#FFEB3B]"></div>
    <div class="flex-1 bg-[#4CAF50]"></div>
    <div class="flex-1 bg-[#1976D2]"></div>
    <div class="flex-1 bg-[#7B1FA2]"></div>
</div>

<!-- Success Stories Section -->
<div class="max-w-7xl mx-auto px-6 py-12">
    <div class="text-center max-w-2xl mx-auto mb-10 space-y-2">
        <span class="bg-pink-100 text-pink-700 text-[10px] px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">Success Stories</span>
        <h2 class="text-3xl font-extrabold text-gray-900 serif-font">Real Matches, True Love</h2>
        <p class="text-gray-600 text-sm">Read the stories of LGBTQ+ couples who found compatibility and companionship on Proud Hearts.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Story 1 -->
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-lg bg-white/40 flex flex-col md:flex-row gap-6 items-center">
            <img src="/uploads/couple1.png" class="w-32 h-32 md:w-40 md:h-40 object-cover rounded-2xl shadow border border-pink-100 shrink-0">
            <div class="space-y-2">
                <span class="text-xs font-bold text-pink-600">💍 Married in Paris</span>
                <h4 class="font-extrabold text-gray-800 text-base">Elena &amp; Maya</h4>
                <p class="text-xs text-gray-500 leading-relaxed">"We met through the compatibility matcher and instantly clicked over our shared passion for travel and art. Proud Hearts made us feel safe, represented, and supported."</p>
            </div>
        </div>

        <!-- Story 2 -->
        <div class="glass-panel p-6 rounded-3xl border border-white/60 shadow-lg bg-white/40 flex flex-col md:flex-row gap-6 items-center">
            <img src="/uploads/couple2.png" class="w-32 h-32 md:w-40 md:h-40 object-cover rounded-2xl shadow border border-indigo-100 shrink-0">
            <div class="space-y-2">
                <span class="text-xs font-bold text-indigo-600">💑 In a Relationship</span>
                <h4 class="font-extrabold text-gray-800 text-base">Kai &amp; Taylor</h4>
                <p class="text-xs text-gray-500 leading-relaxed">"Finding a matrimonial platform that fully respects pronouns and gender identities was crucial. The direct messaging workspace let us build deep trust before meeting."</p>
            </div>
        </div>
    </div>
</div>

<!-- "How It Works" Section -->
<div class="py-16 bg-white/30 border-t border-b border-white/20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
            <span class="bg-pink-100 text-pink-700 text-[10px] px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">The Process</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 serif-font">How Matchmaking Works</h2>
            <p class="text-gray-600 text-sm">We combine secure data privacy protocols with orientation constraints to provide safety outcomes.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="text-center space-y-3 p-6 glass-panel rounded-3xl border border-white/50 bg-white/30">
                <span class="w-12 h-12 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center text-xl font-bold mx-auto">1</span>
                <h3 class="font-extrabold text-gray-800 text-base">Setup Pronouns &amp; Identity</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Customize orientation parameters and pronoun definitions so others know exactly how to respect you.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center space-y-3 p-6 glass-panel rounded-3xl border border-white/50 bg-white/30">
                <span class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xl font-bold mx-auto">2</span>
                <h3 class="font-extrabold text-gray-800 text-base">Explore Matches</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Access matched compatibility scores based on lifestyle intents, and customize filter sliders in real-time.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center space-y-3 p-6 glass-panel rounded-3xl border border-white/50 bg-white/30">
                <span class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-xl font-bold mx-auto">3</span>
                <h3 class="font-extrabold text-gray-800 text-base">Connect &amp; Meet</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Request mutual matchmaking compatibility reviews, unlock direct contacts, and start live conversations.</p>
            </div>
        </div>
    </div>
</div>

<!-- Plan comparison Section -->
<div class="py-16 max-w-7xl mx-auto px-6 space-y-10">
    <div class="text-center space-y-2">
        <span class="bg-pink-100 text-pink-700 text-[10px] px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">Comparison Table</span>
        <h2 class="text-3xl font-extrabold text-gray-900 serif-font">Choose Your Journey Scope</h2>
        <p class="text-gray-600 text-sm">Compare pricing tiers to decide which plan offers the tools you need.</p>
    </div>

    <div class="glass-panel overflow-hidden rounded-3xl border border-white/60 shadow-xl max-w-4xl mx-auto bg-white/50">
        <table class="w-full text-left border-collapse text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-150 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                    <th class="py-4 px-6">Match Capabilities</th>
                    <th class="py-4 px-6 text-center">Free (₹0)</th>
                    <th class="py-4 px-6 text-center text-pink-600">Premium (₹499)</th>
                    <th class="py-4 px-6 text-center text-indigo-600">Platinum (₹999)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                <tr>
                    <td class="py-4 px-6 font-semibold">View Matches Feed</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                </tr>
                <tr class="bg-white/20">
                    <td class="py-4 px-6 font-semibold">Pronoun Visibility</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                </tr>
                <tr>
                    <td class="py-4 px-6 font-semibold">Unblurred Profile Photos</td>
                    <td class="py-4 px-6 text-center">❌ Blurred</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                </tr>
                <tr class="bg-white/20">
                    <td class="py-4 px-6 font-semibold">Direct Real-Time Chat</td>
                    <td class="py-4 px-6 text-center">❌ No</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                </tr>
                <tr>
                    <td class="py-4 px-6 font-semibold">Advanced Filters (City/Intent)</td>
                    <td class="py-4 px-6 text-center">❌ No</td>
                    <td class="py-4 px-6 text-center">❌ No</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                </tr>
                <tr class="bg-white/20">
                    <td class="py-4 px-6 font-semibold">Real-Time Profile View Alerts</td>
                    <td class="py-4 px-6 text-center">❌ No</td>
                    <td class="py-4 px-6 text-center">❌ No</td>
                    <td class="py-4 px-6 text-center">✔️ Yes</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Curated Dating Advice & Relationship News (eHarmony style) -->
<div class="py-16 space-y-8 max-w-7xl mx-auto px-6">
    <div class="text-center space-y-2">
        <span class="bg-indigo-100 text-indigo-700 text-[10px] px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">Dating Guide</span>
        <h2 class="text-3xl font-extrabold text-gray-900 serif-font">Expert Relationship Advice</h2>
        <p class="text-gray-600 text-sm">Curated tips written by queer relationship advisors and safe matchmaking experts.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
        <!-- Article Card 1 -->
        <div class="glass-panel rounded-3xl overflow-hidden border border-white/60 hover:shadow-lg transition flex flex-col h-full card-premium bg-white/40">
            <div class="aspect-[16/10] bg-gray-200">
                <img src="/uploads/article1.png" class="w-full h-full object-cover">
            </div>
            <div class="p-6 space-y-3 flex-grow flex flex-col justify-between">
                <div class="space-y-2">
                    <span class="text-[9px] font-bold text-pink-600 uppercase tracking-widest">Safety Rules</span>
                    <h3 class="font-bold text-gray-800 text-base line-clamp-2">Navigating Matrimonial Apps Securely</h3>
                    <p class="text-xs text-gray-500 line-clamp-3">Learn tips on keeping your coordinates private, identifying fake checks, and initiating safe offline coffee dates.</p>
                </div>
                <span class="text-[10px] font-bold text-indigo-600 mt-2 block">Read Article &rarr;</span>
            </div>
        </div>

        <!-- Article Card 2 -->
        <div class="glass-panel rounded-3xl overflow-hidden border border-white/60 hover:shadow-lg transition flex flex-col h-full card-premium bg-white/40">
            <div class="aspect-[16/10] bg-gray-200">
                <img src="/uploads/article2.png" class="w-full h-full object-cover">
            </div>
            <div class="p-6 space-y-3 flex-grow flex flex-col justify-between">
                <div class="space-y-2">
                    <span class="text-[9px] font-bold text-pink-600 uppercase tracking-widest">Matchmaking</span>
                    <h3 class="font-bold text-gray-800 text-base line-clamp-2">How Pronouns Impact Compatibility Scores</h3>
                    <p class="text-xs text-gray-500 line-clamp-3">Our matching algorithms prioritize users who showcase genuine identity setups, yielding 40% higher marriage outcomes.</p>
                </div>
                <span class="text-[10px] font-bold text-indigo-600 mt-2 block">Read Article &rarr;</span>
            </div>
        </div>

        <!-- Article Card 3 -->
        <div class="glass-panel rounded-3xl overflow-hidden border border-white/60 hover:shadow-lg transition flex flex-col h-full card-premium bg-white/40">
            <div class="aspect-[16/10] bg-gray-200">
                <img src="/uploads/article3.png" class="w-full h-full object-cover">
            </div>
            <div class="p-6 space-y-3 flex-grow flex flex-col justify-between">
                <div class="space-y-2">
                    <span class="text-[9px] font-bold text-pink-600 uppercase tracking-widest">Dating Advice</span>
                    <h3 class="font-bold text-gray-800 text-base line-clamp-2">Finding Your Lifelong Partner in 2026</h3>
                    <p class="text-xs text-gray-500 line-clamp-3">Explore modern guidelines on starting chats, communicating relationship intent transparently, and setting mutual priorities.</p>
                </div>
                <span class="text-[10px] font-bold text-indigo-600 mt-2 block">Read Article &rarr;</span>
            </div>
        </div>
    </div>
</div>

<!-- Safety & Verification Standards Section -->
<div class="py-16 bg-white/20 border-t border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <span class="bg-green-100 text-green-700 text-[10px] px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">Security First</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 serif-font">Our Safe Matrimony Protocols</h2>
                <p class="text-gray-600 text-sm leading-relaxed">
                    We designed Proud Hearts to protect your privacy first. Traditional matrimony sites expose your sensitive contact info immediately. Our custom gating protocols put you in complete control.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-xl bg-pink-50 p-2 rounded-xl text-pink-500">🛡️</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm">Gated Profiles</h4>
                            <p class="text-xs text-gray-500 mt-1">Photos and contact details are blurred for unverified users.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-xl bg-indigo-50 p-2 rounded-xl text-indigo-500">👁️</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm">View Notifications</h4>
                            <p class="text-xs text-gray-500 mt-1">Get real-time browser alerts when users inspect your profile.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-xl bg-purple-50 p-2 rounded-xl text-purple-500">⚖️</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm">Moderation Suite</h4>
                            <p class="text-xs text-gray-500 mt-1">Easily report or block user accounts with immediate response times.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-xl bg-pink-50 p-2 rounded-xl text-pink-500">🔒</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm">Data Encryption</h4>
                            <p class="text-xs text-gray-500 mt-1">Fully encrypted chat logs and profile preferences secure your data.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="glass-panel p-8 rounded-3xl border border-white/60 shadow-xl space-y-6">
                <h3 class="font-extrabold text-gray-800 text-lg serif-font">Our Safety Pledges</h3>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="bg-green-100 text-green-700 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0">✓</span>
                        <span class="text-xs text-gray-600 font-semibold">100% Manual moderator screening of all reported users</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="bg-green-100 text-green-700 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0">✓</span>
                        <span class="text-xs text-gray-600 font-semibold">Zero Tolerance policy for harassment, misgendering, or spam</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="bg-green-100 text-green-700 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0">✓</span>
                        <span class="text-xs text-gray-600 font-semibold">Gated access ensures search engines cannot index private profiles</span>
                    </li>
                </ul>
                <div class="bg-pink-50 border border-pink-100 p-4 rounded-2xl flex items-center gap-3 text-xs text-pink-700">
                    <span>💡</span>
                    <p class="font-medium">You can choose to hide your profile card from the general feed anytime in match settings.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="py-16 max-w-4xl mx-auto px-6 space-y-10">
    <div class="text-center space-y-2">
        <span class="bg-pink-100 text-pink-700 text-[10px] px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">Frequently Asked Questions</span>
        <h2 class="text-3xl font-extrabold text-gray-900 serif-font">Common Inquiries</h2>
        <p class="text-gray-600 text-sm">Everything you need to know about navigating compatibility, plans, and platform safety.</p>
    </div>

    <div class="space-y-4">
        <!-- FAQ 1 -->
        <details class="group class-panel rounded-3xl border border-white/60 p-6 [&_summary::-webkit-details-marker]:hidden bg-white/40 cursor-pointer transition">
            <summary class="flex justify-between items-center gap-1.5 text-gray-800">
                <h3 class="font-extrabold text-sm md:text-base">How does compatibility scoring work?</h3>
                <span class="transition duration-300 group-open:-rotate-180 text-pink-500 font-bold text-lg">&darr;</span>
            </summary>
            <p class="mt-4 leading-relaxed text-xs md:text-sm text-gray-600">
                We analyze your pronouns, sexual orientation, gender identity parameters, and relationship intent options. In contrast to conventional matrimonial platforms that make binary assumptions, our custom scoring engine evaluates alignment on lifestyle intents, relationship values, and personal pronouns settings.
            </p>
        </details>

        <!-- FAQ 2 -->
        <details class="group class-panel rounded-3xl border border-white/60 p-6 [&_summary::-webkit-details-marker]:hidden bg-white/40 cursor-pointer transition">
            <summary class="flex justify-between items-center gap-1.5 text-gray-800">
                <h3 class="font-extrabold text-sm md:text-base">Why are some photos and contact details blurred?</h3>
                <span class="transition duration-300 group-open:-rotate-180 text-pink-500 font-bold text-lg">&darr;</span>
            </summary>
            <p class="mt-4 leading-relaxed text-xs md:text-sm text-gray-600">
                To guarantee safety and filter out non-serious users, we blur sensitive details (e.g. email, phone numbers, and full-size images) for Free members. Upgrading to Premium or Platinum immediately unlocks high-res views and direct communication lines.
            </p>
        </details>

        <!-- FAQ 3 -->
        <details class="group class-panel rounded-3xl border border-white/60 p-6 [&_summary::-webkit-details-marker]:hidden bg-white/40 cursor-pointer transition">
            <summary class="flex justify-between items-center gap-1.5 text-gray-800">
                <h3 class="font-extrabold text-sm md:text-base">How does the platform handle user safety &amp; reporting?</h3>
                <span class="transition duration-300 group-open:-rotate-180 text-pink-500 font-bold text-lg">&darr;</span>
            </summary>
            <p class="mt-4 leading-relaxed text-xs md:text-sm text-gray-600">
                If you encounter spam, inappropriate behavior, or someone misrepresenting their identity, you can immediately file a report using the Flag profile action or block them outright. Our moderation team reviews all reports inside our custom moderator panel within 24 hours.
            </p>
        </details>
    </div>
</div>

<script>
    function nextQuizStep(answer) {
        document.getElementById('quiz-q1').classList.add('hidden');
        document.getElementById('quiz-result').classList.remove('hidden');
    }

    async function demoLogin(email, password) {
        try {
            const res = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (data.success) {
                document.cookie = `jwt_token=${data.tokens.access_token}; path=/; max-age=${data.tokens.expires_in}; SameSite=Lax`;
                window.location.href = '/discovery';
            } else {
                alert('Demo Login failed: ' + data.error);
            }
        } catch (err) {
            alert('Connection error.');
        }
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
