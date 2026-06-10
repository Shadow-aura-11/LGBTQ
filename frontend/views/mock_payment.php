<?php
include __DIR__ . '/header.php';

$paymentId = $_GET['pay_id'] ?? '';
$amount = (int)($_GET['amount'] ?? 0);
$currency = $_GET['currency'] ?? 'INR';
$gateway = $_GET['gateway'] ?? 'stripe';
$plan = $_GET['plan'] ?? 'monthly';
$userId = (int)($_GET['user_id'] ?? 0);

if (!$paymentId || !$userId) {
    echo "<p class='text-red-500'>Invalid checkout validation request.</p>";
    include __DIR__ . '/footer.php';
    exit;
}

$displayAmount = ($currency === 'INR') ? '₹' . ($amount / 100) : '$' . ($amount / 100);
?>

<div class="max-w-md mx-auto my-12">
    <div class="glass-panel p-8 rounded-3xl shadow-xl border border-white/60 text-center space-y-6">
        <div>
            <span class="text-4xl bg-pink-50 p-4 rounded-3xl inline-block shadow-inner">💳</span>
            <h2 class="text-2xl font-black text-gray-900 mt-4 serif-font">Secure Sandbox Checkout</h2>
            <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-semibold">Gateway: <?= htmlspecialchars($gateway) ?></p>
        </div>

        <!-- Order details card -->
        <div class="bg-white/40 p-5 rounded-2xl border border-gray-200/50 text-left space-y-3 text-xs">
            <div class="flex justify-between pb-2 border-b border-gray-100">
                <span class="text-gray-500">Upgrade Plan</span>
                <span class="font-bold text-gray-800 capitalize"><?= htmlspecialchars($plan) ?> subscription</span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-gray-500">Order Amount</span>
                <span class="font-black text-pink-600 text-lg"><?= $displayAmount ?></span>
            </div>
            <div class="flex justify-between items-center text-[10px] text-gray-400">
                <span>Payment Reference</span>
                <span class="font-mono"><?= htmlspecialchars($paymentId) ?></span>
            </div>
        </div>

        <div id="error-box" class="hidden bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-xs text-left"></div>

        <button id="pay-confirm-btn" class="w-full btn-primary py-3.5 rounded-xl font-bold shadow-md hover:shadow-lg transition text-xs">
            Complete Payment &amp; Elevate Account
        </button>

        <div class="flex items-center justify-center gap-1 text-[10px] text-gray-400 font-semibold uppercase tracking-wider">
            <span>🔒</span> SSL Encrypted • Sandbox Gateway
        </div>
    </div>
</div>

<script>
    document.getElementById('pay-confirm-btn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerText = "Authorizing checkout transaction...";

        const errBox = document.getElementById('error-box');
        errBox.classList.add('hidden');

        const payload = {
            payment_id: "<?= $paymentId ?>",
            user_id: <?= $userId ?>,
            plan: "<?= $plan ?>",
            gateway: "<?= $gateway ?>"
        };

        try {
            const res = await fetch('/api/v1/subscriptions/webhook', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                // Sync authentication tier locally in session
                await fetch('/api/v1/auth/internal/update-tier', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: <?= $userId ?>, tier: 'premium' })
                });

                // Clear jwt_token cookie to force re-evaluation of premium claims on next sign in
                document.cookie = "jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
                alert("Payment Confirmed! Please sign in again to activate your premium status.");
                window.location.href = '/login';
            } else {
                errBox.innerText = data.error || 'Checkout verification failed.';
                errBox.classList.remove('hidden');
                btn.disabled = false;
                btn.innerText = "Complete Payment";
            }
        } catch (err) {
            errBox.innerText = 'Connection error contacting webhook callback.';
            errBox.classList.remove('hidden');
            btn.disabled = false;
            btn.innerText = "Complete Payment";
        }
    });
</script>

<?php include __DIR__ . '/footer.php'; ?>
