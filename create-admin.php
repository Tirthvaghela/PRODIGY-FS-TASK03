<?php
// Quick script to create a new admin user
// Run this from command line: php create-admin.php

require_once 'src/config.php';
require_once 'src/db.php';

echo "Creating new admin user...\n";

$email = 'admin@localstore.com';
$password = 'admin123';
$name = 'Admin';

try {
    $pdo = getPDO();
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "Admin user already exists. Updating password...\n";
        
        // Update existing admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, name = ? WHERE email = ? AND role = 'admin'");
        $result = $stmt->execute([$hashedPassword, $name, $email]);
        
        if ($result) {
            echo "✅ Admin password updated successfully!\n";
        } else {
            echo "❌ Failed to update admin password\n";
        }
    } else {
        echo "Creating new admin user...\n";
        
        // Create new admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if ($result) {
            echo "✅ Admin user created successfully!\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    }
    
    echo "\nAdmin Credentials:\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "\nLogin at: " . SITE_URL . "/login.php\n";
    echo "Admin Panel: " . SITE_URL . "/admin/\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>