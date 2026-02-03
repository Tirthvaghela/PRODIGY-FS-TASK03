<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Order.php';

// Require login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    header('Location: index.php');
    exit;
}

// Get order details
$order = Order::getByOrderNumber($orderNumber);
if (!$order || $order['user_id'] != $currentUser['id']) {
    header('Location: index.php');
    exit;
}

$orderItems = Order::getItems($order['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - <?= SITE_NAME ?></title>
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
                    <?php if (isLoggedIn()): ?>
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <?php endif; ?>
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

    <!-- Order Confirmation Content -->
    <div class="w-full px-6 py-12">
        <div class="max-w-4xl mx-auto">
            
            <!-- Success Message -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Order Confirmed!</h1>
                <p class="text-xl text-gray-600 mb-2">Thank you for your order, <?= htmlspecialchars($currentUser['name']) ?>!</p>
                <p class="text-gray-500 mb-2">Your order has been received and is being prepared.</p>
                <p class="text-sm text-emerald-600">📧 A confirmation email has been sent to <?= htmlspecialchars($currentUser['email']) ?></p>
            </div>

            <!-- Order Details Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Order Info -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Information</h2>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500">Order Number</span>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['order_number']) ?></p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Order Date</span>
                                <p class="font-semibold text-gray-800"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Payment Method</span>
                                <p class="font-semibold text-gray-800"><?= $order['payment_method'] === 'COD' ? 'Cash on Delivery' : $order['payment_method'] ?></p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Status</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Info -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Shipping Address</h2>
                        <div class="text-gray-600">
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($currentUser['name']) ?></p>
                            <p><?= htmlspecialchars($currentUser['email']) ?></p>
                            <?php if ($currentUser['phone']): ?>
                            <p><?= htmlspecialchars($currentUser['phone']) ?></p>
                            <?php endif; ?>
                            <div class="mt-2">
                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="border-t border-gray-200 pt-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Order Items</h2>
                    <div class="space-y-4">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="flex items-center space-x-4 py-4 border-b border-gray-100 last:border-b-0">
                            <img src="<?= $item['product_image'] ? UPLOAD_URL . $item['product_image'] : 'assets/images/placeholder.jpg' ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                 class="w-16 h-16 object-cover rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></h3>
                                <p class="text-gray-500 text-sm">Quantity: <?= $item['quantity'] ?></p>
                                <p class="text-gray-500 text-sm">₹<?= number_format($item['price'], 2) ?> each</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">₹<?= number_format($item['subtotal'], 2) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Total -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-800">Total Amount</span>
                            <span class="text-2xl font-bold text-emerald-700">₹<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-emerald-50 rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-emerald-800 mb-4">What happens next?</h2>
                <div class="space-y-3 text-emerald-700">
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-emerald-200 rounded-full flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-emerald-800 text-sm font-bold">1</span>
                        </div>
                        <p>We'll prepare your order with care and attention to quality.</p>
                    </div>
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-emerald-200 rounded-full flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-emerald-800 text-sm font-bold">2</span>
                        </div>
                        <p>You'll receive updates about your order status via email.</p>
                    </div>
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-emerald-200 rounded-full flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-emerald-800 text-sm font-bold">3</span>
                        </div>
                        <p>Your order will be delivered to your specified address.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="track-order.php" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200 text-center">
                    Track Your Order
                </a>
                <a href="index.php" class="bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-8 rounded-lg border border-gray-300 transition-colors duration-200 text-center">
                    Continue Shopping
                </a>
            </div>

            <!-- Support -->
            <div class="text-center mt-8">
                <p class="text-gray-500 mb-2">Need help with your order?</p>
                <a href="support.php" class="text-emerald-600 hover:text-emerald-700 font-medium">Contact Support</a>
            </div>
        </div>
    </div>
</body>
</html>