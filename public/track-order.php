<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Order.php';

$currentUser = getCurrentUser();
$orderDetails = null;
$error = '';

// Handle order tracking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = trim($_POST['order_id']);
    
    if (!empty($orderId)) {
        // Try to find the order by order number
        $order = Order::getByOrderNumber($orderId);
        
        if ($order) {
            // Get order items
            $orderItems = Order::getItems($order['id']);
            
            // Map status to display information
            $statusInfo = [
                'pending' => [
                    'title' => 'Order Confirmed',
                    'description' => 'Your order has been received and is being prepared.',
                    'icon' => 'check',
                    'color' => 'blue'
                ],
                'processing' => [
                    'title' => 'Being Prepared',
                    'description' => 'Your items are being carefully selected and packed.',
                    'icon' => 'clock',
                    'color' => 'yellow'
                ],
                'shipped' => [
                    'title' => 'Out for Delivery',
                    'description' => 'Your order is on its way to you.',
                    'icon' => 'truck',
                    'color' => 'emerald'
                ],
                'delivered' => [
                    'title' => 'Delivered',
                    'description' => 'Your order has been successfully delivered.',
                    'icon' => 'check-circle',
                    'color' => 'green'
                ],
                'cancelled' => [
                    'title' => 'Cancelled',
                    'description' => 'This order has been cancelled.',
                    'icon' => 'x-circle',
                    'color' => 'red'
                ]
            ];
            
            $currentStatus = $statusInfo[$order['status']] ?? $statusInfo['pending'];
            
            // For demo purposes, add some tracking details for shipped orders
            $trackingDetails = null;
            if ($order['status'] === 'shipped') {
                $trackingDetails = [
                    'driver' => 'Marcus',
                    'distance' => '0.8',
                    'eta' => '2:45 PM',
                    'dispatched_time' => date('g:i A', strtotime($order['updated_at'])),
                    'location' => 'Springfield local hub'
                ];
            }
            
            $orderDetails = [
                'order' => $order,
                'items' => $orderItems,
                'status_info' => $currentStatus,
                'tracking' => $trackingDetails
            ];
        } else {
            $error = 'Order not found. Please check your order ID and try again.';
        }
    } else {
        $error = 'Please enter a valid order ID';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - <?= SITE_NAME ?></title>
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
                    <?php if ($currentUser): ?>
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <a href="wishlist.php" class="text-gray-500 hover:text-gray-700 font-medium">Wishlist</a>
                    <?php endif; ?>
                    <a href="track-order.php" class="text-emerald-700 font-medium border-b-2 border-emerald-700 pb-1">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- Right Side -->
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
                    <?php else: ?>
                        <a href="login.php" class="text-emerald-700 font-medium">SIGN IN</a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Track Order Content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            
            <?php if (!$orderDetails): ?>
            <!-- Order Tracking Form -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Order Status</h2>
                <p class="text-gray-600">Tracking your local goodness in real-time.</p>
            </div>
            
            <form method="POST" class="space-y-6">
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <!-- Order ID Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">ORDER IDENTIFIER</label>
                    <input type="text" name="order_id" required
                           class="block w-full px-4 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400"
                           placeholder="LP-XXXX-XXXX"
                           value="<?= htmlspecialchars($_GET['order'] ?? $_POST['order_id'] ?? '') ?>">
                </div>
                
                <!-- Track Button -->
                <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-4 px-4 rounded-lg transition-colors duration-200">
                    Locate Order
                </button>
            </form>
            
            <?php else: ?>
            <!-- Order Status Display -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-<?= $orderDetails['status_info']['color'] ?>-100 rounded-full mb-6">
                    <?php if ($orderDetails['status_info']['icon'] === 'check'): ?>
                    <svg class="w-10 h-10 text-<?= $orderDetails['status_info']['color'] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <?php elseif ($orderDetails['status_info']['icon'] === 'truck'): ?>
                    <svg class="w-10 h-10 text-<?= $orderDetails['status_info']['color'] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php elseif ($orderDetails['status_info']['icon'] === 'clock'): ?>
                    <svg class="w-10 h-10 text-<?= $orderDetails['status_info']['color'] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php else: ?>
                    <svg class="w-10 h-10 text-<?= $orderDetails['status_info']['color'] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php endif; ?>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Order #<?= htmlspecialchars($orderDetails['order']['order_number']) ?></h2>
                <p class="text-gray-600">Tracking your local goodness in real-time.</p>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Order Details</h3>
                        <p class="text-sm text-gray-500">Placed on <?= date('M j, Y', strtotime($orderDetails['order']['created_at'])) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-800">₹<?= number_format($orderDetails['order']['total_amount'], 2) ?></p>
                        <p class="text-sm text-gray-500"><?= count($orderDetails['items']) ?> item(s)</p>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="space-y-3">
                    <?php foreach ($orderDetails['items'] as $item): ?>
                    <div class="flex items-center space-x-3 py-2 border-b border-gray-100 last:border-b-0">
                        <img src="<?= $item['product_image'] ? UPLOAD_URL . $item['product_image'] : 'assets/images/placeholder.jpg' ?>" 
                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                             class="w-12 h-12 object-cover rounded-lg">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></p>
                            <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></p>
                        </div>
                        <p class="font-medium text-gray-800">₹<?= number_format($item['subtotal'], 2) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Current Status -->
            <div class="bg-<?= $orderDetails['status_info']['color'] ?>-50 rounded-lg p-6 mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-<?= $orderDetails['status_info']['color'] ?>-600 rounded-full flex items-center justify-center mr-4">
                        <?php if ($orderDetails['status_info']['icon'] === 'check'): ?>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php elseif ($orderDetails['status_info']['icon'] === 'truck'): ?>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php else: ?>
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800"><?= $orderDetails['status_info']['title'] ?></h3>
                        <p class="text-<?= $orderDetails['status_info']['color'] ?>-600 text-sm"><?= $orderDetails['status_info']['description'] ?></p>
                        <?php if ($orderDetails['tracking']): ?>
                        <p class="text-<?= $orderDetails['status_info']['color'] ?>-600 text-sm">Driver: <?= htmlspecialchars($orderDetails['tracking']['driver']) ?> (nearby)</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($orderDetails['tracking']): ?>
            <!-- Progress Timeline -->
            <div class="space-y-4 mb-8">
                <!-- Almost there -->
                <div class="flex items-start">
                    <div class="w-4 h-4 bg-emerald-600 rounded-full mt-1 mr-4 flex-shrink-0"></div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Almost there!</h4>
                        <p class="text-gray-600 text-sm">Van is currently <?= $orderDetails['tracking']['distance'] ?> miles away from your location.</p>
                        <p class="text-emerald-600 text-sm font-medium"><?= $orderDetails['tracking']['eta'] ?></p>
                    </div>
                </div>

                <!-- Dispatched -->
                <div class="flex items-start">
                    <div class="w-4 h-4 bg-emerald-600 rounded-full mt-1 mr-4 flex-shrink-0"></div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Basket Dispatched</h4>
                        <p class="text-gray-600 text-sm">Order left the <?= $orderDetails['tracking']['location'] ?>.</p>
                        <p class="text-emerald-600 text-sm font-medium"><?= $orderDetails['tracking']['dispatched_time'] ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Back to Tracking Button -->
            <button onclick="window.location.reload()" class="w-full text-gray-500 hover:text-gray-700 font-medium py-2">
                Track Another Order
            </button>
            <?php endif; ?>
        </div>
    </div>

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