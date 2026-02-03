<!-- Footer -->
<footer class="bg-white border-t border-slate-custom/10 pt-24 pb-12 mt-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-12 mb-20">
            <!-- Brand -->
            <div class="col-span-2">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-emerald-custom p-3 rounded-xl text-white shadow-xl shadow-emerald-custom/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <span class="font-black text-2xl tracking-tight text-slate-custom uppercase"><?= SITE_NAME ?></span>
                </div>
                <p class="text-slate-custom/60 max-w-sm font-medium leading-relaxed">
                    Reconnecting communities with their food. Every product has a story, a family, and a farm behind it. Shop local, live better.
                </p>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="font-black text-slate-custom mb-8 uppercase text-xs tracking-widest opacity-40">Categories</h4>
                <ul class="space-y-4 text-slate-custom/60 font-bold text-sm">
                    <li><a href="<?= SITE_URL ?>?category=5" class="hover:text-emerald-custom transition-colors">Fresh Produce</a></li>
                    <li><a href="<?= SITE_URL ?>?category=6" class="hover:text-emerald-custom transition-colors">Artisan Bakery</a></li>
                    <li><a href="<?= SITE_URL ?>?category=7" class="hover:text-emerald-custom transition-colors">Local Dairy</a></li>
                    <li><a href="<?= SITE_URL ?>?category=8" class="hover:text-emerald-custom transition-colors">Pantry Essentials</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h4 class="font-black text-slate-custom mb-8 uppercase text-xs tracking-widest opacity-40">Resources</h4>
                <ul class="space-y-4 text-slate-custom/60 font-bold text-sm">
                    <li><a href="#" class="hover:text-emerald-custom transition-colors">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>track-order.php" class="hover:text-emerald-custom transition-colors">Track Order</a></li>
                    <li><a href="#" class="hover:text-emerald-custom transition-colors">Return Policy</a></li>
                    <li><a href="#" class="hover:text-emerald-custom transition-colors">Contact Us</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="border-t border-slate-custom/10 pt-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-xs font-black text-slate-custom/30 uppercase tracking-wider">
                © <?= date('Y') ?> <?= SITE_NAME ?>. Crafting Neighborhood Connections.
            </p>
            <div class="flex gap-10 text-xs font-black uppercase tracking-wider text-slate-custom/30">
                <a href="#" class="hover:text-emerald-custom transition-colors">Privacy</a>
                <a href="#" class="hover:text-emerald-custom transition-colors">Terms</a>
                <a href="#" class="hover:text-emerald-custom transition-colors">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>assets/js/main.js"></script>
</body>
</html>