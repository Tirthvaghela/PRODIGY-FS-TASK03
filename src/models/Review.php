<?php
require_once __DIR__ . '/../db.php';

class Review {
    private $db;
    
    public function __construct() {
        $this->db = getPDO();
    }
    
    /**
     * Add a new product review
     */
    public function addReview($productId, $userId, $rating, $comment) {
        try {
            // Check if user has already reviewed this product
            $checkStmt = $this->db->prepare("
                SELECT id FROM reviews 
                WHERE user_id = ? AND product_id = ?
            ");
            $checkStmt->execute([$userId, $productId]);
            
            if ($checkStmt->fetch()) {
                throw new Exception('You have already reviewed this product');
            }
            
            // Verify user purchased this product
            $verifyStmt = $this->db->prepare("
                SELECT oi.id FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.user_id = ? AND oi.product_id = ? AND o.status IN ('delivered', 'processing')
            ");
            $verifyStmt->execute([$userId, $productId]);
            $hasPurchased = $verifyStmt->fetch() ? true : false;
            
            if (!$hasPurchased) {
                throw new Exception('You can only review products you have purchased');
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO reviews (product_id, user_id, rating, comment)
                VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$productId, $userId, $rating, $comment]);
            
            if ($result) {
                // Update product review statistics
                $this->updateProductReviewStats($productId);
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error adding review: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get reviews for a product
     */
    public function getProductReviews($productId, $limit = 10, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    u.name as user_name,
                    DATE_FORMAT(r.created_at, '%M %d, %Y') as review_date
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$productId, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting product reviews: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get review statistics for a product
     */
    public function getProductReviewStats($productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews 
                WHERE product_id = ?
            ");
            
            $stmt->execute([$productId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting review stats: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user's review for a product
     */
    public function getUserProductReview($userId, $productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, DATE_FORMAT(r.created_at, '%M %d, %Y') as review_date
                FROM reviews r
                WHERE r.user_id = ? AND r.product_id = ?
                ORDER BY r.created_at DESC LIMIT 1
            ");
            
            $stmt->execute([$userId, $productId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user review: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update product review statistics
     */
    private function updateProductReviewStats($productId) {
        try {
            $stats = $this->getProductReviewStats($productId);
            
            $stmt = $this->db->prepare("
                UPDATE products 
                SET average_rating = ?, review_count = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                round($stats['average_rating'], 2),
                $stats['total_reviews'],
                $productId
            ]);
        } catch (Exception $e) {
            error_log("Error updating product review stats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get products user can review (delivered orders)
     */
    public function getReviewableProducts($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT
                    p.id as product_id,
                    p.name as product_name,
                    p.image as product_image,
                    o.id as order_id,
                    o.order_number,
                    o.created_at as order_date,
                    CASE WHEN r.id IS NOT NULL THEN 1 ELSE 0 END as already_reviewed
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN reviews r ON (r.user_id = ? AND r.product_id = p.id)
                WHERE o.user_id = ? AND o.status IN ('delivered', 'processing')
                ORDER BY o.created_at DESC, p.name ASC
            ");
            
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting reviewable products: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete a review
     */
    public function deleteReview($reviewId, $userId = null) {
        try {
            // Get product ID before deletion for stats update
            $productStmt = $this->db->prepare("SELECT product_id FROM reviews WHERE id = ?");
            $productStmt->execute([$reviewId]);
            $productId = $productStmt->fetchColumn();
            
            $sql = "DELETE FROM reviews WHERE id = ?";
            $params = [$reviewId];
            
            // If userId provided, ensure user owns the review
            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $productId) {
                $this->updateProductReviewStats($productId);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting review: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all reviews for admin management
     */
    public function getAllReviews($limit = 20, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    u.name as user_name,
                    u.email as user_email,
                    p.name as product_name,
                    DATE_FORMAT(r.created_at, '%M %d, %Y at %h:%i %p') as review_date
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN products p ON r.product_id = p.id
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting all reviews: " . $e->getMessage());
            return [];
        }
    }
}
?>