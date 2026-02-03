<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../cart.php';
require_once __DIR__ . '/../utils.php';

$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/custom.css">
</head>
<body class="bg-cream font-sans text-slate-custom selection:bg-emerald-custom/20 selection:text-emerald-custom">

<!-- Navigation -->
<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-custom/10">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-2">
            <a href="<?= SITE_URL ?>" class="flex items-center gap-2">
                <div class="bg-emerald-custom p-2 rounded-lg text-white shadow-lg shadow-emerald-custom/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-custom hidden sm:block uppercase"><?= SITE_NAME ?></span>
            </a>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-8 font-medium text-slate-custom/70">
            <a href="<?= SITE_URL ?>" class="hover:text-emerald-custom transition-colors">Shop</a>
            <a href="<?= SITE_URL ?>track-order.php" class="hover:text-emerald-custom transition-colors">Track Order</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>profile.php" class="hover:text-emerald-custom transition-colors">Profile</a>
            <?php endif; ?>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-4">
            <!-- Search (Desktop) -->
            <div class="relative group hidden sm:block">
                <form action="<?= SITE_URL ?>search.php" method="GET">
                    <input type="text" name="q" placeholder="Search products..." 
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                           class="pl-10 pr-4 py-2 rounded-full bg-white border border-slate-custom/20 focus:border-emerald-custom focus:ring-2 focus:ring-emerald-custom/10 transition-all text-sm w-48 lg:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-custom/40 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </form>
            </div>

            <!-- Cart -->
            <a href="<?= SITE_URL ?>cart.php" class="relative p-2 text-slate-custom hover:bg-cream rounded-full transition-colors border border-transparent hover:border-slate-custom/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <?php if ($cartCount > 0): ?>
                    <span class="absolute top-0 right-0 bg-amber-custom text-white text-xs font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- User Menu -->
            <?php if (isLoggedIn()): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 text-slate-custom hover:text-emerald-custom transition-colors">
                        <span class="hidden sm:inline">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-custom/10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                        <div class="py-2">
                            <a href="<?= SITE_URL ?>profile.php" class="block px-4 py-2 text-sm text-slate-custom hover:bg-cream">Profile</a>
                            <a href="<?= SITE_URL ?>orders.php" class="block px-4 py-2 text-sm text-slate-custom hover:bg-cream">My Orders</a>
                            <?php if (isAdmin()): ?>
                                <hr class="my-2 border-slate-custom/10">
                                <a href="<?= ADMIN_URL ?>" class="block px-4 py-2 text-sm text-emerald-custom hover:bg-cream">Admin Panel</a>
                            <?php endif; ?>
                            <hr class="my-2 border-slate-custom/10">
                            <a href="<?= SITE_URL ?>logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-cream">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= SITE_URL ?>login.php" class="text-emerald-custom font-medium">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Search -->
    <div class="sm:hidden px-4 pb-3">
        <form action="<?= SITE_URL ?>search.php" method="GET">
            <div class="relative">
                <input type="text" name="q" placeholder="Search products..." 
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                       class="w-full pl-10 pr-4 py-2 rounded-full bg-white border border-slate-custom/20 focus:border-emerald-custom focus:ring-2 focus:ring-emerald-custom/10 transition-all text-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-custom/40 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </form>
    </div>
</nav>

<!-- Flash Messages -->
<?php if (hasFlashMessage('success')): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-4 mt-4" role="alert">
        <span class="block sm:inline"><?= getFlashMessage('success') ?></span>
    </div>
<?php endif; ?>

<?php if (hasFlashMessage('error')): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-4 mt-4" role="alert">
        <span class="block sm:inline"><?= getFlashMessage('error') ?></span>
    </div>
<?php endif; ?>

<?php if (hasFlashMessage('info')): ?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mx-4 mt-4" role="alert">
        <span class="block sm:inline"><?= getFlashMessage('info') ?></span>
    </div>
<?php endif; ?>