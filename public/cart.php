<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/cart.php';
require_once __DIR__ . '/../src/image-upload.php';
require_once __DIR__ . '/../src/models/Product.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $productId = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if ($productId <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
                exit;
            }
            
            // Check if product exists and has stock
            $product = Product::getById($productId);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            
            if ($product['stock'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                exit;
            }
            
            addToCart($productId, $quantity);
            echo json_encode([
                'success' => true, 
                'message' => 'Product added to cart',
                'cart_count' => getCartCount()
            ]);
            exit;
            
        case 'update':
            $productId = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 0);
            
            updateCart($productId, $quantity);
            echo json_encode([
                'success' => true,
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal()
            ]);
            exit;
            
        case 'remove':
            $productId = intval($_POST['product_id'] ?? 0);
            removeFromCart($productId);
            echo json_encode([
                'success' => true,
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal()
            ]);
            exit;
            
        case 'count':
            echo json_encode([
                'success' => true,
                'count' => getCartCount()
            ]);
            exit;
            
        case 'get_items':
            $items = getCartItems();
            $total = getCartTotal();
            echo json_encode([
                'success' => true,
                'items' => $items,
                'total' => $total
            ]);
            exit;
            
        case 'clear':
            clearCart();
            echo json_encode(['success' => true, 'message' => 'Cart cleared']);
            exit;
    }
}

// Display cart page
$cartItems = getCartItems();
$cartTotal = getCartTotal();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
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
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-emerald rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl">LP</span>
                    </div>
                    <a href="index.php" class="text-2xl font-bold text-gradient-emerald"><?= SITE_NAME ?></a>
                </div>
                
                <nav class="flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-emerald-600">← Back to Shop</a>
                    <?php if ($currentUser): ?>
                        <span class="text-gray-700">Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                        <a href="logout.php" class="text-gray-700 hover:text-emerald-600">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-emerald-600">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🛒</div>
            <h2 class="text-2xl font-bold text-gray-600 mb-4">Your cart is empty</h2>
            <p class="text-gray-500 mb-8">Add some delicious products to get started!</p>
            <a href="index.php" class="btn-primary text-white px-6 py-3 rounded-lg">Continue Shopping</a>
        </div>
        <?php else: ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-6">Cart Items (<?= count($cartItems) ?>)</h2>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="flex items-center border-b border-gray-200 py-4 last:border-b-0">
                        <img src="<?= ImageUpload::getImageUrl($item['image'], true) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="w-20 h-20 object-cover rounded-lg">
                        
                        <div class="flex-1 ml-4">
                            <h3 class="font-bold text-lg"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-gray-600 text-sm"><?= htmlspecialchars(substr($item['description'], 0, 100)) ?>...</p>
                            <div class="flex items-center space-x-2">
                                <span class="text-2xl font-bold text-emerald-600">₹<?= number_format($item['price'], 2) ?> each</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <!-- Quantity Controls -->
                            <div class="flex items-center border rounded-lg">
                                <button onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['cart_quantity'] - 1 ?>)" 
                                        class="px-3 py-1 hover:bg-gray-100" 
                                        <?= $item['cart_quantity'] <= 1 ? 'disabled' : '' ?>>-</button>
                                <span class="px-3 py-1 border-x"><?= $item['cart_quantity'] ?></span>
                                <button onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['cart_quantity'] + 1 ?>)" 
                                        class="px-3 py-1 hover:bg-gray-100"
                                        <?= $item['cart_quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                            </div>
                            
                            <!-- Subtotal -->
                            <div class="text-right min-w-[80px]">
                                <p class="font-bold text-lg">₹<?= number_format($item['subtotal'], 2) ?></p>
                            </div>
                            
                            <!-- Remove Button -->
                            <button onclick="removeFromCart(<?= $item['id'] ?>)" 
                                    class="text-red-500 hover:text-red-700 p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-6 pt-6 border-t">
                        <button onclick="clearCart()" class="text-red-500 hover:text-red-700">Clear Cart</button>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-6">Order Summary</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>₹<?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping:</span>
                            <span class="text-green-600">Free</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span class="cart-total">₹<?= number_format($cartTotal, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($currentUser): ?>
                    <a href="checkout.php" class="btn-primary w-full text-center text-white py-3 rounded-lg block mb-4">
                        Proceed to Checkout
                    </a>
                    <?php else: ?>
                    <div class="text-center mb-4">
                        <p class="text-gray-600 mb-4">Please login to checkout</p>
                        <a href="login.php?redirect=cart.php" class="btn-primary text-white px-6 py-2 rounded-lg">Login</a>
                    </div>
                    <?php endif; ?>
                    
                    <a href="index.php" class="btn-secondary w-full text-center text-white py-2 rounded-lg block">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) return;
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function removeFromCart(productId) {
            if (confirm('Remove this item from cart?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
        
        function clearCart() {
            if (confirm('Clear all items from cart?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>