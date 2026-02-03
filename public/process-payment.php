<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/payment.php';
require_once __DIR__ . '/../src/models/Order.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/email.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$requiredFields = ['order_id', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

try {
    // Get order details
    $order = new Order();
    $orderData = $order->getById($input['order_id']);
    
    if (!$orderData) {
        throw new Exception('Order not found');
    }
    
    // Verify order belongs to current user
    $currentUser = getCurrentUser();
    if ($orderData['user_id'] != $currentUser['id']) {
        throw new Exception('Unauthorized access to order');
    }
    
    // Process payment
    $paymentData = [
        'razorpay_order_id' => $input['razorpay_order_id'],
        'razorpay_payment_id' => $input['razorpay_payment_id'],
        'razorpay_signature' => $input['razorpay_signature']
    ];
    
    $result = PaymentService::processPayment($input['order_id'], $paymentData);
    
    if ($result['success']) {
        // Get updated order data
        $updatedOrder = $order->getById($input['order_id']);
        
        // Get customer details
        $user = new User();
        $customer = $user->getById($currentUser['id']);
        
        // Get order items
        $orderItems = $order->getOrderItems($input['order_id']);
        
        // Send order confirmation email
        EmailService::sendOrderConfirmation($updatedOrder, $orderItems, $customer);
        
        // Log successful payment
        error_log("Payment processed successfully for order {$input['order_id']} by user {$currentUser['id']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment_id' => $result['payment_id'],
            'order_number' => $orderData['order_number']
        ]);
    } else {
        throw new Exception($result['message']);
    }
    
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>