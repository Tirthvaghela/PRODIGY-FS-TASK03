-- Create database
CREATE DATABASE IF NOT EXISTS local_store_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE local_store_db;

-- Users table (customers and admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured)
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'COD',
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password_hash, role) VALUES 
('Admin', 'admin@localstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Men and women fashion'),
('Books', 'Educational and fiction books'),
('Home & Kitchen', 'Home appliances and kitchen items'),
('Produce', 'Fresh fruits and vegetables'),
('Bakery', 'Fresh baked goods'),
('Dairy', 'Milk, cheese, and dairy products'),
('Pantry', 'Pantry staples and preserves'),
('Beverages', 'Drinks and beverages');

-- Insert sample products
INSERT INTO products (category_id, name, description, price, original_price, stock, image, featured) VALUES
(1, 'Wireless Headphones', 'High-quality Bluetooth headphones with noise cancellation', 2999.00, 3999.00, 25, 'headphones.jpg', TRUE),
(1, 'Smart Watch', 'Fitness tracking smartwatch with heart rate monitor', 4999.00, 6999.00, 15, 'smartwatch.jpg', TRUE),
(2, 'Cotton T-Shirt', 'Premium cotton round neck t-shirt', 499.00, 799.00, 50, 'tshirt.jpg', FALSE),
(3, 'Python Programming Book', 'Complete guide to Python programming', 599.00, 799.00, 30, 'book.jpg', FALSE),
(5, 'Organic Heirloom Tomatoes', 'Sun-ripened tomatoes bursting with flavor. These heirloom varieties are grown without pesticides and picked at peak ripeness for your kitchen.', 149.00, 199.00, 45, 'tomatoes.jpg', TRUE),
(6, 'Artisanal Sourdough', 'Freshly baked every morning using traditional methods. Made with 100% organic flour and a 48-hour fermentation process for the perfect crust and tang.', 195.00, 240.00, 20, 'sourdough.jpg', TRUE),
(7, 'Farm Fresh Eggs (Dozen)', 'Pasture-raised eggs delivered daily from local farms. Our chickens enjoy a diverse diet and plenty of sunshine, resulting in rich, golden yolks.', 225.00, 270.00, 30, 'eggs.jpg', FALSE),
(8, 'Local Honey (Wildflower)', 'Pure, unfiltered honey harvested from local wildflower fields. Retains all natural enzymes and pollen for maximum health benefits and floral notes.', 360.00, 450.00, 25, 'honey.jpg', TRUE),
(9, 'Cold Brew Coffee Concentrate', 'Steeped for 18 hours for an incredibly smooth, low-acid, bold finish. Perfect for iced coffees or adding to smoothies for a natural energy boost.', 450.00, 540.00, 18, 'coffee.jpg', FALSE);