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

    <!-- Side-by-Side Pricing Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
        <!-- Premium Plan -->
        <div class="glass-panel p-8 rounded-3xl border border-white/60 hover:shadow-lg transition relative flex flex-col justify-between shadow-sm">
            <div>
                <h3 class="font-bold text-gray-800 text-xl flex items-center gap-2">
                    <span>👑</span> Premium
                </h3>
                <p class="text-gray-500 text-xs mt-1">Perfect for starting your matching journey.</p>
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-gray-900">₹499</span>
                    <span class="text-gray-500 text-xs"> / month</span>
                </div>

                <ul class="space-y-3.5 text-xs text-gray-600 mb-8 border-t border-gray-100 pt-6 font-medium">
                    <li class="flex items-center gap-2">✔️ Direct Messaging &amp; Live Chat</li>
                    <li class="flex items-center gap-2">✔️ View Full Profile Attributes</li>
                    <li class="flex items-center gap-2">✔️ Unblurred Matching Photos</li>
                    <li class="flex items-center gap-2">✔️ Standard search filters</li>
                </ul>
            </div>

            <div class="space-y-2">
                <button onclick="startCheckout('monthly', 'stripe')" class="w-full btn-primary py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Stripe
                </button>
                <button onclick="startCheckout('monthly', 'razorpay')" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Razorpay (UPI)
                </button>
            </div>
        </div>

        <!-- Platinum Plan -->
        <div class="glass-panel p-8 rounded-3xl border-2 border-pink-400 bg-white/80 hover:shadow-xl transition relative flex flex-col justify-between shadow-md">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-pink-500 text-white text-[9px] uppercase font-bold tracking-widest px-3.5 py-1 rounded-full shadow-sm">MOST RECOMMENDED</span>
            
            <div>
                <h3 class="font-bold text-gray-800 text-xl flex items-center gap-2">
                    <span>🌟</span> Platinum
                </h3>
                <p class="text-gray-500 text-xs mt-1">Full-suite matchmaking priority.</p>
                <div class="my-6">
                    <span class="text-4xl font-extrabold text-gray-900">₹999</span>
                    <span class="text-gray-500 text-xs"> / month</span>
                </div>

                <ul class="space-y-3.5 text-xs text-gray-600 mb-8 border-t border-gray-100 pt-6 font-medium">
                    <li class="flex items-center gap-2">✔️ Direct Messaging &amp; Live Chat</li>
                    <li class="flex items-center gap-2">✔️ View Full Profile Attributes</li>
                    <li class="flex items-center gap-2">✔️ Unblurred Matching Photos</li>
                    <li class="flex items-center gap-2">✔️ Advanced filters (City / Intent)</li>
                    <li class="flex items-center gap-2">✔️ Priority matching placement</li>
                    <li class="flex items-center gap-2">✔️ Real-time profile view notifications</li>
                </ul>
            </div>

            <div class="space-y-2">
                <button onclick="startCheckout('annual', 'stripe')" class="w-full btn-primary py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Stripe
                </button>
                <button onclick="startCheckout('annual', 'razorpay')" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition shadow text-xs">
                    Pay with Razorpay (UPI)
                </button>
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
