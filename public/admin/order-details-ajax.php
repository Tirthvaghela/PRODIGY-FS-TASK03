<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/models/Order.php';
require_once __DIR__ . '/../../src/image-upload.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID provided']);
    exit;
}

$orderId = intval($_GET['id']);

try {
    // Get order details
    $order = Order::getById($orderId);
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $orderItems = Order::getItems($orderId);
    
    // Parse shipping address (it might be JSON or plain text)
    $shippingAddress = null;
    if ($order['shipping_address']) {
        $decoded = json_decode($order['shipping_address'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $shippingAddress = $decoded;
        } else {
            // If it's not JSON, treat as plain text
            $shippingAddress = ['address' => $order['shipping_address']];
        }
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'order' => $order,
        'items' => $orderItems,
        'shipping_address' => $shippingAddress
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    error_log('Order details AJAX error: ' . $e->getMessage());
}
?>