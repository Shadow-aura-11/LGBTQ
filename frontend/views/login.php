<?php include __DIR__ . '/header.php'; ?>

<div class="max-w-md mx-auto my-16">
    <div class="glass-panel p-8 md:p-10 rounded-3xl shadow-xl border border-white/60">
        <div class="text-center space-y-2 mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 serif-font">Welcome Back</h2>
            <p class="text-gray-600 text-xs">Enter your details to view compatible matching profiles.</p>
        </div>

        <div id="error-box" class="hidden bg-red-100 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-sm mb-6"></div>

        <form id="login-form" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" id="email" required placeholder="name@domain.com"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="password" required placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-300 focus:border-transparent outline-none transition bg-white/50 text-sm">
            </div>

            <button type="submit" class="w-full btn-primary py-3.5 rounded-xl font-bold shadow-md hover:shadow-lg transition text-sm">
                Sign In
            </button>
        </form>

        <div class="relative my-6 text-center">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
            <span class="relative bg-transparent px-3 text-[10px] text-gray-500 uppercase font-bold tracking-wider">Or continue with</span>
        </div>

        <button id="google-login-btn" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 py-3 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.2-5.137 4.2-3.417 0-6.19-2.772-6.19-6.19 0-3.417 2.773-6.19 6.19-6.19 1.488 0 2.851.527 3.922 1.402l3.056-3.056C18.96 1.833 15.825 1 12.24 1.002 6.136 1.002 1.2 5.938 1.2 12.042s4.936 11.04 11.04 11.04c5.8 0 10.828-4.148 10.828-11.04 0-.665-.06-1.3-.173-1.757H12.24Z"/>
            </svg>
            Google Identity
        </button>

        <div class="relative my-6 text-center">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
            <span class="relative bg-transparent px-3 text-[10px] text-gray-500 uppercase font-bold tracking-wider">Or Quick Demo Login</span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <button type="button" onclick="demoLogin('sam@lgbtqmatrimony.local', 'password')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-xl text-[10px] font-bold transition shadow-sm">Free User</button>
            <button type="button" onclick="demoLogin('jordan@lgbtqmatrimony.local', 'password')" class="btn-primary py-2.5 rounded-xl text-[10px] font-bold transition shadow-sm">Premium</button>
            <button type="button" onclick="demoLogin('admin@lgbtqmatrimony.local', 'AdminSecure2026!')" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-[10px] font-bold transition shadow-sm">Admin</button>
        </div>

        <p class="text-center text-xs text-gray-500 mt-8">
            Don't have an account? <a href="/register" class="text-pink-600 font-bold hover:underline">Register free</a>
        </p>
    </div>
</div>

<script>
    async function demoLogin(email, password) {
        const errBox = document.getElementById('error-box');
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
                window.location.href = '/dashboard';
            } else {
                errBox.innerText = data.error || 'Demo Login failed.';
                errBox.classList.remove('hidden');
            }
        } catch (err) {
            errBox.innerText = 'Connection error.';
            errBox.classList.remove('hidden');
        }
    }

    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errBox = document.getElementById('error-box');
        errBox.classList.add('hidden');

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
                window.location.href = '/dashboard';
            } else {
                errBox.innerText = data.error || 'Invalid credentials.';
                errBox.classList.remove('hidden');
            }
        } catch (err) {
            errBox.innerText = 'Network error. Please try again.';
            errBox.classList.remove('hidden');
        }
    });

    document.getElementById('google-login-btn').addEventListener('click', async function() {
        const mockToken = "google_token_" + Math.random().toString(36).substring(7);
        const errBox = document.getElementById('error-box');
        
        try {
            const res = await fetch('/api/v1/auth/google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: mockToken })
            });
            const data = await res.json();

            if (data.success) {
                document.cookie = `jwt_token=${data.tokens.access_token}; path=/; max-age=${data.tokens.expires_in}; SameSite=Lax`;
                window.location.href = '/dashboard';
            } else {
                errBox.innerText = data.error || 'Google login failed.';
                errBox.classList.remove('hidden');
            }
        } catch (err) {
            errBox.innerText = 'Network error.';
            errBox.classList.remove('hidden');
        }
    });
</script>

<?php include __DIR__ . '/footer.php'; ?>
