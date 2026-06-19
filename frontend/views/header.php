<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PrideUnion is a premium, safe, and inclusive matrimony platform designed exclusively for the LGBTQ+ community. Express interests, chat in real-time, and find your soulmate.">
    <meta name="keywords" content="LGBTQ matrimony, queer matchmaking, gay marriage, lesbian matchmaking, non-binary partnership, inclusive matrimony">
    <meta name="robots" content="index, follow">
    <title>PrideUnion — Inclusive LGBTQ+ Matrimony</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #FFF1F6 0%, #FFF0F5 40%, #FDF4FF 70%, #F3E8FF 100%);
            min-height: 100vh;
        }
        .serif-font {
            font-family: 'Playfair Display', serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f43f5e 0%, #ec4899 50%, #a855f7 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(236, 72, 153, 0.5);
            background: linear-gradient(135deg, #db2777 0%, #a855f7 50%, #6366f1 100%);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px 0 rgba(236, 72, 153, 0.05);
        }
        .card-premium {
            transition: all 0.3s ease;
        }
        .card-premium:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(236, 72, 153, 0.15);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen text-gray-800">
    <!-- Navbar -->
    <nav class="glass-panel sticky top-0 z-50 px-6 py-4 shadow-sm border-b border-white/20">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-1.5 group">
                <span class="text-2xl font-black serif-font text-[#be185d] tracking-tight transition group-hover:scale-105 duration-300">
                    PrideUnion
                </span>
                <span class="bg-[#fce7f3] text-[#db2777] text-xs font-black px-2.5 py-0.5 rounded-full shadow-sm">
                    Matrimony
                </span>
            </a>

            <!-- Navigation Links -->
            <div class="flex items-center gap-6">
                <?php if ($currentUser): ?>
                    <a href="/dashboard" class="text-gray-600 hover:text-pink-600 font-semibold text-sm transition flex items-center gap-1">
                        🏠 Dashboard
                    </a>
                    <a href="/discovery" class="text-gray-600 hover:text-pink-600 font-semibold text-sm transition flex items-center gap-1">
                        🧭 Discover
                    </a>
                    <a href="/chat" class="text-gray-600 hover:text-pink-600 font-semibold text-sm transition flex items-center gap-1">
                        💬 Chat
                    </a>
                    <a href="/notifications" class="text-gray-600 hover:text-pink-600 font-semibold text-sm transition flex items-center gap-1 relative">
                        🔔 Alerts
                        <span id="nav-notif-dot" class="<?= $notifDotClass ?> absolute -top-1 -right-2 bg-red-500 w-2.5 h-2.5 rounded-full ring-2 ring-white"></span>
                    </a>
                    <a href="/profile/setup" class="text-gray-600 hover:text-pink-600 font-semibold text-sm transition flex items-center gap-1">
                        👤 Profile
                    </a>
                    
                    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                        <a href="/admin" class="bg-indigo-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold hover:bg-indigo-700 transition">🛡️ Admin</a>
                    <?php endif; ?>

                    <!-- Tier Badge -->
                    <?php if ($isGold): ?>
                        <span class="bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-500 text-white text-xs px-3 py-1.5 rounded-full font-bold shadow-sm flex items-center gap-1">👑 Gold</span>
                    <?php endif; ?>
                    <?php if ($isSilver): ?>
                        <span class="bg-gradient-to-r from-slate-400 to-slate-500 text-white text-xs px-3 py-1.5 rounded-full font-bold shadow-sm flex items-center gap-1">🥈 Silver</span>
                        <span class="bg-amber-50 text-amber-700 text-xs px-3 py-1.5 rounded-full font-bold border border-amber-200 flex items-center gap-1">💰 <?= htmlspecialchars($currentUser['credits']) ?> Credits</span>
                    <?php endif; ?>
                    <?php if ($isFree): ?>
                        <a href="/subscription" class="bg-pink-100 text-pink-700 text-xs px-3 py-1.5 rounded-full font-bold border border-pink-200 hover:bg-pink-200 transition flex items-center gap-1">✨ Go Premium</a>
                    <?php endif; ?>

                    <div class="border-l border-gray-200 h-6 mx-1"></div>
                    <a href="/logout" class="text-red-500 hover:text-red-700 text-sm font-semibold transition">Logout</a>
                <?php else: ?>
                    <a href="/login" class="text-gray-600 hover:text-pink-600 font-semibold transition">Sign In</a>
                    <a href="/register" class="btn-primary px-5 py-2.5 rounded-full font-semibold shadow-md">Register Free</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Toast Alerts Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3 max-w-sm"></div>

    <!-- Live Toast Javascript -->
    <script>
        <?php if ($currentUser): ?>
            // Boot WebSockets Connection
            const token = "<?= $_COOKIE['jwt_token'] ?? '' ?>";
            const wsUrl = "ws://" + window.location.host + "/ws/?token=" + encodeURIComponent(token);
            const ws = new WebSocket(wsUrl);

            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (data.type === 'notification') {
                    showToast(data.notification.title, data.notification.message);
                    const dot = document.getElementById('nav-notif-dot');
                    if (dot) dot.classList.remove('hidden');
                } else if (data.type === 'message') {
                    showToast("New Message", data.message.message);
                }
            };

            function showToast(title, message) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = 'glass-panel bg-white/90 p-4 rounded-xl shadow-lg border-l-4 border-pink-500 transform translate-y-5 opacity-0 transition duration-300';
                toast.innerHTML = `
                    <h4 class="font-bold text-gray-800 text-sm">${title}</h4>
                    <p class="text-gray-600 text-xs mt-1">${message}</p>
                `;
                container.appendChild(toast);
                setTimeout(() => {
                    toast.classList.remove('translate-y-5', 'opacity-0');
                }, 100);

                setTimeout(() => {
                    toast.classList.add('opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            }
        <?php endif; ?>
    </script>

    <main class="flex-grow max-w-7xl w-full mx-auto p-6">
