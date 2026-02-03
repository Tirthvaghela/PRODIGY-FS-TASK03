-- Payment System Migration
-- Add payment-related columns to orders table

ALTER TABLE orders 
ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER status,
ADD COLUMN payment_id VARCHAR(100) NULL AFTER payment_status,
ADD COLUMN razorpay_order_id VARCHAR(100) NULL AFTER payment_id,
ADD COLUMN payment_date TIMESTAMP NULL AFTER razorpay_order_id;

-- Create payment_transactions table for detailed payment tracking
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('razorpay', 'cod', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    razorpay_order_id VARCHAR(100) NULL,
    razorpay_payment_id VARCHAR(100) NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_razorpay_payment_id (razorpay_payment_id)
);

-- Create product_reviews table for customer reviews
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(200) NULL,
    review_text TEXT NULL,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_product_order (user_id, product_id, order_id),
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rating (rating),
    INDEX idx_approved (is_approved)
);

-- Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id)
);

-- Add average rating and review count to products table
ALTER TABLE products 
ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER price,
ADD COLUMN review_count INT DEFAULT 0 AFTER average_rating;

-- Create trigger to update product ratings when reviews are added/updated
DELIMITER //
CREATE TRIGGER update_product_rating_after_review_insert
AFTER INSERT ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        average_rating = (
            SELECT AVG(rating) 
            FROM product_reviews 
            WHERE product_id = NEW.product_id AND is_approved = TRUE
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM product_reviews 
            WHERE product_id = NEW.product_id AND is_approved = TRUE
        )
    WHERE id = NEW.product_id;
END//

CREATE TRIGGER update_product_rating_after_review_update
AFTER UPDATE ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        average_rating = (
            SELECT AVG(rating) 
            FROM product_reviews 
            WHERE product_id = NEW.product_id AND is_approved = TRUE
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM product_reviews 
            WHERE product_id = NEW.product_id AND is_approved = TRUE
        )
    WHERE id = NEW.product_id;
END//

CREATE TRIGGER update_product_rating_after_review_delete
AFTER DELETE ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        average_rating = COALESCE((
            SELECT AVG(rating) 
            FROM product_reviews 
            WHERE product_id = OLD.product_id AND is_approved = TRUE
        ), 0.00),
        review_count = (
            SELECT COUNT(*) 
            FROM product_reviews 
            WHERE product_id = OLD.product_id AND is_approved = TRUE
        )
    WHERE id = OLD.product_id;
END//
DELIMITER ;

-- Insert sample payment transaction for existing orders (optional)
INSERT INTO payment_transactions (order_id, payment_method, payment_status, amount, currency)
SELECT id, payment_method, 'success', total_amount, 'INR'
FROM orders 
WHERE payment_method = 'COD';

-- Update existing orders to have payment_status
UPDATE orders SET payment_status = 'paid' WHERE payment_method = 'COD';

-- Add some sample reviews (optional)
INSERT INTO product_reviews (product_id, user_id, rating, review_title, review_text, is_verified_purchase) VALUES
(1, 2, 5, 'Excellent Quality!', 'Fresh and organic apples. Great taste and quality. Highly recommended!', TRUE),
(1, 3, 4, 'Good product', 'Nice apples, delivered fresh. Will order again.', TRUE),
(2, 2, 5, 'Perfect for cooking', 'Best basmati rice I have used. Perfect for biryani and pulao.', TRUE),
(3, 3, 4, 'Fresh and tasty', 'Good quality tomatoes, fresh and ripe. Good for cooking.', TRUE);

-- Update product ratings based on sample reviews
UPDATE products SET 
    average_rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id AND is_approved = TRUE),
    review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = products.id AND is_approved = TRUE);