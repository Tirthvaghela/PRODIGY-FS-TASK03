<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/models/Product.php';
require_once __DIR__ . '/../../src/models/Category.php';
require_once __DIR__ . '/../../src/image-upload.php';

requireAdmin();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $originalPrice = floatval($_POST['original_price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $categoryId = intval($_POST['category_id'] ?? 0);
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            if (empty($name) || $price <= 0 || $categoryId <= 0) {
                $error = 'Please fill in all required fields with valid values';
            } else {
                $imageFilename = null;
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadResult = ImageUpload::uploadProductImage($_FILES['image']);
                    if ($uploadResult['success']) {
                        $imageFilename = $uploadResult['filename'];
                    } else {
                        $error = 'Image upload failed: ' . $uploadResult['error'];
                    }
                }
                
                if (!$error) {
                    $result = Product::create([
                        'name' => $name,
                        'description' => $description,
                        'price' => $price,
                        'original_price' => $originalPrice > 0 ? $originalPrice : $price,
                        'stock' => $stock,
                        'category_id' => $categoryId,
                        'featured' => $featured,
                        'image' => $imageFilename
                    ]);
                    
                    if ($result['success']) {
                        $message = 'Product added successfully!';
                    } else {
                        $error = $result['error'] ?? 'Failed to add product';
                        // Clean up uploaded image if product creation failed
                        if ($imageFilename) {
                            ImageUpload::deleteProductImage($imageFilename);
                        }
                    }
                }
            }
            break;
            
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $originalPrice = floatval($_POST['original_price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $categoryId = intval($_POST['category_id'] ?? 0);
            $featured = isset($_POST['featured']) ? 1 : 0;
            $status = $_POST['status'] ?? 'active';
            
            if ($id <= 0 || empty($name) || $price <= 0 || $categoryId <= 0) {
                $error = 'Please fill in all required fields with valid values';
            } else {
                $updateData = [
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'original_price' => $originalPrice > 0 ? $originalPrice : $price,
                    'stock' => $stock,
                    'category_id' => $categoryId,
                    'featured' => $featured,
                    'status' => $status
                ];
                
                // Handle image upload for update
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadResult = ImageUpload::uploadProductImage($_FILES['image']);
                    if ($uploadResult['success']) {
                        // Get current product to delete old image
                        $currentProduct = Product::getById($id);
                        if ($currentProduct && $currentProduct['image']) {
                            ImageUpload::deleteProductImage($currentProduct['image']);
                        }
                        
                        $updateData['image'] = $uploadResult['filename'];
                    } else {
                        $error = 'Image upload failed: ' . $uploadResult['error'];
                    }
                }
                
                if (!$error) {
                    $result = Product::update($id, $updateData);
                    
                    if ($result) {
                        $message = 'Product updated successfully!';
                    } else {
                        $error = 'Failed to update product';
                        // Clean up uploaded image if product update failed
                        if (isset($updateData['image'])) {
                            ImageUpload::deleteProductImage($updateData['image']);
                        }
                    }
                }
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = Product::delete($id);
                if ($result) {
                    $message = 'Product deleted successfully!';
                } else {
                    $error = 'Failed to delete product';
                }
            }
            break;
    }
}

// Get products and categories
$products = Product::getAll(['include_inactive' => true]);
$categories = Category::getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - <?= SITE_NAME ?></title>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-wide">ADMIN - PRODUCTS</h1>
                </div>

                <!-- Navigation - Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Dashboard</a>
                    <a href="../index.php" class="text-gray-500 hover:text-gray-700 font-medium">View Store</a>
                    <a href="../logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="w-full px-6 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Product Management</h1>
                    <p class="text-gray-600 mt-2">Manage your store's product inventory</p>
                </div>
                <button onclick="openAddModal()" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                    Add New Product
                </button>
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

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Product</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Category</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Price</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Stock</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Featured</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="<?= ImageUpload::getImageUrl($product['image'], true) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="w-12 h-12 object-cover rounded-lg mr-4">
                                        <div>
                                            <p class="font-medium text-gray-800"><?= htmlspecialchars($product['name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-800">₹<?= number_format($product['price'], 2) ?></p>
                                        <?php if ($product['original_price'] > $product['price']): ?>
                                        <p class="text-sm text-gray-400 line-through">₹<?= number_format($product['original_price'], 2) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium <?= $product['stock'] > 10 ? 'text-green-600' : ($product['stock'] > 0 ? 'text-yellow-600' : 'text-red-600') ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['featured']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Featured
                                    </span>
                                    <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)" 
                                                class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                            Edit
                                        </button>
                                        <button onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')" 
                                                class="text-red-600 hover:text-red-800 font-medium text-sm">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Product</h2>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="productForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" id="productId" name="id">
                        <input type="hidden" id="formAction" name="action" value="add">
                        
                        <!-- Product Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" id="productName" name="name" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                   placeholder="Enter product name">
                        </div>
                        
                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select id="productCategory" name="category_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="productDescription" name="description" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                      placeholder="Enter product description"></textarea>
                        </div>
                        
                        <!-- Product Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                            
                            <!-- Current Image Display (for edit mode) -->
                            <div id="currentImageSection" class="hidden mb-4">
                                <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                                <div class="flex items-center space-x-4">
                                    <img id="currentImage" src="" alt="Current product image" class="w-20 h-20 object-cover rounded-lg border">
                                    <div class="text-sm text-gray-500">
                                        <p>Upload a new image to replace the current one</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" id="productImage" name="image" accept="image/*"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <p class="text-sm text-gray-500 mt-1">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</p>
                                </div>
                                <div id="imagePreview" class="hidden">
                                    <img id="previewImg" src="" alt="Preview" class="w-20 h-20 object-cover rounded-lg border">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Price Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₹) *</label>
                                <input type="number" id="productPrice" name="price" step="0.01" min="0" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Original Price (₹)</label>
                                <input type="number" id="productOriginalPrice" name="original_price" step="0.01" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                        
                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity *</label>
                            <input type="number" id="productStock" name="stock" min="0" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                   placeholder="0">
                        </div>
                        
                        <!-- Status (only for edit) -->
                        <div id="statusField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="productStatus" name="status"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Featured -->
                        <div class="flex items-center">
                            <input type="checkbox" id="productFeatured" name="featured" value="1"
                                   class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <label for="productFeatured" class="ml-2 block text-sm text-gray-700">
                                Mark as featured product
                            </label>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button type="button" onclick="closeModal()" 
                                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-medium">
                                <span id="submitText">Add Product</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Delete Product</h3>
                    <p class="text-gray-600 mb-6">Are you sure you want to delete "<span id="deleteProductName"></span>"? This action cannot be undone.</p>
                    
                    <form id="deleteForm" method="POST" class="flex justify-end space-x-4">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteProductId" name="id">
                        
                        <button type="button" onclick="closeDeleteModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('submitText').textContent = 'Add Product';
            document.getElementById('statusField').classList.add('hidden');
            document.getElementById('currentImageSection').classList.add('hidden');
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('productForm').reset();
            document.getElementById('productModal').classList.remove('hidden');
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'update';
            document.getElementById('submitText').textContent = 'Update Product';
            document.getElementById('statusField').classList.remove('hidden');
            
            // Show current image if exists
            if (product.image) {
                document.getElementById('currentImageSection').classList.remove('hidden');
                document.getElementById('currentImage').src = '../uploads/products/' + product.image;
            } else {
                document.getElementById('currentImageSection').classList.add('hidden');
            }
            
            // Hide preview initially
            document.getElementById('imagePreview').classList.add('hidden');
            
            // Fill form with product data
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productOriginalPrice').value = product.original_price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productStatus').value = product.status;
            document.getElementById('productFeatured').checked = product.featured == 1;
            
            // Clear the file input
            document.getElementById('productImage').value = '';
            
            document.getElementById('productModal').classList.remove('hidden');
        }
        
        function deleteProduct(id, name) {
            document.getElementById('deleteProductId').value = id;
            document.getElementById('deleteProductName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        // Close modals when clicking outside
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
        
        // Image preview functionality
        document.getElementById('productImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        });
    </script>
</body>
</html>