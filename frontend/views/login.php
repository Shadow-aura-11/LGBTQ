<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to PrideUnion — the inclusive LGBTQ+ matrimony platform. Find your soulmate today.">
    <title>Sign In — PrideUnion Matrimony</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; margin: 0; }
        .serif-font { font-family: 'Playfair Display', serif; }

        .login-left {
            background: linear-gradient(160deg, #fce4ec 0%, #f8bbd0 40%, #f48fb1 75%, #f06292 100%);
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -20%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .btn-login {
            background: linear-gradient(135deg, #F48FB1 0%, #ec407a 100%);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -8px rgba(236, 64, 122, 0.5);
        }

        .input-field {
            transition: all 0.2s ease;
        }
        .input-field:focus {
            border-color: #f48fb1;
            box-shadow: 0 0 0 3px rgba(244, 143, 177, 0.15);
        }

        .social-btn {
            transition: all 0.2s ease;
        }
        .social-btn:hover {
            background: #fce4ec;
            border-color: #f48fb1;
        }

        .members-badge {
            animation: slideUp 0.6s ease-out 1s both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-image-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 55%;
            overflow: hidden;
        }
        .hero-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
            mask-image: linear-gradient(to bottom, transparent 0%, black 25%);
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 25%);
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- LEFT PANEL — Pink gradient with testimonial -->
        <div class="login-left lg:w-[48%] w-full min-h-[340px] lg:min-h-screen flex flex-col justify-between p-8 md:p-12 relative">
            <!-- Brand -->
            <div class="relative z-10">
                <a href="/" class="inline-block">
                    <span class="text-2xl md:text-3xl font-extrabold text-rose-800 tracking-tight serif-font">PrideUnion</span>
                </a>
            </div>

            <!-- Testimonial Quote -->
            <div class="relative z-10 my-auto py-12 lg:py-0">
                <blockquote class="space-y-4 max-w-md">
                    <p class="text-2xl md:text-4xl lg:text-[2.6rem] font-bold text-rose-900 leading-tight serif-font italic">
                        "We found our happy place together."
                    </p>
                    <cite class="block text-rose-800/80 text-sm md:text-base font-medium not-italic">
                        — Priya & Simran, Married 2025
                    </cite>
                </blockquote>

                <!-- Members badge -->
                <div class="members-badge mt-8 inline-flex items-center gap-2.5 bg-white/35 backdrop-blur-sm px-5 py-3 rounded-full border border-white/40 shadow-sm">
                    <span class="text-lg">🎉</span>
                    <span class="text-rose-900 text-xs md:text-sm font-semibold">Over 25 new verified members joined in your area today!</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10">
                <p class="text-rose-800/50 text-xs font-medium">&copy; 2026 PrideUnion Matrimony. All Rights Reserved.</p>
            </div>

            <!-- Hero Image Overlay at bottom (hidden on smaller screens for cleanliness) -->
            <div class="hero-image-container hidden lg:block">
                <img src="/uploads/login_hero.png" alt="Happy couple">
            </div>
        </div>

        <!-- RIGHT PANEL — Login Form -->
        <div class="lg:w-[52%] w-full flex items-center justify-center bg-white p-8 md:p-16 lg:p-20">
            <div class="w-full max-w-md">
                <!-- Welcome Header -->
                <div class="mb-10">
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 serif-font">Welcome Back</h1>
                    <p class="text-gray-500 text-sm mt-2">Enter your details to log in to your verified account.</p>
                </div>

                <!-- Error Box -->
                <div id="error-box" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span id="error-text"></span>
                </div>

                <!-- Login Form -->
                <form id="login-form" class="space-y-5">
                    <div>
                        <label for="email" class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" id="email" required placeholder="hello@prideunion.com"
                               class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 outline-none text-sm text-gray-800 bg-white placeholder:text-gray-400">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest">Password</label>
                            <a href="#" class="text-[11px] font-semibold text-pink-500 hover:text-pink-700 transition">Forgot?</a>
                        </div>
                        <input type="password" id="password" required placeholder="••••••••••"
                               class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 outline-none text-sm text-gray-800 bg-white placeholder:text-gray-400">
                    </div>

                    <button type="submit" id="login-submit-btn" class="btn-login w-full py-3.5 rounded-xl font-bold text-sm shadow-md">
                        Log In
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-7 text-center">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <span class="relative bg-white px-4 text-[10px] text-gray-400 uppercase font-bold tracking-widest">Or continue with</span>
                </div>

                <!-- Google Auth -->
                <button id="google-login-btn" class="social-btn w-full flex items-center justify-center gap-3 bg-white border border-gray-200 py-3.5 rounded-xl text-sm font-semibold text-gray-700 shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google Authentication
                </button>

                <!-- Demo Quick Login (dev-only) -->
                <div class="mt-6 border border-dashed border-gray-200 rounded-xl p-4 bg-gray-50/50">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider text-center mb-3">Quick Demo Access</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="demoLogin('sam@lgbtqmatrimony.local', 'password')" 
                                class="bg-white border border-gray-200 hover:border-pink-300 hover:bg-pink-50 text-gray-700 py-2.5 rounded-lg text-[10px] font-bold transition shadow-sm">
                            Free User
                        </button>
                        <button type="button" onclick="demoLogin('jordan@lgbtqmatrimony.local', 'password')" 
                                class="bg-pink-500 hover:bg-pink-600 text-white py-2.5 rounded-lg text-[10px] font-bold transition shadow-sm">
                            Premium 👑
                        </button>
                        <button type="button" onclick="demoLogin('admin@lgbtqmatrimony.local', 'AdminSecure2026!')" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-[10px] font-bold transition shadow-sm">
                            Admin 🛡️
                        </button>
                    </div>
                </div>

                <!-- Register Link -->
                <p class="text-center text-sm text-gray-500 mt-8">
                    Don't have an account? <a href="/register" class="text-pink-600 font-bold hover:underline">Register free</a>
                </p>
            </div>
        </div>
    </div>

<script>
    async function demoLogin(email, password) {
        const errBox = document.getElementById('error-box');
        const errText = document.getElementById('error-text');
        errBox.classList.add('hidden');
        try {
             const res = await fetch('/api/v1/auth/login', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ email, password })
             });
             const data = await res.json();
             if (data.success) {
                 document.cookie = `jwt_token=${data.tokens.access_token}; path=/; max-age=${data.tokens.expires_in}; SameSite=Lax`;
                 window.location.href = data.user.role === 'admin' ? '/admin' : '/dashboard';
             } else {
                 errText.innerText = data.error || 'Demo Login failed.';
                 errBox.classList.remove('hidden');
             }
        } catch (err) {
             errText.innerText = 'Connection error.';
             errBox.classList.remove('hidden');
        }
    }

    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errBox = document.getElementById('error-box');
        const errText = document.getElementById('error-text');
        errBox.classList.add('hidden');

        const btn = document.getElementById('login-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
             const res = await fetch('/api/v1/auth/login', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ email, password })
             });
             const data = await res.json();
             
             if (data.success) {
                 document.cookie = `jwt_token=${data.tokens.access_token}; path=/; max-age=${data.tokens.expires_in}; SameSite=Lax`;
                 window.location.href = data.user.role === 'admin' ? '/admin' : '/dashboard';
             } else {
                 errText.innerText = data.error || 'Invalid credentials.';
                 errBox.classList.remove('hidden');
                 btn.disabled = false;
                 btn.innerHTML = 'Log In';
             }
        } catch (err) {
             errText.innerText = 'Network error. Please try again.';
             errBox.classList.remove('hidden');
             btn.disabled = false;
             btn.innerHTML = 'Log In';
        }
    });

    document.getElementById('google-login-btn').addEventListener('click', async function() {
        const mockToken = "google_token_" + Math.random().toString(36).substring(7);
        const errBox = document.getElementById('error-box');
        const errText = document.getElementById('error-text');
        
        try {
             const res = await fetch('/api/v1/auth/google', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ token: mockToken })
             });
             const data = await res.json();

             if (data.success) {
                 document.cookie = `jwt_token=${data.tokens.access_token}; path=/; max-age=${data.tokens.expires_in}; SameSite=Lax`;
                 window.location.href = data.user.role === 'admin' ? '/admin' : '/dashboard';
             } else {
                 errText.innerText = data.error || 'Google login failed.';
                 errBox.classList.remove('hidden');
             }
        } catch (err) {
             errText.innerText = 'Network error.';
             errBox.classList.remove('hidden');
        }
    });
</script>
</body>
</html>
