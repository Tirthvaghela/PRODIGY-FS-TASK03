<!-- Admin Header -->
<header class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-emerald rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">LP</span>
                </div>
                <h1 class="text-2xl font-bold text-gradient-emerald"><?= SITE_NAME ?> Admin</h1>
            </div>
            
            <nav class="flex items-center space-x-6">
                <a href="<?= SITE_URL ?>/admin/index.php" class="text-gray-700 hover:text-emerald-600">Dashboard</a>
                <a href="<?= SITE_URL ?>/admin/products.php" class="text-gray-700 hover:text-emerald-600">Products</a>
                <a href="<?= SITE_URL ?>/admin/orders.php" class="text-gray-700 hover:text-emerald-600">Orders</a>
                <a href="<?= SITE_URL ?>/index.php" class="text-gray-700 hover:text-emerald-600">View Store</a>
                <a href="<?= SITE_URL ?>/logout.php" class="text-gray-700 hover:text-emerald-600">Logout</a>
            </nav>
        </div>
    </div>
</header>