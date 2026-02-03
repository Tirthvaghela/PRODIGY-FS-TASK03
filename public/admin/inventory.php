<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/models/Product.php';
require_once __DIR__ . '/../../src/models/Category.php';
require_once __DIR__ . '/../../src/image-upload.php';

requireAdmin();

$message = '';
$error = '';

// Handle bulk stock updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'bulk_update_stock') {
        $updates = $_POST['stock_updates'] ?? [];
        $successCount = 0;
        
        foreach ($updates as $productId => $newStock) {
            $productId = intval($productId);
            $newStock = intval($newStock);
            
            if ($productId > 0 && $newStock >= 0) {
                if (Product::update($productId, ['stock' => $newStock])) {
                    $successCount++;
                }
            }
        }
        
        if ($successCount > 0) {
            $message = "Successfully updated stock for {$successCount} products!";
        } else {
            $error = "No stock updates were made.";
        }
    }
}

// Get inventory data
$lowStockProducts = Product::getLowStock(10); // Products with stock <= 10
$outOfStockProducts = Product::getOutOfStock();
$allProducts = Product::getAll(['include_inactive' => true]);
$categories = Category::getAll();

// Calculate inventory stats
$totalProducts = count($allProducts);
$lowStockCount = count($lowStockProducts);
$outOfStockCount = count($outOfStockProducts);
$totalInventoryValue = array_sum(array_map(function($p) { return $p['price'] * $p['stock']; }, $allProducts));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-wide">ADMIN - INVENTORY</h1>
                </div>

                <!-- Navigation - Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Dashboard</a>
                    <a href="products.php" class="text-gray-500 hover:text-gray-700 font-medium">Products</a>
                    <a href="orders.php" class="text-gray-500 hover:text-gray-700 font-medium">Orders</a>
                    <a href="../index.php" class="text-gray-500 hover:text-gray-700 font-medium">View Store</a>
                    <a href="../logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="w-full px-6 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Inventory Management</h1>
                <p class="text-gray-600 mt-2">Monitor stock levels and manage inventory efficiently</p>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Inventory Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Products</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalProducts ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Low Stock</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $lowStockCount ?></p>
                            <p class="text-xs text-yellow-600 mt-1">≤ 10 items</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $outOfStockCount ?></p>
                            <p class="text-xs text-red-600 mt-1">0 items</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Inventory Value</p>
                            <p class="text-2xl font-bold text-gray-900">₹<?= number_format($totalInventoryValue, 0) ?></p>
                            <p class="text-xs text-green-600 mt-1">Total worth</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Low Stock Alert -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Low Stock Alert
                        </h2>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?= $lowStockCount ?> items
                        </span>
                    </div>
                    
                    <?php if (empty($lowStockProducts)): ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-2">✅</div>
                        <p class="text-gray-500">All products are well stocked!</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <?php foreach ($lowStockProducts as $product): ?>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="flex items-center">
                                <img src="<?= ImageUpload::getImageUrl($product['image'], true) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="w-10 h-10 object-cover rounded-lg mr-3">
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($product['category_name']) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-yellow-700"><?= $product['stock'] ?> left</p>
                                <a href="products.php" class="text-xs text-blue-600 hover:text-blue-800">Restock →</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Out of Stock Alert -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Out of Stock
                        </h2>
                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?= $outOfStockCount ?> items
                        </span>
                    </div>
                    
                    <?php if (empty($outOfStockProducts)): ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-2">🎉</div>
                        <p class="text-gray-500">No products are out of stock!</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <?php foreach ($outOfStockProducts as $product): ?>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-center">
                                <img src="<?= ImageUpload::getImageUrl($product['image'], true) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="w-10 h-10 object-cover rounded-lg mr-3">
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($product['category_name']) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-red-700">0 stock</p>
                                <a href="products.php" class="text-xs text-blue-600 hover:text-blue-800">Restock →</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stock Update -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Quick Stock Update</h2>
                    <button onclick="toggleBulkUpdate()" class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-2 px-4 rounded-lg">
                        Bulk Update Stock
                    </button>
                </div>

                <!-- Bulk Update Form (Hidden by default) -->
                <div id="bulkUpdateForm" class="hidden mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <form method="POST">
                        <input type="hidden" name="action" value="bulk_update_stock">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            <?php foreach (array_slice($allProducts, 0, 12) as $product): ?>
                            <div class="flex items-center space-x-3 p-3 bg-white rounded-lg">
                                <img src="<?= ImageUpload::getImageUrl($product['image'], true) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="w-8 h-8 object-cover rounded">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="text-xs text-gray-500">Current: <?= $product['stock'] ?></p>
                                </div>
                                <input type="number" name="stock_updates[<?= $product['id'] ?>]" 
                                       value="<?= $product['stock'] ?>" min="0"
                                       class="w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="toggleBulkUpdate()" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg">
                                Update Stock
                            </button>
                        </div>
                    </form>
                </div>

                <!-- All Products Stock Overview -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Product</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Category</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Price</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Stock</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Value</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($allProducts as $product): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="<?= ImageUpload::getImageUrl($product['image'], true) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="w-12 h-12 object-cover rounded-lg mr-4">
                                        <div>
                                            <p class="font-medium text-gray-800"><?= htmlspecialchars($product['name']) ?></p>
                                            <p class="text-sm text-gray-500">ID: <?= $product['id'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">₹<?= number_format($product['price'], 2) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium <?= $product['stock'] == 0 ? 'text-red-600' : ($product['stock'] <= 10 ? 'text-yellow-600' : 'text-green-600') ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800">₹<?= number_format($product['price'] * $product['stock'], 2) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['stock'] == 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                    <?php elseif ($product['stock'] <= 10): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Low Stock
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        In Stock
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBulkUpdate() {
            const form = document.getElementById('bulkUpdateForm');
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>