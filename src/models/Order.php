<?php
require_once __DIR__ . '/../db.php';

class Order {
    
    public static function create($userId, $cartItems, $shippingAddress, $paymentMethod = 'COD') {
        $pdo = getPDO();
        $pdo->beginTransaction();
        
        try {
            $orderNumber = 'LP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $totalAmount = array_sum(array_column($cartItems, 'subtotal'));
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $orderNumber, $totalAmount, $shippingAddress, $paymentMethod]);
            $orderId = $pdo->lastInsertId();
            
            // Create order items and update stock
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                                  VALUES (?, ?, ?, ?, ?)");
            
            foreach ($cartItems as $item) {
                // Add order item
                $stmt->execute([
                    $orderId,
                    $item['id'],
                    $item['cart_quantity'],
                    $item['price'],
                    $item['subtotal']
                ]);
                
                // Update product stock
                $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stockStmt->execute([$item['cart_quantity'], $item['id']]);
            }
            
            $pdo->commit();
            return ['success' => true, 'order_number' => $orderNumber, 'order_id' => $orderId];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public static function getById($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              WHERE o.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function getByOrderNumber($orderNumber, $email = null) {
        $pdo = getPDO();
        
        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.order_number = ?";
        $params = [$orderNumber];
        
        if ($email) {
            $sql .= " AND u.email = ?";
            $params[] = $email;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public static function getByUser($userId, $limit = null) {
        $pdo = getPDO();
        
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public static function getAll($filters = []) {
        $pdo = getPDO();
        
        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id";
        $params = [];
        $conditions = [];
        
        if (!empty($filters['status'])) {
            $conditions[] = "o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public static function getItems($orderId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    public static function updateStatus($id, $status) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public static function getRecentOrders($limit = 10) {
        return self::getAll(['limit' => $limit]);
    }
    
    public static function cancel($id, $userId = null) {
        $pdo = getPDO();
        
        $sql = "UPDATE orders SET status = 'cancelled' WHERE id = ? AND status = 'pending'";
        $params = [$id];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update order with dynamic fields
     */
    public static function update($id, $data) {
        $pdo = getPDO();
        
        if (empty($data)) {
            return false;
        }
        
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $id; // Add ID for WHERE clause
        
        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Get order count with optional filters
     */
    public static function getCount($filters = []) {
        $pdo = getPDO();
        $sql = "SELECT COUNT(*) as count FROM orders WHERE 1=1";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total revenue for a date range
     */
    public static function getTotalRevenue($dateFrom = null, $dateTo = null) {
        $pdo = getPDO();
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
        $params = [];
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    
    public static function getOrderStats() {
        $pdo = getPDO();
        
        // Get total orders
        $totalOrders = self::getCount();
        
        // Get total revenue
        $totalRevenue = self::getTotalRevenue();
        
        // Get orders by status
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $stmt->execute();
        $statusResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ordersByStatus = [];
        foreach ($statusResults as $result) {
            $ordersByStatus[$result['status']] = $result['count'];
        }
        
        // Get recent orders count (last 30 days)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $recentOrders = $stmt->fetchColumn();
        
        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'orders_by_status' => $ordersByStatus,
            'recent_orders' => $recentOrders
        ];
    }
    
    public static function getCountByUser($userId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get top selling products
     */
    public static function getTopSellingProducts($limit = 10, $dateFrom = null, $dateTo = null) {
        $pdo = getPDO();
        $sql = "SELECT 
                    p.id, p.name, p.price,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.price) as total_revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o ON oi.order_id = o.id
                WHERE 1=1";
        $params = [];
        
        if ($dateFrom) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " GROUP BY p.id, p.name, p.price
                  ORDER BY total_sold DESC
                  LIMIT ?";
        $params[] = $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by status for a date range
     */
    public static function getOrdersByStatus($dateFrom = null, $dateTo = null) {
        $pdo = getPDO();
        $sql = "SELECT status, COUNT(*) as count FROM orders WHERE 1=1";
        $params = [];
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " GROUP BY status";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to associative array
        $statusCounts = [];
        foreach ($results as $result) {
            $statusCounts[$result['status']] = $result['count'];
        }
        
        return $statusCounts;
    }
    
    /**
     * Get daily sales for the last N days
     */
    public static function getDailySales($days = 30) {
        $pdo = getPDO();
        $sql = "SELECT 
                    DATE(created_at) as sale_date,
                    SUM(total_amount) as daily_total
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY sale_date ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to associative array with date as key
        $dailySales = [];
        foreach ($results as $result) {
            $dailySales[$result['sale_date']] = (float)$result['daily_total'];
        }
        
        return $dailySales;
    }
}
?>