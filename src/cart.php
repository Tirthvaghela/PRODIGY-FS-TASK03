<?php
require_once __DIR__ . '/db.php';

function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function updateCart($productId, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function removeFromCart($productId) {
    unset($_SESSION['cart'][$productId]);
}

function getCartItems() {
    if (empty($_SESSION['cart'])) {
        return [];
    }
    
    $pdo = getPDO();
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    
    foreach ($products as &$product) {
        $product['cart_quantity'] = $_SESSION['cart'][$product['id']];
        $product['subtotal'] = $product['price'] * $product['cart_quantity'];
    }
    
    return $products;
}

function getCartTotal() {
    $items = getCartItems();
    return array_sum(array_column($items, 'subtotal'));
}

function getCartCount() {
    return !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
}

function clearCart() {
    unset($_SESSION['cart']);
}

function validateCartStock() {
    $items = getCartItems();
    $errors = [];
    
    foreach ($items as $item) {
        if ($item['cart_quantity'] > $item['stock']) {
            $errors[] = "Only {$item['stock']} units of {$item['name']} are available";
        }
    }
    
    return $errors;
}
?>