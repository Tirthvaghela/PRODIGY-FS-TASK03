<?php
require_once __DIR__ . '/../db.php';

class Address {
    
    public static function getByUserId($userId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, label ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public static function getById($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function getDefault($userId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = TRUE LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public static function create($data) {
        $pdo = getPDO();
        
        // If this is set as default, unset other defaults for this user
        if ($data['is_default']) {
            self::unsetDefault($data['user_id']);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO user_addresses (user_id, label, name, phone, address_line_1, address_line_2, 
                                      city, state, postal_code, country, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $data['user_id'],
            $data['label'],
            $data['name'],
            $data['phone'],
            $data['address_line_1'],
            $data['address_line_2'] ?? null,
            $data['city'],
            $data['state'],
            $data['postal_code'],
            $data['country'] ?? 'India',
            $data['is_default'] ?? false
        ]);
        
        if ($success) {
            return ['success' => true, 'address_id' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'error' => 'Failed to create address'];
        }
    }
    
    public static function update($id, $data) {
        $pdo = getPDO();
        
        // Get current address to check user_id
        $currentAddress = self::getById($id);
        if (!$currentAddress) {
            return ['success' => false, 'error' => 'Address not found'];
        }
        
        // If this is set as default, unset other defaults for this user
        if ($data['is_default']) {
            self::unsetDefault($currentAddress['user_id']);
        }
        
        $stmt = $pdo->prepare("
            UPDATE user_addresses 
            SET label=?, name=?, phone=?, address_line_1=?, address_line_2=?, 
                city=?, state=?, postal_code=?, country=?, is_default=?, updated_at=CURRENT_TIMESTAMP
            WHERE id=?
        ");
        
        $success = $stmt->execute([
            $data['label'],
            $data['name'],
            $data['phone'],
            $data['address_line_1'],
            $data['address_line_2'] ?? null,
            $data['city'],
            $data['state'],
            $data['postal_code'],
            $data['country'] ?? 'India',
            $data['is_default'] ?? false,
            $id
        ]);
        
        return ['success' => $success];
    }
    
    public static function delete($id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function setDefault($id, $userId) {
        $pdo = getPDO();
        
        // First, unset all defaults for this user
        self::unsetDefault($userId);
        
        // Then set this address as default
        $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = TRUE WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
    
    private static function unsetDefault($userId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    public static function getFormattedAddress($address) {
        $parts = [];
        
        if (!empty($address['address_line_1'])) {
            $parts[] = $address['address_line_1'];
        }
        
        if (!empty($address['address_line_2'])) {
            $parts[] = $address['address_line_2'];
        }
        
        $cityStateParts = [];
        if (!empty($address['city'])) {
            $cityStateParts[] = $address['city'];
        }
        if (!empty($address['state'])) {
            $cityStateParts[] = $address['state'];
        }
        if (!empty($address['postal_code'])) {
            $cityStateParts[] = $address['postal_code'];
        }
        
        if (!empty($cityStateParts)) {
            $parts[] = implode(', ', $cityStateParts);
        }
        
        if (!empty($address['country']) && $address['country'] !== 'India') {
            $parts[] = $address['country'];
        }
        
        return implode(', ', $parts);
    }
    
    public static function getCount($userId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}
?>