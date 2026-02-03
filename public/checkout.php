<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/cart.php';
require_once __DIR__ . '/../src/models/Order.php';
require_once __DIR__ . '/../src/models/Address.php';
require_once __DIR__ . '/../src/email.php';
require_once __DIR__ . '/../src/image-upload.php';
require_once __DIR__ . '/../src/payment.php';

// Require login for checkout
if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$currentUser = getCurrentUser();
$cartItems = getCartItems();
$cartTotal = getCartTotal();
$addresses = [];
$defaultAddress = null;

// Try to get addresses, handle if table doesn't exist
try {
    $addresses = Address::getByUserId($currentUser['id']);
    $defaultAddress = Address::getDefault($currentUser['id']);
} catch (Exception $e) {
    // If address table doesn't exist, just use empty arrays
    // The checkout will fall back to manual address entry
    $addresses = [];
    $defaultAddress = null;
}

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'COD';
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($shippingAddress)) {
        $error = 'Please provide a shipping address';
    } elseif (empty($phone)) {
        $error = 'Please provide a phone number';
    } else {
        // Create the order
        $orderData = [
            'user_id' => $currentUser['id'],
            'cart_items' => $cartItems,
            'shipping_address' => $shippingAddress,
            'payment_method' => $paymentMethod,
            'phone' => $phone,
            'notes' => $notes
        ];
        
        $result = Order::create($currentUser['id'], $cartItems, $shippingAddress, $paymentMethod);
        
        if ($result['success']) {
            // Get the created order details
            $order = Order::getByOrderNumber($result['order_number']);
            $orderItems = Order::getItems($result['order_id']);
            
            // Handle payment method
            if ($paymentMethod === 'razorpay') {
                // Create Razorpay order
                $razorpayOrder = PaymentService::createRazorpayOrder(
                    $cartTotal, 
                    $result['order_number'], 
                    $currentUser
                );
                
                // Update order with Razorpay order ID
                $orderModel = new Order();
                $orderModel->update($result['order_id'], [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'payment_status' => 'pending'
                ]);
                
                // Store order data in session for payment page
                $_SESSION['pending_order'] = [
                    'order_id' => $result['order_id'],
                    'order_number' => $result['order_number'],
                    'total_amount' => $cartTotal,
                    'razorpay_order_id' => $razorpayOrder['id']
                ];
                
                // Clear the cart
                clearCart();
                
                // Redirect to payment page
                header('Location: payment.php');
                exit;
            } elseif ($paymentMethod === 'BANK_TRANSFER' || $paymentMethod === 'WHATSAPP_PAY') {
                // Store payment instructions in session
                $_SESSION['payment_instructions'] = [
                    'order_id' => $result['order_id'],
                    'order_number' => $result['order_number'],
                    'total_amount' => $cartTotal,
                    'payment_method' => $paymentMethod
                ];
                
                // Clear the cart
                clearCart();
                
                // Redirect to payment instructions page
                header('Location: payment-instructions.php');
                exit;
            } else {
                // COD - just redirect to confirmation
                // Clear the cart
                clearCart();
                
                // Redirect to order confirmation
                header('Location: order-confirmation.php?order=' . $result['order_number']);
                exit;
            }
        } else {
            $error = $result['error'] ?? 'Failed to create order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= SITE_NAME ?></title>
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
                    <a href="profile.php" class="text-gray-500 hover:text-gray-700 font-medium">Profile</a>
                    <span class="text-gray-700 font-medium">Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                    <a href="logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Checkout Content -->
    <div class="w-full px-6 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-8">
                <a href="index.php" class="hover:text-gray-700">Shop</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="cart.php" class="hover:text-gray-700">Cart</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-emerald-600 font-medium">Checkout</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h1>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Checkout Form -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Shipping Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-800">Shipping Information</h2>
                            <?php if (!empty($addresses)): ?>
                            <div class="flex items-center text-sm text-emerald-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?= count($addresses) ?> saved address<?= count($addresses) > 1 ? 'es' : '' ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Address Selection -->
                        <?php if (!empty($addresses)): ?>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Select Delivery Address</label>
                            <div class="space-y-3">
                                <?php foreach ($addresses as $index => $address): ?>
                                <div class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-emerald-500 transition-colors duration-200 address-option" 
                                     onclick="selectAddress(<?= $index ?>)" 
                                     data-address-id="<?= $address['id'] ?>">
                                    <div class="flex items-start">
                                        <input type="radio" name="selected_address" value="<?= $address['id'] ?>" 
                                               id="address_<?= $index ?>" 
                                               class="mt-1 w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300"
                                               <?= ($address['is_default'] || $index === 0) ? 'checked' : '' ?>>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <?= htmlspecialchars($address['label']) ?>
                                                </span>
                                                <?php if ($address['is_default']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    Default
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="font-medium text-gray-800"><?= htmlspecialchars($address['name']) ?></p>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($address['phone']) ?></p>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars(Address::getFormattedAddress($address)) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <!-- Add New Address Option -->
                                <div class="border border-dashed border-gray-300 rounded-lg p-4 cursor-pointer hover:border-emerald-500 transition-colors duration-200 address-option" 
                                     onclick="selectNewAddress()">
                                    <div class="flex items-center">
                                        <input type="radio" name="selected_address" value="new" id="address_new" 
                                               class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                        <div class="ml-3 flex items-center text-emerald-600">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            <span class="font-medium">Add New Address</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Manual Address Form (shown when no saved addresses or "Add New" is selected) -->
                        <div id="manualAddressForm" class="<?= !empty($addresses) ? 'hidden' : '' ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name (pre-filled) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="recipient_name" value="<?= htmlspecialchars($currentUser['name']) ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                           required>
                                </div>
                                
                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                    <input type="tel" name="phone" required
                                           value="<?= htmlspecialchars($currentUser['phone'] ?? $_POST['phone'] ?? '') ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                           placeholder="+91 98765 43210">
                                </div>
                            </div>
                            
                            <!-- Shipping Address -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address *</label>
                                <textarea name="shipping_address" required rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                          placeholder="Enter your complete address including street, city, state, and postal code"><?= htmlspecialchars($currentUser['address'] ?? $_POST['shipping_address'] ?? '') ?></textarea>
                            </div>
                            
                            <!-- Save Address Option -->
                            <div class="mt-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="save_address" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Save this address for future orders</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Order Notes -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order Notes (Optional)</label>
                            <textarea name="notes" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                      placeholder="Any special instructions for delivery..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <!-- Cash on Delivery -->
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <input type="radio" id="cod" name="payment_method" value="COD" checked
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <label for="cod" class="ml-3 flex-1">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-800">Cash on Delivery</p>
                                            <p class="text-sm text-gray-500">Pay when your order arrives</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Razorpay Online Payment -->
                            <?php if (defined('PAYMENT_ENABLED') && PAYMENT_ENABLED): ?>
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" id="razorpay" name="payment_method" value="razorpay"
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <label for="razorpay" class="ml-3 flex-1 cursor-pointer">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">💳</span>
                                        <div>
                                            <p class="font-medium text-gray-800">Pay Online</p>
                                            <p class="text-sm text-gray-500">Credit/Debit Card, UPI, Net Banking - Powered by Razorpay</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <?php else: ?>
                            <!-- Bank Transfer/UPI Option -->
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <input type="radio" id="bank_transfer" name="payment_method" value="BANK_TRANSFER"
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <label for="bank_transfer" class="ml-3 flex-1 cursor-pointer">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-800">Bank Transfer / UPI</p>
                                            <p class="text-sm text-gray-500">Pay via UPI, PhonePe, Paytm, or Bank Transfer</p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- WhatsApp Payment Option -->
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <input type="radio" id="whatsapp_pay" name="payment_method" value="WHATSAPP_PAY"
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <label for="whatsapp_pay" class="ml-3 flex-1 cursor-pointer">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-green-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.700"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-800">WhatsApp Payment</p>
                                            <p class="text-sm text-gray-500">Message us on WhatsApp for payment assistance</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Order Summary</h2>
                        
                        <!-- Order Items -->
                        <div class="space-y-4 mb-6">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="flex items-center space-x-3">
                                <img src="<?= ImageUpload::getImageUrl($item['image'], true) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                     class="w-12 h-12 object-cover rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="text-gray-500 text-sm">Qty: <?= $item['cart_quantity'] ?></p>
                                </div>
                                <p class="font-medium text-gray-800">₹<?= number_format($item['subtotal'], 2) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal (<?= count($cartItems) ?> items)</span>
                                <span>₹<?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span class="text-emerald-600 font-medium">Free</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span>₹0.00</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between font-bold text-lg text-gray-800">
                                    <span>Total</span>
                                    <span>₹<?= number_format($cartTotal, 2) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Place Order Button -->
                        <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-4 px-6 rounded-lg transition-colors duration-200 mt-6">
                            Place Order
                        </button>
                        
                        <!-- Security Notice -->
                        <div class="mt-4 flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Your information is secure and protected</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectAddress(index) {
            // Check the radio button
            document.getElementById('address_' + index).checked = true;
            
            // Hide manual form
            document.getElementById('manualAddressForm').classList.add('hidden');
            
            // Update visual selection
            updateAddressSelection();
        }
        
        function selectNewAddress() {
            // Check the new address radio button
            document.getElementById('address_new').checked = true;
            
            // Show manual form
            document.getElementById('manualAddressForm').classList.remove('hidden');
            
            // Update visual selection
            updateAddressSelection();
        }
        
        function updateAddressSelection() {
            // Remove selection styling from all options
            document.querySelectorAll('.address-option').forEach(option => {
                option.classList.remove('border-emerald-500', 'bg-emerald-50');
                option.classList.add('border-gray-200');
            });
            
            // Add selection styling to selected option
            const selectedRadio = document.querySelector('input[name="selected_address"]:checked');
            if (selectedRadio) {
                const selectedOption = selectedRadio.closest('.address-option');
                if (selectedOption) {
                    selectedOption.classList.remove('border-gray-200');
                    selectedOption.classList.add('border-emerald-500', 'bg-emerald-50');
                }
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateAddressSelection();
            
            // Add click handlers for address options
            document.querySelectorAll('.address-option').forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        
                        if (radio.value === 'new') {
                            selectNewAddress();
                        } else {
                            document.getElementById('manualAddressForm').classList.add('hidden');
                            updateAddressSelection();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>