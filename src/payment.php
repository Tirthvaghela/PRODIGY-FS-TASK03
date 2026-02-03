<?php
require_once __DIR__ . '/config.php';

class PaymentService {
    
    private static $razorpayKeyId;
    private static $razorpayKeySecret;
    
    public static function init() {
        self::$razorpayKeyId = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : 'rzp_test_your_key_id';
        self::$razorpayKeySecret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : 'your_key_secret';
    }
    
    /**
     * Create Razorpay order for payment
     */
    public static function createRazorpayOrder($orderAmount, $orderNumber, $customerDetails) {
        self::init();
        
        $orderData = [
            'receipt' => $orderNumber,
            'amount' => $orderAmount * 100, // Amount in paise (₹1 = 100 paise)
            'currency' => 'INR',
            'notes' => [
                'customer_name' => $customerDetails['name'],
                'customer_email' => $customerDetails['email'],
                'order_number' => $orderNumber
            ]
        ];
        
        // In production, this would make actual API call to Razorpay
        // For demo, we'll simulate the response
        return [
            'id' => 'order_' . uniqid(),
            'entity' => 'order',
            'amount' => $orderAmount * 100,
            'amount_paid' => 0,
            'amount_due' => $orderAmount * 100,
            'currency' => 'INR',
            'receipt' => $orderNumber,
            'status' => 'created',
            'created_at' => time()
        ];
    }
    
    /**
     * Verify Razorpay payment signature
     */
    public static function verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
        self::init();
        
        $body = $razorpayOrderId . "|" . $razorpayPaymentId;
        $expectedSignature = hash_hmac('sha256', $body, self::$razorpayKeySecret);
        
        return hash_equals($expectedSignature, $razorpaySignature);
    }
    
    /**
     * Process payment and update order status
     */
    public static function processPayment($orderId, $paymentData) {
        try {
            // Verify payment signature
            $isValid = self::verifyPaymentSignature(
                $paymentData['razorpay_order_id'],
                $paymentData['razorpay_payment_id'],
                $paymentData['razorpay_signature']
            );
            
            if ($isValid) {
                // Update order status to paid
                require_once __DIR__ . '/models/Order.php';
                $order = new Order();
                
                $updateData = [
                    'payment_status' => 'paid',
                    'payment_id' => $paymentData['razorpay_payment_id'],
                    'payment_method' => 'razorpay',
                    'status' => 'processing'
                ];
                
                $result = $order->update($orderId, $updateData);
                
                if ($result) {
                    // Log successful payment
                    error_log("Payment successful for order ID: $orderId, Payment ID: " . $paymentData['razorpay_payment_id']);
                    
                    return [
                        'success' => true,
                        'message' => 'Payment processed successfully',
                        'payment_id' => $paymentData['razorpay_payment_id']
                    ];
                } else {
                    throw new Exception('Failed to update order status');
                }
            } else {
                throw new Exception('Invalid payment signature');
            }
            
        } catch (Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get payment methods available
     */
    public static function getPaymentMethods() {
        return [
            'razorpay' => [
                'name' => 'Credit/Debit Card, UPI, Net Banking',
                'description' => 'Pay securely with Razorpay',
                'icon' => '💳',
                'enabled' => true
            ],
            'cod' => [
                'name' => 'Cash on Delivery',
                'description' => 'Pay when your order is delivered',
                'icon' => '💵',
                'enabled' => true
            ]
        ];
    }
    
    /**
     * Generate Razorpay checkout script
     */
    public static function generateCheckoutScript($orderData, $customerData) {
        self::init();
        
        return "
        <script src='https://checkout.razorpay.com/v1/checkout.js'></script>
        <script>
        function initiateRazorpayPayment() {
            var options = {
                'key': '" . self::$razorpayKeyId . "',
                'amount': " . ($orderData['total_amount'] * 100) . ",
                'currency': 'INR',
                'name': '" . SITE_NAME . "',
                'description': 'Order #" . $orderData['order_number'] . "',
                'order_id': '" . $orderData['razorpay_order_id'] . "',
                'handler': function (response) {
                    // Send payment data to server for verification
                    fetch('process-payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: " . $orderData['id'] . ",
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'payment-success.php?order_id=" . $orderData['id'] . "';
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
                    'name': '" . $customerData['name'] . "',
                    'email': '" . $customerData['email'] . "',
                    'contact': '" . ($customerData['phone'] ?? '') . "'
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
        </script>
        ";
    }
}

// Payment status constants
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');
?>