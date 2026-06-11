    </main>

    <!-- Minimalist Footer -->
    <footer class="glass-panel mt-auto border-t border-gray-200/50 bg-white/20 py-8 px-6 text-xs text-gray-500">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Top Row -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Brand Logo -->
                <a href="<?= $currentUser ? '/discovery' : '/' ?>" class="flex items-center gap-1.5 group">
                    <span class="text-2xl font-black serif-font text-[#be185d] tracking-tight transition group-hover:scale-105 duration-300">
                        PrideUnion
                    </span>
                </a>
                
                <!-- Links -->
                <div class="flex flex-wrap items-center justify-center gap-3 md:gap-4 font-semibold text-gray-600">
                    <a href="/mission" class="hover:text-pink-600 transition">About</a>
                    <span class="text-gray-300">•</span>
                    <a href="/safe-dating-tips" class="hover:text-pink-600 transition">Safety Guidelines</a>
                    <span class="text-gray-300">•</span>
                    <a href="/privacy" class="hover:text-pink-600 transition">Privacy Policy</a>
                    <span class="text-gray-300">•</span>
                    <a href="/terms" class="hover:text-pink-600 transition">Terms of Use</a>
                    <span class="text-gray-300">•</span>
                    <a href="/trust-safety" class="hover:text-pink-600 transition">Contact Support</a>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="border-t border-gray-200/40 pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-gray-400">
                <p class="flex items-center gap-1 font-medium">Made with 🌈 Pride for all.</p>
                <p>&copy; 2026 PrideUnion Matrimony. Proudly Verified.</p>
            </div>
        </div>
    </footer>
</body>
</html>
