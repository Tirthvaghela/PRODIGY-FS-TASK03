<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Order.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/image-upload.php';

// Require user to be logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Get user's orders
$userOrders = Order::getByUser($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
    <header class="bg-white shadow-lg sticky top-0 z-40">
        <div class="w-full px-6">
            <div class="flex items-center justify-between py-4">
                <!-- Logo - Left Side -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-emerald-700 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl">LP</span>
                    </div>
                    <a href="index.php" class="text-2xl font-bold text-emerald-700">LOCAL PANTRY</a>
                </div>
                
                <!-- Navigation - Centered -->
                <nav class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Shop</a>
                    <a href="my-orders.php" class="text-emerald-700 font-medium border-b-2 border-emerald-700 pb-1">My Orders</a>
                    <a href="wishlist.php" class="text-gray-500 hover:text-gray-700 font-medium">Wishlist</a>
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
                                <?php if (isAdmin()): ?>
                                <a href="admin/" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        </svg>
                                        <span>Admin</span>
                                    </div>
                                </a>
                                <?php endif; ?>
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

    <!-- Main Content -->
    <div class="min-h-screen py-12 px-6">
        <div class="max-w-6xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">My Orders</h1>
                <p class="text-gray-600">View and track all your orders from Local Pantry</p>
            </div>

            <?php if (empty($userOrders)): ?>
            <!-- No Orders State -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Orders Yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="index.php" class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-6 py-3 rounded-lg inline-flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>Start Shopping</span>
                </a>
            </div>
            <?php else: ?>
            <!-- Orders List -->
            <div class="space-y-6">
                <?php foreach ($userOrders as $order): ?>
                <?php
                $orderItems = Order::getItems($order['id']);
                $orderDate = date('M j, Y g:i A', strtotime($order['created_at']));
                
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'delivered' => 'bg-green-100 text-green-800 border-green-200',
                    'cancelled' => 'bg-red-100 text-red-800 border-red-200'
                ];
                $statusColor = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                ?>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col md:flex-row md:items-center md:space-x-6">
                                <div>
                                    <p class="text-sm text-gray-600">Order Number</p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['order_number']) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Order Date</p>
                                    <p class="font-medium text-gray-800"><?= $orderDate ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Amount</p>
                                    <p class="font-semibold text-emerald-600">₹<?= number_format($order['total_amount'], 2) ?></p>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border <?= $statusColor ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <?php foreach ($orderItems as $item): ?>
                            <div class="flex items-center space-x-4">
                                <img src="<?= ImageUpload::getImageUrl($item['product_image'], true) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                     class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    <p class="text-sm text-gray-600">Quantity: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800">₹<?= number_format($item['subtotal'], 2) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Order Actions -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">Payment Method:</span>
                                <span class="text-sm font-medium text-gray-800">
                                    <?= $order['payment_method'] === 'COD' ? 'Cash on Delivery' : htmlspecialchars($order['payment_method']) ?>
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="track-order.php?order=<?= urlencode($order['order_number']) ?>" 
                                   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                                    Track Order
                                </a>
                                <?php if ($order['status'] === 'pending'): ?>
                                <button onclick="cancelOrder('<?= $order['id'] ?>', '<?= htmlspecialchars($order['order_number']) ?>')"
                                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                                    Cancel Order
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Shopping Cart</h2>
                <button onclick="closeCartSidebar()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Cart Content -->
            <div id="cart-sidebar-content" class="flex-1 flex flex-col">
                <!-- Empty State -->
                <div id="empty-cart" class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Your cart is empty</h3>
                    <p class="text-gray-500 text-sm mb-4">Add some products to get started</p>
                    <button onclick="closeCartSidebar()" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm">
                        Continue Shopping
                    </button>
                </div>
                
                <!-- Cart Items (populated by JavaScript) -->
                <div id="cart-items" class="flex-1 overflow-y-auto p-6 space-y-4 hidden">
                    <!-- Cart items will be inserted here -->
                </div>
                
                <!-- Cart Footer -->
                <div id="cart-footer" class="border-t border-gray-200 p-6 hidden">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-semibold text-gray-800">Total:</span>
                        <span id="cart-total" class="text-lg font-bold text-emerald-600">₹0.00</span>
                    </div>
                    <button onclick="window.location.href='checkout.php'" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-3 px-4 rounded-lg">
                        Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar Overlay -->
    <div id="cart-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeCartSidebar()"></div>

    <!-- Cancel Order Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cancel Order</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to cancel order <span id="cancelOrderNumber" class="font-medium"></span>? This action cannot be undone.</p>
                <div class="flex justify-end space-x-4">
                    <button onclick="closeCancelModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Keep Order
                    </button>
                    <button onclick="confirmCancel()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                        Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cancelOrderId = null;
        
        function cancelOrder(orderId, orderNumber) {
            cancelOrderId = orderId;
            document.getElementById('cancelOrderNumber').textContent = orderNumber;
            document.getElementById('cancelModal').classList.remove('hidden');
        }
        
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
            cancelOrderId = null;
        }
        
        function confirmCancel() {
            if (cancelOrderId) {
                // Send cancel request
                fetch('cancel-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: cancelOrderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel order: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error cancelling order');
                });
            }
            closeCancelModal();
        }
    </script>
    
    <!-- Include cart functionality -->
    <script src="assets/js/main.js"></script>
    
    <script>
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