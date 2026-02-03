<?php
require_once __DIR__ . '/../db.php';

class Wishlist {
    private $db;
    
    public function __construct() {
        $this->db = getPDO();
    }
    
    /**
     * Add product to wishlist
     */
    public function addToWishlist($userId, $productId) {
        try {
            // Check if already in wishlist
            if ($this->isInWishlist($userId, $productId)) {
                return ['success' => false, 'message' => 'Product already in wishlist'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO wishlist (user_id, product_id) 
                VALUES (?, ?)
            ");
            
            $result = $stmt->execute([$userId, $productId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Product added to wishlist'];
            }
            
            return ['success' => false, 'message' => 'Failed to add product to wishlist'];
        } catch (Exception $e) {
            error_log("Error adding to wishlist: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }
    
    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist($userId, $productId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM wishlist 
                WHERE user_id = ? AND product_id = ?
            ");
            
            $result = $stmt->execute([$userId, $productId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Product removed from wishlist'];
            }
            
            return ['success' => false, 'message' => 'Failed to remove product from wishlist'];
        } catch (Exception $e) {
            error_log("Error removing from wishlist: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }
    
    /**
     * Check if product is in user's wishlist
     */
    public function isInWishlist($userId, $productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM wishlist 
                WHERE user_id = ? AND product_id = ?
            ");
            
            $stmt->execute([$userId, $productId]);
            return $stmt->fetch() ? true : false;
        } catch (Exception $e) {
            error_log("Error checking wishlist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's wishlist with product details
     */
    public function getUserWishlist($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    w.*,
                    p.name,
                    p.description,
                    p.price,
                    p.image,
                    p.stock as stock_quantity,
                    COALESCE(p.average_rating, 0) as average_rating,
                    COALESCE(p.review_count, 0) as review_count,
                    c.name as category_name,
                    DATE_FORMAT(w.added_at, '%M %d, %Y') as added_date
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE w.user_id = ? AND p.status = 'active'
                ORDER BY w.added_at DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user wishlist: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get wishlist count for user
     */
    public function getWishlistCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ? AND p.status = 'active'
            ");
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting wishlist count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Move wishlist item to cart
     */
    public function moveToCart($userId, $productId, $quantity = 1) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Add to cart
            require_once __DIR__ . '/../cart.php';
            $cartResult = addToCart($productId, $quantity);
            
            if ($cartResult['success']) {
                // Remove from wishlist
                $this->removeFromWishlist($userId, $productId);
                
                $this->db->commit();
                return ['success' => true, 'message' => 'Product moved to cart'];
            } else {
                $this->db->rollback();
                return ['success' => false, 'message' => $cartResult['message']];
            }
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error moving to cart: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to move product to cart'];
        }
    }
    
    /**
     * Clear user's entire wishlist
     */
    public function clearWishlist($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Wishlist cleared'];
            }
            
            return ['success' => false, 'message' => 'Failed to clear wishlist'];
        } catch (Exception $e) {
            error_log("Error clearing wishlist: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }
    
    /**
     * Get wishlist items that are on sale or low stock
     */
    public function getWishlistAlerts($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    w.*,
                    p.name,
                    p.price,
                    p.stock_quantity,
                    p.image,
                    CASE 
                        WHEN p.stock_quantity <= 5 THEN 'low_stock'
                        WHEN p.price < (SELECT AVG(price) FROM products WHERE category_id = p.category_id) * 0.8 THEN 'on_sale'
                        ELSE 'normal'
                    END as alert_type
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ? AND p.status = 'active'
                AND (p.stock_quantity <= 5 OR p.price < (SELECT AVG(price) FROM products WHERE category_id = p.category_id) * 0.8)
                ORDER BY w.added_at DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting wishlist alerts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get most wishlisted products (for analytics)
     */
    public function getMostWishlistedProducts($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.image,
                    COUNT(w.id) as wishlist_count
                FROM products p
                JOIN wishlist w ON p.id = w.product_id
                WHERE p.status = 'active'
                GROUP BY p.id, p.name, p.price, p.image
                ORDER BY wishlist_count DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting most wishlisted products: " . $e->getMessage());
            return [];
        }
    }
}
?>