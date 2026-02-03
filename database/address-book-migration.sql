-- Address Book Migration
-- Add this to your existing database

USE local_store_db;

-- Create user_addresses table for multiple addresses
CREATE TABLE user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) NOT NULL, -- 'Home', 'Office', 'Other', etc.
    name VARCHAR(100) NOT NULL, -- Recipient name
    phone VARCHAR(20) NOT NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'India',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_default (is_default)
) ENGINE=InnoDB;

-- Insert sample addresses for existing users (optional)
-- You can run this after creating the table
INSERT INTO user_addresses (user_id, label, name, phone, address_line_1, city, state, postal_code, is_default) 
SELECT 
    id as user_id,
    'Home' as label,
    name,
    COALESCE(phone, '+91 98765 43210') as phone,
    COALESCE(address, '123 Main Street') as address_line_1,
    'Ahmedabad' as city,
    'Gujarat' as state,
    '380001' as postal_code,
    TRUE as is_default
FROM users 
WHERE role = 'customer' AND (phone IS NOT NULL OR address IS NOT NULL);