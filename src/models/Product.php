<?php
require_once __DIR__ . '/../db.php';

class Product {
    
    public static function getAll($filters = []) {
        $pdo = getPDO();
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";
        $params = [];
        $conditions = [];
        
        // Only filter by active status if not in admin context
        if (!isset($filters['include_inactive'])) {
            $conditions[] = "p.status = 'active'";
        }
        
        if (!empty($filters['category'])) {
            $conditions[] = "p.category_id = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['min_price'])) {
            $conditions[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $conditions[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['featured'])) {
            $conditions[] = "p.featured = 1";
        }
        
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Sorting
        $orderBy = " ORDER BY ";
        switch ($filters['sort'] ?? 'newest') {
            case 'price_low':
                $orderBy .= "p.price ASC";
                break;
            case 'price_high':
                $orderBy .= "p.price DESC";
                break;
            case 'name':
                $orderBy .= "p.name ASC";
                break;
            case 'oldest':
                $orderBy .= "p.created_at ASC";
                break;
            default:
                $orderBy .= "p.created_at DESC";
        }
        
        $sql .= $orderBy;
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public static function getById($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function getFeatured($limit = 8) {
        return self::getAll(['featured' => true, 'limit' => $limit]);
    }
    
    public static function getByCategory($categoryId, $limit = null) {
        return self::getAll(['category' => $categoryId, 'limit' => $limit]);
    }
    
    public static function search($query, $limit = null) {
        return self::getAll(['search' => $query, 'limit' => $limit]);
    }
    
    public static function create($data) {
        $pdo = getPDO();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, original_price, stock, image, featured) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['description'],
                $data['price'],
                $data['original_price'] ?? $data['price'],
                $data['stock'],
                $data['image'] ?? null,
                $data['featured'] ?? 0
            ]);
            
            if ($result) {
                return ['success' => true, 'id' => $pdo->lastInsertId()];
            } else {
                return ['success' => false, 'error' => 'Failed to create product'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public static function update($id, $data) {
        $pdo = getPDO();
        
        // Build dynamic SQL based on provided data
        $fields = [];
        $params = [];
        
        if (isset($data['category_id'])) {
            $fields[] = "category_id = ?";
            $params[] = $data['category_id'];
        }
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $params[] = $data['name'];
        }
        
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (isset($data['price'])) {
            $fields[] = "price = ?";
            $params[] = $data['price'];
        }
        
        if (isset($data['original_price'])) {
            $fields[] = "original_price = ?";
            $params[] = $data['original_price'];
        }
        
        if (isset($data['stock'])) {
            $fields[] = "stock = ?";
            $params[] = $data['stock'];
        }
        
        if (isset($data['image'])) {
            $fields[] = "image = ?";
            $params[] = $data['image'];
        }
        
        if (isset($data['featured'])) {
            $fields[] = "featured = ?";
            $params[] = $data['featured'];
        }
        
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        
        // Always update the updated_at timestamp
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        
        if (empty($fields)) {
            return false; // No fields to update
        }
        
        $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public static function updateImage($id, $imageFilename) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        return $stmt->execute([$imageFilename, $id]);
    }
    
    public static function delete($id) {
        $pdo = getPDO();
        
        // Get product to delete image
        $product = self::getById($id);
        if ($product && $product['image']) {
            $imagePath = UPLOAD_DIR . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function updateStock($id, $quantity) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    public static function getCount($filters = []) {
        $pdo = getPDO();
        $sql = "SELECT COUNT(*) FROM products p WHERE p.status = 'active'";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get products with low stock
     */
    public static function getLowStock($threshold = 10) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.stock > 0 AND p.stock <= ? AND p.status = 'active'
            ORDER BY p.stock ASC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get products that are out of stock
     */
    public static function getOutOfStock() {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.stock = 0 AND p.status = 'active'
            ORDER BY p.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get inventory statistics
     */
    public static function getInventoryStats() {
        $pdo = getPDO();
        
        // Total products
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Low stock count
        $stmt = $pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock > 0 AND stock <= 10 AND status = 'active'");
        $stmt->execute();
        $lowStock = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];
        
        // Out of stock count
        $stmt = $pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE stock = 0 AND status = 'active'");
        $stmt->execute();
        $outOfStock = $stmt->fetch(PDO::FETCH_ASSOC)['out_of_stock'];
        
        // Total inventory value
        $stmt = $pdo->prepare("SELECT SUM(price * stock) as total_value FROM products WHERE status = 'active'");
        $stmt->execute();
        $totalValue = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'] ?? 0;
        
        return [
            'total_products' => $total,
            'low_stock_count' => $lowStock,
            'out_of_stock_count' => $outOfStock,
            'total_inventory_value' => $totalValue
        ];
    }
    
    /**
     * Update stock for multiple products
     */
    public static function bulkUpdateStock($updates) {
        $pdo = getPDO();
        $successCount = 0;
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            
            foreach ($updates as $productId => $newStock) {
                if ($stmt->execute([$newStock, $productId])) {
                    $successCount++;
                }
            }
            
            $pdo->commit();
            return $successCount;
        } catch (Exception $e) {
            $pdo->rollback();
            error_log("Bulk stock update error: " . $e->getMessage());
            return 0;
        }
    }
}
?>