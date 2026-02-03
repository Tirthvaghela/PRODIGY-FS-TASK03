<?php
require_once __DIR__ . '/../db.php';

class User {
    
    public static function getById($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function getByEmail($email) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public static function create($data) {
        $pdo = getPDO();
        
        // Check if email already exists
        if (self::getByEmail($data['email'])) {
            return ['success' => false, 'error' => 'Email already exists'];
        }
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, address, role) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $data['name'],
            $data['email'],
            $passwordHash,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['role'] ?? 'customer'
        ]);
        
        if ($success) {
            return ['success' => true, 'user_id' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'error' => 'Failed to create user'];
        }
    }
    
    public static function update($id, $data) {
        $pdo = getPDO();
        
        $sql = "UPDATE users SET name=?, phone=?, address=?";
        $params = [$data['name'], $data['phone'] ?? null, $data['address'] ?? null];
        
        // Update password if provided
        if (!empty($data['password'])) {
            $sql .= ", password_hash=?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public static function updateProfile($id, $data) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['phone'], $data['address'], $id]);
    }
    
    public static function changePassword($id, $currentPassword, $newPassword) {
        $user = self::getById($id);
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }
        
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $success = $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
        
        return ['success' => $success];
    }
    
    public static function getAll($filters = null) {
        $pdo = getPDO();
        
        $sql = "SELECT id, name, email, phone, address, role, created_at FROM users";
        $params = [];
        
        // Handle both string and array parameters for backward compatibility
        if ($filters) {
            if (is_string($filters)) {
                // Old format: User::getAll('customer')
                $sql .= " WHERE role = ?";
                $params[] = $filters;
            } elseif (is_array($filters) && isset($filters['role'])) {
                // New format: User::getAll(['role' => 'customer'])
                $sql .= " WHERE role = ?";
                $params[] = $filters['role'];
            }
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public static function getCustomers() {
        return self::getAll('customer');
    }
    
    public static function getAdmins() {
        return self::getAll('admin');
    }
    
    public static function delete($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        return $stmt->execute([$id]);
    }
    
    public static function getCount($role = null) {
        $pdo = getPDO();
        
        $sql = "SELECT COUNT(*) FROM users";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE role = ?";
            $params[] = $role;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get recent users
     */
    public static function getRecent($limit = 10) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE role = 'customer' 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>