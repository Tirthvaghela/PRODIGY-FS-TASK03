<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Site Configuration
define('SITE_NAME', 'LOCAL PANTRY');
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_URL', SITE_URL . '/admin/');

// Email Configuration
define('ADMIN_EMAIL', 'your-email@example.com');
define('EMAIL_DEBUG', true); // Set to false to actually send emails

// SMTP Configuration (for Gmail or MailHog)
define('USE_SMTP', false); // Set to true to use SMTP instead of mail()
define('SMTP_PASSWORD', 'your-app-password'); // Gmail App Password (16 characters)
define('USE_MAILHOG', true); // Set to false when using SMTP

// Payment Gateway Configuration (Razorpay for India)
// IMPORTANT: Replace with your actual Razorpay keys from https://razorpay.com
// Current keys are demo keys and will not work for real payments
define('RAZORPAY_KEY_ID', 'your_razorpay_key_id'); // Replace with your actual Key ID
define('RAZORPAY_KEY_SECRET', 'your_razorpay_key_secret'); // Replace with your actual Key Secret
define('PAYMENT_ENABLED', false); // Set to true after adding real Razorpay keys
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/products/');
define('UPLOAD_URL', '/uploads/products/');

// Session Configuration
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone - Set to India Standard Time
date_default_timezone_set('Asia/Kolkata');
?>