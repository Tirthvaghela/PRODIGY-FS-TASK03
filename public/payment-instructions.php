<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check if there are payment instructions
if (!isset($_SESSION['payment_instructions'])) {
    header('Location: index.php');
    exit;
}

$currentUser = getCurrentUser();
$paymentInfo = $_SESSION['payment_instructions'];

// Clear the session data
unset($_SESSION['payment_instructions']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Instructions - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100">
        <div class="w-full px-6">
            <div class="relative flex items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-wide"><?= SITE_NAME ?></h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full px-6 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
                <p class="text-gray-600">Order #<?= htmlspecialchars($paymentInfo['order_number']) ?></p>
            </div>

            <!-- Payment Instructions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">💳 Payment Instructions</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold text-blue-800">Amount to Pay: ₹<?= number_format($paymentInfo['total_amount'], 2) ?></span>
                    </div>
                    <p class="text-blue-700 text-sm">Please complete payment within 24 hours to confirm your order.</p>
                </div>

                <?php if ($paymentInfo['payment_method'] === 'BANK_TRANSFER'): ?>
                <!-- Bank Transfer Instructions -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Payment Options
                    </h3>

                    <!-- UPI Payment -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">🔸 UPI Payment (Recommended)</h4>
                        <div class="bg-gray-50 p-3 rounded font-mono text-sm">
                            <strong>UPI ID:</strong> localpantry@upi<br>
                            <strong>Name:</strong> Local Pantry Store
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Use any UPI app: PhonePe, Paytm, Google Pay, etc.</p>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">🔸 Bank Transfer</h4>
                        <div class="bg-gray-50 p-3 rounded font-mono text-sm">
                            <strong>Account Name:</strong> Local Pantry Store<br>
                            <strong>Account Number:</strong> 1234567890<br>
                            <strong>IFSC Code:</strong> SBIN0001234<br>
                            <strong>Bank:</strong> State Bank of India
                        </div>
                    </div>

                    <!-- Payment Confirmation -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-medium text-yellow-800 mb-2">📝 After Payment</h4>
                        <ol class="text-sm text-yellow-700 space-y-1">
                            <li>1. Take a screenshot of payment confirmation</li>
                            <li>2. WhatsApp us at <strong>+91 8735028305</strong></li>
                            <li>3. Send screenshot with Order #<?= htmlspecialchars($paymentInfo['order_number']) ?></li>
                            <li>4. We'll confirm and ship your order within 24 hours</li>
                        </ol>
                    </div>
                </div>

                <?php elseif ($paymentInfo['payment_method'] === 'WHATSAPP_PAY'): ?>
                <!-- WhatsApp Payment Instructions -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.700"/>
                        </svg>
                        WhatsApp Payment Assistance
                    </h3>

                    <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                        <h4 class="font-medium text-green-800 mb-3">📱 Contact us on WhatsApp for payment</h4>
                        
                        <div class="space-y-3">
                            <a href="https://wa.me/918735028305?text=Hi! I want to pay for Order #<?= urlencode($paymentInfo['order_number']) ?>. Amount: ₹<?= number_format($paymentInfo['total_amount'], 2) ?>" 
                               target="_blank"
                               class="block bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium">
                                💬 Message us on WhatsApp
                            </a>
                            
                            <div class="text-center">
                                <p class="text-sm text-green-700">Or manually message: <strong>+91 8735028305</strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">💬 What to message:</h4>
                        <div class="bg-white p-3 rounded border text-sm">
                            "Hi! I want to pay for Order #<?= htmlspecialchars($paymentInfo['order_number']) ?>. Amount: ₹<?= number_format($paymentInfo['total_amount'], 2) ?>"
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Our team will guide you through UPI/bank transfer payment.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Order Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-medium">#<?= htmlspecialchars($paymentInfo['order_number']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-medium">₹<?= number_format($paymentInfo['total_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="font-medium">
                            <?= $paymentInfo['payment_method'] === 'BANK_TRANSFER' ? 'Bank Transfer/UPI' : 'WhatsApp Payment' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center space-x-4">
                <a href="my-orders.php" class="bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                    View My Orders
                </a>
                <a href="index.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors font-medium">
                    Continue Shopping
                </a>
            </div>
        </div>
    </main>
</body>
</html>