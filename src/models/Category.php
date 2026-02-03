<?php
require_once __DIR__ . '/../db.php';

class Category {
    
    public static function getAll() {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }
    
    public static function getById($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function create($name, $description = null) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }
    
    public static function update($id, $data) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
    }
    
    public static function delete($id) {
        $pdo = getPDO();
        
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $productCount = $stmt->fetchColumn();
        
        if ($productCount > 0) {
            return ['success' => false, 'error' => 'Cannot delete category with existing products'];
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $success = $stmt->execute([$id]);
        
        return ['success' => $success];
    }
    
    public static function getWithProductCount() {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                            FROM categories c 
                            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                            GROUP BY c.id 
                            ORDER BY c.name ASC");
        return $stmt->fetchAll();
    }
    
    public static function getPopular($limit = 5) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT c.*, COUNT(p.id) as product_count 
                              FROM categories c 
                              LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                              GROUP BY c.id 
                              HAVING product_count > 0
                              ORDER BY product_count DESC 
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>