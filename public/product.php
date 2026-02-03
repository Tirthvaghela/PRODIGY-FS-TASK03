<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Product.php';

$productId = intval($_GET['id'] ?? 0);
$product = Product::getById($productId);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Generate random rating for demo
$rating = rand(40, 50) / 10;
$reviews = rand(45, 342);

// Handle AJAX request for modal
if (isset($_GET['modal']) && $_GET['modal'] === '1') {
    header('Content-Type: application/json');
    
    ob_start();
    ?>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" onclick="closeProductModal(event)">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
            <!-- Close Button -->
            <div class="absolute top-6 right-6 z-10">
                <button onclick="closeProductModal()" class="w-8 h-8 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 h-full">
                <!-- Product Image -->
                <div class="relative bg-gray-100">
                    <img src="<?= $product['image'] ? UPLOAD_URL . $product['image'] : 'assets/images/placeholder.jpg' ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="w-full h-full object-cover">
                </div>
                
                <!-- Product Details -->
                <div class="p-8 flex flex-col justify-between">
                    <!-- Category Badge -->
                    <div class="mb-4">
                        <span class="bg-emerald-700 text-white text-xs font-medium px-3 py-1 rounded-full uppercase tracking-wide">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </span>
                    </div>
                    
                    <!-- Product Name -->
                    <h2 class="text-3xl font-bold text-gray-800 mb-4 leading-tight">
                        <?= htmlspecialchars($product['name']) ?>
                    </h2>
                    
                    <!-- Rating -->
                    <div class="flex items-center mb-6">
                        <div class="flex items-center">
                            <?php 
                            $fullStars = floor($rating);
                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++): 
                                if ($i <= $fullStars): ?>
                                    <svg class="w-5 h-5 text-orange-400 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                    <svg class="w-5 h-5 text-orange-400" viewBox="0 0 20 20">
                                        <defs>
                                            <linearGradient id="half-modal-<?= $product['id'] ?>">
                                                <stop offset="50%" stop-color="#fb923c"/>
                                                <stop offset="50%" stop-color="#e5e7eb"/>
                                            </linearGradient>
                                        </defs>
                                        <path fill="url(#half-modal-<?= $product['id'] ?>)" d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-5 h-5 text-gray-300 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                <?php endif;
                            endfor; ?>
                        </div>
                        <span class="text-lg font-semibold text-gray-700 ml-3"><?= number_format($rating, 1) ?></span>
                        <span class="text-gray-500 ml-2">(<?= $reviews ?> reviews)</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-8">
                        <div class="flex items-center space-x-3">
                            <span class="text-4xl font-bold text-emerald-700">₹<?= number_format($product['price'], 2) ?></span>
                            <?php if ($product['original_price'] > $product['price']): ?>
                            <span class="text-xl text-gray-400 line-through">₹<?= number_format($product['original_price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- About Section -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">ABOUT THIS PRODUCT</h3>
                        <p class="text-gray-700 leading-relaxed text-lg">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>
                    </div>
                    
                    <!-- Features -->
                    <div class="flex items-center space-x-8 mb-8">
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">LOCAL PICKUP</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 00.707.293H15a2 2 0 012 2v2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V10m-9 4h4"></path>
                            </svg>
                            <span class="font-medium">ECO-DELIVERY</span>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <div class="mt-auto">
                        <?php if ($product['stock'] > 0): ?>
                        <button onclick="addToCart(<?= $product['id'] ?>); closeProductModal();" 
                                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-4 px-6 rounded-xl transition-colors duration-200 text-lg">
                            Add to Basket
                        </button>
                        <?php else: ?>
                        <button disabled class="w-full bg-gray-300 text-gray-500 font-semibold py-4 px-6 rounded-xl cursor-not-allowed text-lg">
                            Out of Stock
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $modalHtml = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $modalHtml,
        'product' => $product
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-gray-50">
    <!-- Regular product page content would go here -->
    <div class="container mx-auto px-4 py-8">
        <a href="index.php" class="text-emerald-600 hover:text-emerald-500 mb-4 inline-block">← Back to products</a>
        
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['description']) ?></p>
            <p class="text-2xl font-bold text-emerald-600">₹<?= number_format($product['price'], 2) ?></p>
        </div>
    </div>
</body>
</html>