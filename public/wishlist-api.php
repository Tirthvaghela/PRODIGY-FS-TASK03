<?php
// Prevent any output before JSON
ob_start();

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/Wishlist.php';

// Clear any output that might have occurred
ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

// Disable error display for API
ini_set('display_errors', 0);
error_reporting(0);

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to use wishlist']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $currentUser = getCurrentUser();
    $wishlist = new Wishlist();

    $action = $_POST['action'] ?? '';
    $productId = $_POST['product_id'] ?? null;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    switch ($action) {
        case 'toggle':
            // Check if product is in wishlist
            $isInWishlist = $wishlist->isInWishlist($currentUser['id'], $productId);
            
            if ($isInWishlist) {
                // Remove from wishlist
                $result = $wishlist->removeFromWishlist($currentUser['id'], $productId);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'action' => 'removed',
                        'message' => 'Removed from wishlist'
                    ]);
                } else {
                    echo json_encode($result);
                }
            } else {
                // Add to wishlist
                $result = $wishlist->addToWishlist($currentUser['id'], $productId);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'action' => 'added',
                        'message' => 'Added to wishlist'
                    ]);
                } else {
                    echo json_encode($result);
                }
            }
            break;
            
        case 'check':
            // Check if product is in wishlist
            $isInWishlist = $wishlist->isInWishlist($currentUser['id'], $productId);
            echo json_encode([
                'success' => true,
                'in_wishlist' => $isInWishlist
            ]);
            break;
            
        case 'count':
            // Get wishlist count
            $count = $wishlist->getWishlistCount($currentUser['id']);
            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    // Log error but return clean JSON
    error_log('Wishlist API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>