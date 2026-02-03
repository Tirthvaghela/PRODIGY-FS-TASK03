<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/payment.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check if payments are enabled
if (!PAYMENT_ENABLED) {
    $_SESSION['error'] = 'Online payments are currently disabled. Please use Cash on Delivery.';
    header('Location: checkout.php');
    exit;
}

// Check if there's a pending order
if (!isset($_SESSION['pending_order'])) {
    header('Location: index.php');
    exit;
}

$currentUser = getCurrentUser();
$pendingOrder = $_SESSION['pending_order'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - <?= SITE_NAME ?></title>
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

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <span class="text-gray-700 font-medium">Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full px-6 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Payment Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Complete Your Payment</h1>
                    <p class="text-gray-600">Secure payment powered by Razorpay</p>
                </div>

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-semibold"><?= htmlspecialchars($pendingOrder['order_number']) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="text-2xl font-bold text-emerald-600">₹<?= number_format($pendingOrder['total_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Payment Button -->
                <div class="text-center">
                    <button onclick="initiateRazorpayPayment()" 
                            class="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors w-full mb-4">
                        💳 Pay ₹<?= number_format($pendingOrder['total_amount'], 2) ?> Now
                    </button>
                    
                    <p class="text-sm text-gray-500 mb-4">
                        Secure payment with 256-bit SSL encryption
                    </p>
                    
                    <div class="flex items-center justify-center space-x-4 text-sm text-gray-400">
                        <span>💳 Cards</span>
                        <span>📱 UPI</span>
                        <span>🏦 Net Banking</span>
                        <span>💰 Wallets</span>
                    </div>
                </div>

                <!-- Alternative -->
                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-gray-600 mb-4">Having trouble with payment?</p>
                    <a href="support.php" class="text-emerald-600 hover:text-emerald-700 font-medium">Contact Support</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Razorpay Integration -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    function initiateRazorpayPayment() {
        var options = {
            'key': '<?= RAZORPAY_KEY_ID ?>',
            'amount': <?= ($pendingOrder['total_amount'] * 100) ?>,
            'currency': 'INR',
            'name': '<?= SITE_NAME ?>',
            'description': 'Order #<?= $pendingOrder['order_number'] ?>',
            'order_id': '<?= $pendingOrder['razorpay_order_id'] ?>',
            'handler': function (response) {
                // Send payment data to server for verification
                fetch('process-payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: <?= $pendingOrder['order_id'] ?>,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'payment-success.php?order_id=<?= $pendingOrder['order_id'] ?>';
                    } else {
                        alert('Payment verification failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Payment processing error. Please contact support.');
                });
            },
            'prefill': {
                'name': '<?= htmlspecialchars($currentUser['name']) ?>',
                'email': '<?= htmlspecialchars($currentUser['email']) ?>',
                'contact': '<?= htmlspecialchars($currentUser['phone'] ?? '') ?>'
            },
            'theme': {
                'color': '#059669'
            },
            'modal': {
                'ondismiss': function() {
                    console.log('Payment cancelled by user');
                }
            }
        };
        
        var rzp = new Razorpay(options);
        rzp.open();
    }

    // Show loading state during payment
    function showLoading() {
        const button = document.querySelector('button[onclick="initiateRazorpayPayment()"]');
        button.innerHTML = '⏳ Processing Payment...';
        button.disabled = true;
    }

    // Hide loading state
    function hideLoading() {
        const button = document.querySelector('button[onclick="initiateRazorpayPayment()"]');
        button.innerHTML = '💳 Pay ₹<?= number_format($pendingOrder['total_amount'], 2) ?> Now';
        button.disabled = false;
    }

    // Override the payment function to add loading states
    const originalInitiatePayment = window.initiateRazorpayPayment;
    window.initiateRazorpayPayment = function() {
        showLoading();
        try {
            originalInitiatePayment();
        } catch (error) {
            hideLoading();
            alert('Payment initialization failed. Please try again.');
        }
    };
    </script>
</body>
</html>