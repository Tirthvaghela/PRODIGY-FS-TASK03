<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Order.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Get order details
$order = new Order();
$orderData = $order->getById($orderId);

if (!$orderData) {
    header('Location: index.php');
    exit;
}

// Verify order belongs to current user
$currentUser = getCurrentUser();
if ($orderData['user_id'] != $currentUser['id']) {
    header('Location: index.php');
    exit;
}

// Get order items
$orderItems = $order->getOrderItems($orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - <?= SITE_NAME ?></title>
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
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <span class="text-gray-700 font-medium">Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                    <a href="logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full px-6 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Success Message -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Successful! 🎉</h1>
                <p class="text-lg text-gray-600 mb-8">Thank you for your payment. Your order has been confirmed and is being processed.</p>
                
                <!-- Order Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Details</h2>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Order Number</p>
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($orderData['order_number']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?= ucfirst($orderData['payment_status']) ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Amount</p>
                            <p class="font-semibold text-gray-900">₹<?= number_format($orderData['total_amount'], 2) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="font-semibold text-gray-900"><?= $orderData['payment_method'] === 'razorpay' ? 'Online Payment' : 'Cash on Delivery' ?></p>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <h3 class="font-semibold text-gray-900 mb-3">Items Ordered</h3>
                    <div class="space-y-3">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($item['product_name']) ?></p>
                                <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></p>
                            </div>
                            <p class="font-semibold text-gray-900">₹<?= number_format($item['subtotal'], 2) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- What's Next -->
                <div class="bg-blue-50 rounded-lg p-6 mb-8 text-left">
                    <h3 class="font-semibold text-blue-900 mb-3">What happens next?</h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Order confirmation email sent to your registered email
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            We'll prepare your order with care and quality
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            You'll receive email updates about order status
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Order will be delivered to your specified address
                        </li>
                    </ul>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="my-orders.php" class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                        View My Orders
                    </a>
                    <a href="track-order.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                        Track This Order
                    </a>
                    <a href="index.php" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="w-full px-6">
            <div class="text-center">
                <p class="text-gray-300">&copy; 2026 <?= SITE_NAME ?>. All rights reserved.</p>
                <p class="text-gray-400 text-sm mt-2">Fresh local products delivered to your doorstep</p>
            </div>
        </div>
    </footer>
</body>
</html>