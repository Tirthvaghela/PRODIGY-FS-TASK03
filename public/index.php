<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Product.php';
require_once __DIR__ . '/../src/models/Category.php';
require_once __DIR__ . '/../src/models/Wishlist.php';
require_once __DIR__ . '/../src/image-upload.php';

// Get filters from URL
$filters = [
    'category' => $_GET['category'] ?? '',
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? ''
];

// Get products and categories
$products = Product::getAll($filters);
$featuredProducts = Product::getFeatured(6);
$categories = Category::getWithProductCount();
$currentUser = getCurrentUser();

// Get user's wishlist items if logged in
$userWishlistItems = [];
if ($currentUser) {
    $wishlist = new Wishlist();
    $wishlistItems = $wishlist->getUserWishlist($currentUser['id']);
    // Create array of product IDs for easy checking
    foreach ($wishlistItems as $item) {
        $userWishlistItems[] = $item['product_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Fresh Local Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'emerald': {
                            50: '#ecfdf5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b'
                        },
                        'orange': {
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c'
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100">
        <div class="w-full px-6">
            <div class="relative flex items-center h-16">
                <!-- Logo - Left Side -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-wide">LOCAL PANTRY</h1>
                </div>

                <!-- Navigation - Centered -->
                <nav class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-emerald-700 font-medium border-b-2 border-emerald-700 pb-1">Shop</a>
                    <?php if ($currentUser): ?>
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <a href="wishlist.php" class="text-gray-500 hover:text-gray-700 font-medium">Wishlist</a>
                    <?php endif; ?>
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- User Menu & Cart - Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <?php if ($currentUser): ?>
                        <!-- User Dropdown -->
                        <div class="relative" id="userDropdown">
                            <button onclick="toggleUserDropdown()" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 font-medium focus:outline-none">
                                <span>Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                                <svg class="w-4 h-4 transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span>Profile</span>
                                    </div>
                                </a>
                                <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span>Logout</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php if (isAdmin()): ?>
                            <a href="admin/" class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-3 py-1.5 rounded-md text-sm flex items-center space-x-1.5 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Admin</span>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="text-emerald-700 font-medium">SIGN IN</a>
                    <?php endif; ?>
                    
                    <!-- Cart Icon -->
                    <button onclick="openCartSidebar()" class="relative p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                        <span id="cart-count" class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-gray-900 overflow-hidden min-h-screen flex items-center">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-black opacity-60 z-10"></div>
            <img src="https://assets.bonappetit.com/photos/5bd209294da27a1df47e91c5/16:9/w_5360,h_3015,c_limit/Ina-garten-pantry.jpg" 
                 alt="Local Pantry Background" 
                 class="w-full h-full object-cover">
        </div>
        
        <!-- Content -->
        <div class="relative z-20 w-full">
            <div class="pl-12 md:pl-16 lg:pl-20 xl:pl-24">
                <div class="max-w-4xl">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-emerald-600 text-white mb-8 shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        SINCE 2012 • LOCAL PRODUCE
                    </div>
                    
                    <!-- Main Heading -->
                    <h1 class="text-6xl md:text-7xl lg:text-8xl xl:text-9xl font-bold text-white leading-tight mb-8">
                        Freshness,<br>
                        <span class="text-emerald-400">Sourced Right</span><br>
                        <span class="text-gray-200">at Your Door.</span>
                    </h1>
                    
                    <!-- Subheading -->
                    <p class="text-xl md:text-2xl lg:text-3xl text-gray-300 mb-12 max-w-3xl leading-relaxed font-light">
                        Fresh groceries and local products delivered with care.
                    </p>
                    
                    <!-- CTA Button -->
                    <a href="#shop" class="inline-flex items-center justify-center px-12 py-6 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl text-xl">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                        Start Shopping
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter Bar -->
    <section id="shop" class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="w-full px-6">
            <div class="flex items-center justify-between py-4">
                <!-- Category Filters -->
                <div class="flex items-center space-x-1 overflow-x-auto">
                    <a href="?" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap <?= empty($filters['category']) ? 'bg-emerald-700 text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' ?>">
                        All
                    </a>
                    <?php 
                    $categoryNames = ['Produce', 'Bakery', 'Dairy', 'Pantry', 'Beverages', 'Home'];
                    foreach ($categories as $category): 
                        if (in_array($category['name'], $categoryNames)):
                    ?>
                    <a href="?category=<?= $category['id'] ?>" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap <?= $filters['category'] == $category['id'] ? 'bg-emerald-700 text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                
                <!-- Sort Dropdown -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                        </svg>
                        <span>SORT BY</span>
                    </div>
                    <select name="sort" onchange="updateFilters()" class="text-sm font-medium text-gray-900 bg-transparent border-none focus:ring-0 cursor-pointer">
                        <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Featured</option>
                        <option value="price_low" <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- All Products -->
    <section class="bg-gray-50 min-h-screen">
        <div class="w-full px-6 py-8">
            <?php if (empty($products)): ?>
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🛒</div>
                <h2 class="text-2xl font-bold text-gray-600 mb-4">No products found</h2>
                <p class="text-gray-500 mb-8">Try adjusting your search or browse all categories.</p>
                <a href="?" class="inline-flex items-center px-6 py-3 bg-emerald-700 text-white font-medium rounded-lg hover:bg-emerald-800">
                    View All Products
                </a>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                <?php foreach ($products as $product): ?>
                <div class="product-card bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden cursor-pointer" 
                     data-product-id="<?= $product['id'] ?>" onclick="openProductModal(<?= $product['id'] ?>)">
                    <div class="relative aspect-square overflow-hidden">
                        <img src="<?= ImageUpload::getImageUrl($product['image'], true) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-full object-cover">
                        
                        <!-- Category Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="bg-emerald-700 text-white text-xs font-medium px-3 py-1 rounded-full uppercase tracking-wide">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 text-lg">
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>
                        
                        <!-- Star Rating -->
                        <div class="flex items-center mb-3">
                            <div class="flex items-center">
                                <?php 
                                // Generate random rating for demo (4-5 stars)
                                $rating = rand(40, 50) / 10;
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                $reviews = rand(45, 342);
                                
                                for ($i = 1; $i <= 5; $i++): 
                                    if ($i <= $fullStars): ?>
                                        <svg class="w-4 h-4 text-orange-400 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                        <svg class="w-4 h-4 text-orange-400" viewBox="0 0 20 20">
                                            <defs>
                                                <linearGradient id="half-<?= $product['id'] ?>">
                                                    <stop offset="50%" stop-color="#fb923c"/>
                                                    <stop offset="50%" stop-color="#e5e7eb"/>
                                                </linearGradient>
                                            </defs>
                                            <path fill="url(#half-<?= $product['id'] ?>)" d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    <?php endif;
                                endfor; ?>
                            </div>
                            <span class="text-sm text-gray-500 ml-2">(<?= $reviews ?>)</span>
                        </div>
                        
                        <!-- Price -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-xl font-bold text-gray-900">
                                    ₹<?= number_format($product['price'], 2) ?>
                                </span>
                                <?php if ($product['original_price'] > $product['price']): ?>
                                <span class="text-sm text-gray-400 line-through">
                                    ₹<?= number_format($product['original_price'], 2) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Product Actions -->
                        <div class="flex items-center space-x-2">
                            <!-- Add to Cart Button -->
                            <?php if ($product['stock'] > 0): ?>
                            <button onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)" 
                                    class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-medium py-3 px-4 rounded-xl transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add to Cart
                            </button>
                            <?php else: ?>
                            <button disabled class="flex-1 bg-gray-300 text-gray-500 font-medium py-3 px-4 rounded-xl cursor-not-allowed">
                                Out of Stock
                            </button>
                            <?php endif; ?>
                            
                            <!-- Wishlist Button -->
                            <?php if ($currentUser): ?>
                            <?php 
                            $isInWishlist = in_array($product['id'], $userWishlistItems);
                            $heartClass = $isInWishlist ? 'text-red-500' : 'text-gray-400';
                            $heartFill = $isInWishlist ? 'fill="currentColor"' : 'fill="none"';
                            $buttonTitle = $isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist';
                            ?>
                            <button onclick="event.stopPropagation(); toggleWishlist(<?= $product['id'] ?>)" 
                                    id="wishlist-btn-<?= $product['id'] ?>"
                                    class="p-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors wishlist-btn"
                                    data-product-id="<?= $product['id'] ?>"
                                    title="<?= $buttonTitle ?>">
                                <svg class="w-5 h-5 <?= $heartClass ?>" <?= $heartFill ?> stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200 py-16">
        <div class="w-full px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Brand Section -->
                <div class="md:col-span-1">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-emerald-700 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 tracking-wide">LOCAL PANTRY</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Reconnecting communities with their food. Every product has a story, a family, and a farm behind it. Shop local, live better.
                    </p>
                </div>

                <!-- Categories Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-6">CATEGORIES</h4>
                    <ul class="space-y-4">
                        <li><a href="?category=5" class="text-gray-600 hover:text-gray-900 transition-colors">Marketplace</a></li>
                        <li><a href="?category=6" class="text-gray-600 hover:text-gray-900 transition-colors">Artisan Bakery</a></li>
                        <li><a href="?category=5" class="text-gray-600 hover:text-gray-900 transition-colors">Local Harvest</a></li>
                        <li><a href="?category=4" class="text-gray-600 hover:text-gray-900 transition-colors">Small-Batch Home</a></li>
                    </ul>
                </div>

                <!-- Resources Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-6">RESOURCES</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Vendor Policy</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Eco Initiatives</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Return Safety</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-gray-200 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-gray-400 mb-4 md:mb-0">
                        © <?= date('Y') ?> THE LOCAL PANTRY. CRAFTING NEIGHBORHOOD CONNECTIONS.
                    </p>
                    <div class="flex items-center space-x-6 text-sm text-gray-400">
                        <a href="#" class="hover:text-gray-600 transition-colors">Privacy</a>
                        <a href="#" class="hover:text-gray-600 transition-colors">Terms</a>
                        <a href="#" class="hover:text-gray-600 transition-colors">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cart Notification -->
    <div id="cart-notification" class="fixed bottom-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full transition-transform duration-300 z-50">
        <p>Item added to cart!</p>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Your Basket</h2>
                <button onclick="closeCartSidebar()" class="p-2 hover:bg-gray-100 rounded-full">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Cart Content -->
            <div id="cart-sidebar-content" class="flex-1 flex flex-col">
                <!-- Empty State -->
                <div id="empty-cart" class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Your basket is empty</h3>
                    <p class="text-gray-500 mb-6">Looks like you haven't added anything yet.</p>
                    <button onclick="closeCartSidebar()" class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-3 px-6 rounded-xl transition-colors duration-200">
                        Start Shopping
                    </button>
                </div>
                
                <!-- Cart Items (will be populated by JavaScript) -->
                <div id="cart-items" class="flex-1 overflow-y-auto p-6 hidden">
                    <!-- Items will be inserted here -->
                </div>
                
                <!-- Cart Footer -->
                <div id="cart-footer" class="border-t border-gray-200 p-6 hidden">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-semibold">Total:</span>
                        <span id="cart-total" class="text-xl font-bold text-emerald-700">₹0.00</span>
                    </div>
                    <button onclick="window.location.href='cart.php'" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-6 rounded-xl transition-colors duration-200">
                        View Cart & Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar Overlay -->
    <div id="cart-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeCartSidebar()"></div>

    <script src="assets/js/main.js"></script>
</body>
</html>