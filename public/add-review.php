<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Review.php';
require_once __DIR__ . '/../src/models/Product.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$review = new Review();
$product = new Product();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? '';
    
    if ($productId && $rating) {
        try {
            $reviewId = $review->addReview($productId, $currentUser['id'], $rating, $comment);
            if ($reviewId) {
                $_SESSION['success_message'] = 'Review submitted successfully!';
                header('Location: my-orders.php');
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'Please provide a rating';
    }
}

// Get product details if product_id is provided
$productId = $_GET['product_id'] ?? null;
$productDetails = null;
if ($productId) {
    $productDetails = $product->getById($productId);
    if (!$productDetails) {
        header('Location: index.php');
        exit;
    }
}

// Get reviewable products
$reviewableProducts = $review->getReviewableProducts($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="index.php" class="text-xl font-bold text-gray-800 tracking-wide">LOCAL PANTRY</a>
                </div>

                <!-- Navigation - Centered -->
                <nav class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Shop</a>
                    <a href="my-orders.php" class="text-emerald-700 font-medium border-b-2 border-emerald-700 pb-1">My Orders</a>
                    <a href="wishlist.php" class="text-gray-500 hover:text-gray-700 font-medium">Wishlist</a>
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <!-- User Dropdown -->
                    <div class="relative" id="userDropdown">
                        <button onclick="toggleUserDropdown()" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 font-medium focus:outline-none">
                            <span>Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                            <svg class="w-4 h-4 transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Profile</span>
                                </div>
                            </a>
                            <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Logout</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full px-6 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Write a Review</h1>
                <p class="text-gray-600 mt-2">Share your experience with other customers</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($productDetails): ?>
            <!-- Review Form for Specific Product -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Product Info -->
                <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-gray-200">
                    <img src="<?= ImageUpload::getImageUrl($productDetails['image'], true) ?>" 
                         alt="<?= htmlspecialchars($productDetails['name']) ?>" 
                         class="w-16 h-16 object-cover rounded-lg">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($productDetails['name']) ?></h2>
                        <p class="text-gray-600">₹<?= number_format($productDetails['price'], 2) ?></p>
                    </div>
                </div>

                <!-- Review Form -->
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="product_id" value="<?= $productDetails['id'] ?>">
                    
                    <!-- Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                        <div class="flex items-center space-x-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" onclick="setRating(<?= $i ?>)" 
                                    class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition-colors focus:outline-none">
                                ★
                            </button>
                            <?php endfor; ?>
                            <span id="rating-text" class="ml-4 text-gray-600"></span>
                        </div>
                        <input type="hidden" name="rating" id="rating-input" required>
                    </div>
                    
                    <!-- Comment -->
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                        <textarea name="comment" id="comment" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                  placeholder="Share your thoughts about this product..."></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex items-center space-x-4">
                        <button type="submit" 
                                class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                            Submit Review
                        </button>
                        <a href="my-orders.php" class="text-gray-600 hover:text-gray-800">Cancel</a>
                    </div>
                </form>
            </div>
            
            <?php else: ?>
            <!-- Product Selection -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Select a Product to Review</h2>
                
                <?php if (empty($reviewableProducts)): ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Products to Review</h3>
                    <p class="text-gray-600 mb-4">You can only review products from delivered orders.</p>
                    <a href="index.php" class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                        Start Shopping
                    </a>
                </div>
                <?php else: ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($reviewableProducts as $item): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-4">
                            <img src="<?= ImageUpload::getImageUrl($item['product_image'], true) ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                 class="w-16 h-16 object-cover rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($item['product_name']) ?></h3>
                                <p class="text-sm text-gray-600">Order #<?= htmlspecialchars($item['order_number']) ?></p>
                                <p class="text-xs text-gray-500"><?= date('M d, Y', strtotime($item['order_date'])) ?></p>
                            </div>
                            <div>
                                <?php if ($item['already_reviewed']): ?>
                                <span class="text-sm text-green-600 font-medium">✓ Reviewed</span>
                                <?php else: ?>
                                <a href="add-review.php?product_id=<?= $item['product_id'] ?>" 
                                   class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                                    Write Review
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="w-full px-6">
            <div class="text-center">
                <p class="text-gray-300">&copy; 2026 <?= SITE_NAME ?>. All rights reserved.</p>
                <p class="text-gray-400 text-sm mt-2">Fresh local products delivered to your doorstep</p>
            </div>
        </div>
    </footer>

    <script>
        let selectedRating = 0;
        const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        
        function setRating(rating) {
            selectedRating = rating;
            document.getElementById('rating-input').value = rating;
            document.getElementById('rating-text').textContent = ratingTexts[rating];
            
            // Update star display
            const stars = document.querySelectorAll('.star-btn');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });
        }
        
        // User dropdown functionality
        function toggleUserDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            const arrow = document.getElementById('dropdownArrow');
            
            dropdown.classList.toggle('hidden');
            arrow.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (!dropdown.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
                document.getElementById('dropdownArrow').style.transform = 'rotate(0deg)';
            }
        });
    </script>
</body>
</html>