<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Order.php';

header('Content-Type: application/json');

// Require user to be logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Get order to verify ownership
$order = Order::getById($orderId);
if (!$order || $order['user_id'] != $userId) {
    echo json_encode(['success' => false, 'error' => 'Order not found or access denied']);
    exit;
}

// Check if order can be cancelled
if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'error' => 'Order cannot be cancelled. Current status: ' . $order['status']]);
    exit;
}

// Cancel the order
$result = Order::cancel($orderId, $userId);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to cancel order']);
}
?>