<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Wishlist.php';
require_once __DIR__ . '/../src/image-upload.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$wishlist = new Wishlist();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'remove':
            $productId = $_POST['product_id'] ?? null;
            if ($productId) {
                $result = $wishlist->removeFromWishlist($currentUser['id'], $productId);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
            }
            exit;
            
        case 'move_to_cart':
            $productId = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 1;
            if ($productId) {
                $result = $wishlist->moveToCart($currentUser['id'], $productId, $quantity);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
            }
            exit;
            
        case 'clear_all':
            $result = $wishlist->clearWishlist($currentUser['id']);
            echo json_encode($result);
            exit;
    }
}

// Get user's wishlist
$wishlistItems = $wishlist->getUserWishlist($currentUser['id']);
$wishlistCount = count($wishlistItems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                    <a href="index.php" class="text-xl font-bold text-gray-800 tracking-wide">LOCAL PANTRY</a>
                </div>

                <!-- Navigation - Centered -->
                <nav class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Shop</a>
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <a href="wishlist.php" class="text-emerald-700 font-medium border-b-2 border-emerald-700 pb-1">Wishlist</a>
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
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
                    
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full px-6 py-8">
        <div class="w-full">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
                    <p class="text-gray-600 mt-2"><?= $wishlistCount ?> item<?= $wishlistCount !== 1 ? 's' : '' ?> saved for later</p>
                </div>
                
                <?php if ($wishlistCount > 0): ?>
                <button onclick="clearWishlist()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Clear All
                </button>
                <?php endif; ?>
            </div>

            <?php if (empty($wishlistItems)): ?>
            <!-- Empty Wishlist -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Your wishlist is empty</h2>
                <p class="text-gray-600 mb-8">Save items you love to your wishlist and shop them later!</p>
                <a href="index.php" class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                    Start Shopping
                </a>
            </div>
            <?php else: ?>
            
            <!-- Wishlist Items -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                <?php foreach ($wishlistItems as $item): ?>
                <div class="product-card bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden" id="wishlist-item-<?= $item['product_id'] ?>">
                    <!-- Product Image -->
                    <div class="relative">
                        <img src="<?= ImageUpload::getImageUrl($item['image'], true) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="w-full h-48 object-cover">
                        
                        <!-- Remove Button -->
                        <button onclick="removeFromWishlist(<?= $item['product_id'] ?>)" 
                                class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        
                        <!-- Stock Status -->
                        <?php if ($item['stock_quantity'] <= 0): ?>
                        <div class="absolute bottom-2 left-2 bg-red-600 text-white px-2 py-1 rounded text-xs font-medium">
                            Out of Stock
                        </div>
                        <?php elseif ($item['stock_quantity'] <= 5): ?>
                        <div class="absolute bottom-2 left-2 bg-orange-600 text-white px-2 py-1 rounded text-xs font-medium">
                            Only <?= $item['stock_quantity'] ?> left
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Details -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2"><?= htmlspecialchars($item['name']) ?></h3>
                        
                        <!-- Category -->
                        <?php if ($item['category_name']): ?>
                        <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($item['category_name']) ?></p>
                        <?php endif; ?>
                        
                        <!-- Rating -->
                        <?php if ($item['average_rating'] > 0): ?>
                        <div class="flex items-center mb-2">
                            <div class="flex items-center">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-4 h-4 <?= $i <= round($item['average_rating']) ? 'text-yellow-400' : 'text-gray-300' ?>" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="text-sm text-gray-600 ml-2">(<?= $item['review_count'] ?>)</span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Price -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xl font-bold text-emerald-600">₹<?= number_format($item['price'], 2) ?></span>
                            <span class="text-sm text-gray-500">Added <?= $item['added_date'] ?></span>
                        </div>
                        
                        <!-- Actions -->
                        <div class="space-y-2">
                            <?php if ($item['stock_quantity'] > 0): ?>
                            <button onclick="moveToCart(<?= $item['product_id'] ?>)" 
                                    class="w-full bg-emerald-600 text-white py-2 px-4 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                                Move to Cart
                            </button>
                            <?php else: ?>
                            <button disabled class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-lg cursor-not-allowed font-medium">
                                Out of Stock
                            </button>
                            <?php endif; ?>
                            
                            <button onclick="removeFromWishlist(<?= $item['product_id'] ?>)" 
                                    class="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Continue Shopping -->
            <div class="text-center mt-12">
                <a href="index.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                    Continue Shopping
                </a>
            </div>
            
            <?php endif; ?>
        </div>
    </main>

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
                        <li><a href="index.php?category=5" class="text-gray-600 hover:text-gray-900 transition-colors">Marketplace</a></li>
                        <li><a href="index.php?category=6" class="text-gray-600 hover:text-gray-900 transition-colors">Artisan Bakery</a></li>
                        <li><a href="index.php?category=5" class="text-gray-600 hover:text-gray-900 transition-colors">Local Harvest</a></li>
                        <li><a href="index.php?category=4" class="text-gray-600 hover:text-gray-900 transition-colors">Small-Batch Home</a></li>
                    </ul>
                </div>

                <!-- Resources Section -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-6">RESOURCES</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Vendor Policy</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Eco Initiatives</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">Return Safety</a></li>
                        <li><a href="support.php" class="text-gray-600 hover:text-gray-900 transition-colors">Contact Us</a></li>
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

    <script>
        // Remove item from wishlist
        function removeFromWishlist(productId) {
            if (!confirm('Remove this item from your wishlist?')) return;
            
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const item = document.getElementById(`wishlist-item-${productId}`);
                    if (item) {
                        item.remove();
                    }
                    
                    // Check if wishlist is empty
                    const remainingItems = document.querySelectorAll('[id^="wishlist-item-"]');
                    if (remainingItems.length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to remove item', 'error');
            });
        }
        
        // Move item to cart
        function moveToCart(productId) {
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=move_to_cart&product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const item = document.getElementById(`wishlist-item-${productId}`);
                    if (item) {
                        item.remove();
                    }
                    
                    // Check if wishlist is empty
                    const remainingItems = document.querySelectorAll('[id^="wishlist-item-"]');
                    if (remainingItems.length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to move item to cart', 'error');
            });
        }
        
        // Clear entire wishlist
        function clearWishlist() {
            if (!confirm('Are you sure you want to clear your entire wishlist?')) return;
            
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear_all'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to clear wishlist', 'error');
            });
        }
        
        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // User dropdown functionality
        function toggleUserDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            const arrow = document.getElementById('dropdownArrow');
            
            dropdown.classList.toggle('hidden');
            arrow.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (!dropdown.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
                document.getElementById('dropdownArrow').style.transform = 'rotate(0deg)';
            }
        });
    </script>
</body>
</html>