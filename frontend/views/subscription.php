<?php
include __DIR__ . '/header.php';

if (!$currentUser) {
    header('Location: /login');
    exit;
}
?>

<div class="max-w-5xl mx-auto my-12">
    <div class="text-center space-y-4 mb-16">
        <span class="bg-pink-100 text-pink-700 text-xs px-3.5 py-1.5 rounded-full font-bold uppercase tracking-wider">Pricing Plans</span>
        <h2 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight serif-font">Select Your Journey</h2>
        <p class="text-gray-600 max-w-md mx-auto text-sm">Choose a plan that fits your dating goals and unlocks direct contact capabilities.</p>
    </div>

    <!-- Pricing Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Silver Plan -->
        <div class="glass-panel p-8 rounded-3xl border border-white/60 hover:shadow-lg transition relative flex flex-col justify-between shadow-sm bg-white">
            <div>
                <h3 class="font-bold text-gray-800 text-xl flex items-center gap-2">
                    <span>🥈</span> Silver Plan
                </h3>
                <p class="text-gray-500 text-xs mt-1">Chat active. Contact details cost credits.</p>
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-gray-900">₹299</span>
                    <span class="text-gray-500 text-xs"> / month</span>
                </div>

                <ul class="space-y-3.5 text-xs text-gray-600 mb-8 border-t border-gray-100 pt-6 font-medium">
                    <li class="flex items-center gap-2">✔️ Direct Messaging &amp; Live Chat</li>
                    <li class="flex items-center gap-2">✔️ View Full Profile Attributes</li>
                    <li class="flex items-center gap-2">✔️ Unblurred Matching Photos</li>
                    <li class="flex items-center gap-2">❌ Contact Numbers cost 10 Credits</li>
                </ul>
            </div>

            <div class="space-y-2">
                <button onclick="startCheckout('silver', 'stripe')" class="w-full btn-primary py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Stripe
                </button>
                <button onclick="startCheckout('silver', 'razorpay')" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Razorpay (UPI)
                </button>
            </div>
        </div>

        <!-- Gold Plan -->
        <div class="glass-panel p-8 rounded-3xl border-2 border-pink-400 bg-white/80 hover:shadow-xl transition relative flex flex-col justify-between shadow-md">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-pink-500 text-white text-[9px] uppercase font-bold tracking-widest px-3.5 py-1 rounded-full shadow-sm">MOST RECOMMENDED</span>
            
            <div>
                <h3 class="font-bold text-gray-800 text-xl flex items-center gap-2">
                    <span>👑</span> Gold Plan
                </h3>
                <p class="text-gray-500 text-xs mt-1">Unlimited messaging and unlimited contact views.</p>
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-gray-900">₹599</span>
                    <span class="text-gray-500 text-xs"> / month</span>
                </div>

                <ul class="space-y-3.5 text-xs text-gray-600 mb-8 border-t border-gray-100 pt-6 font-medium">
                    <li class="flex items-center gap-2">✔️ Direct Messaging &amp; Live Chat</li>
                    <li class="flex items-center gap-2">✔️ View Full Profile Attributes</li>
                    <li class="flex items-center gap-2">✔️ Unblurred Matching Photos</li>
                    <li class="flex items-center gap-2">✔️ Unlimited direct contact views (Free)</li>
                    <li class="flex items-center gap-2">✔️ Advanced search filters</li>
                </ul>
            </div>

            <div class="space-y-2">
                <button onclick="startCheckout('gold', 'stripe')" class="w-full btn-primary py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Stripe
                </button>
                <button onclick="startCheckout('gold', 'razorpay')" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Razorpay (UPI)
                </button>
            </div>
        </div>

        <!-- Credits Wallet Card -->
        <div class="glass-panel p-8 rounded-3xl border border-white/60 hover:shadow-lg transition relative flex flex-col justify-between shadow-sm bg-white">
            <div>
                <h3 class="font-bold text-gray-800 text-xl flex items-center gap-2">
                    <span>💰</span> Buy Credits
                </h3>
                <p class="text-gray-500 text-xs mt-1">Unlock contact numbers on the Silver plan.</p>
                
                <div class="my-6 space-y-4">
                    <!-- Pack 1 -->
                    <div class="p-4 bg-pink-50/50 border border-pink-100 rounded-2xl flex justify-between items-center text-left">
                        <div>
                            <span class="font-bold text-gray-900 text-sm block">50 Credits</span>
                            <span class="text-[10px] text-gray-400">Unlock up to 5 profiles</span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-pink-600 text-lg block">₹99</span>
                            <div class="flex gap-1.5 mt-1">
                                <button onclick="startCheckout('credits_50', 'stripe')" class="px-2 py-1 bg-pink-600 text-white rounded text-[9px] font-bold">Stripe</button>
                                <button onclick="startCheckout('credits_50', 'razorpay')" class="px-2 py-1 bg-indigo-600 text-white rounded text-[9px] font-bold">UPI</button>
                            </div>
                        </div>
                    </div>

                    <!-- Pack 2 -->
                    <div class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl flex justify-between items-center text-left">
                        <div>
                            <span class="font-bold text-gray-900 text-sm block">120 Credits</span>
                            <span class="text-[10px] text-indigo-400 font-semibold">Best Value! Unlock 12 profiles</span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-indigo-600 text-lg block">₹199</span>
                            <div class="flex gap-1.5 mt-1">
                                <button onclick="startCheckout('credits_120', 'stripe')" class="px-2 py-1 bg-pink-600 text-white rounded text-[9px] font-bold">Stripe</button>
                                <button onclick="startCheckout('credits_120', 'razorpay')" class="px-2 py-1 bg-indigo-600 text-white rounded text-[9px] font-bold">UPI</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-[10px] text-gray-400 text-center mt-4">
                Note: Credits do not expire. Requires Silver or Gold plan to use chat features.
            </div>
        </div>
    </div>
</div>

<script>
    async function startCheckout(plan, gateway) {
        try {
            const res = await fetch('/api/v1/subscriptions/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + '<?= $token ?>'
                },
                body: JSON.stringify({ plan, gateway })
            });
            const data = await res.json();

            if (data.success && data.checkout_url) {
                window.location.href = data.checkout_url;
            } else {
                alert(data.error || 'Failed to initialize checkout.');
            }
        } catch (err) {
            alert('Connection error.');
        }
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
