# 🚀 LOCAL PANTRY - Setup Guide

This guide will help you set up the LOCAL PANTRY e-commerce platform on your local machine or server.

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.0+** with extensions:
  - PDO MySQL
  - GD (for image processing)
  - mbstring
  - openssl
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Web Server** (Apache, Nginx, or PHP built-in server)
- **Git** (for cloning the repository)

## 🛠️ Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/local-pantry.git
cd local-pantry
```

### 2. Database Setup

#### Create Database
```bash
mysql -u root -p -e "CREATE DATABASE local_pantry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### Import Schema and Migrations
```bash
# Import main schema
mysql -u root -p local_pantry < database/schema.sql

# Import additional features
mysql -u root -p local_pantry < database/wishlist-migration.sql
mysql -u root -p local_pantry < database/password-reset-migration.sql
mysql -u root -p local_pantry < database/payment-system-migration.sql
```

### 3. Configuration

#### Copy Configuration File
```bash
cp src/config.php.example src/config.php
```

#### Edit Configuration
Open `src/config.php` and update the following:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'local_pantry');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Site Configuration
define('SITE_NAME', 'Your Store Name');
define('SITE_URL', 'http://localhost:8000'); // Update with your URL

// Email Configuration
define('ADMIN_EMAIL', 'your-email@example.com');
define('USE_SMTP', true);
define('SMTP_PASSWORD', 'your-gmail-app-password'); // 16-character app password
```

### 4. File Permissions

```bash
# Make upload directories writable
chmod 755 public/uploads/
chmod 755 public/uploads/products/
chmod 755 logs/

# On Linux/Mac, you might need:
sudo chown -R www-data:www-data public/uploads/
sudo chown -R www-data:www-data logs/
```

### 5. Create Admin Account

#### Method 1: Via Registration (Recommended)
1. Start your web server
2. Visit `/register.php`
3. Create your account
4. Update the user role in database:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
   ```

#### Method 2: Direct Database Insert
```sql
INSERT INTO users (name, email, password_hash, role, created_at) 
VALUES ('Admin User', 'admin@example.com', '$2y$10$example_hash', 'admin', NOW());
```

### 6. Start the Application

#### Using PHP Built-in Server (Development)
```bash
cd public
php -S localhost:8000
```

#### Using Apache/Nginx
Point your web server document root to the `public/` directory.

## 🔧 Configuration Options

### Email Setup

#### Gmail SMTP
1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password: Google Account → Security → App passwords
3. Use the 16-character app password in `SMTP_PASSWORD`

#### MailHog (Development)
```php
define('USE_MAILHOG', true);
define('USE_SMTP', false);
```

### Payment Gateway

#### Razorpay (India)
1. Sign up at [razorpay.com](https://razorpay.com)
2. Get your Key ID and Key Secret
3. Update configuration:
   ```php
   define('RAZORPAY_KEY_ID', 'your_key_id');
   define('RAZORPAY_KEY_SECRET', 'your_key_secret');
   define('PAYMENT_ENABLED', true);
   ```

## 🎯 First Steps After Setup

1. **Access Admin Panel**: Visit `/admin/` with your admin account
2. **Add Categories**: Create product categories
3. **Add Products**: Upload products with images
4. **Test Orders**: Place a test order to verify functionality
5. **Configure Email**: Test password reset and order confirmations

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
- Check database credentials in `src/config.php`
- Ensure MySQL service is running
- Verify database exists and user has permissions

#### File Upload Issues
- Check file permissions on `public/uploads/`
- Verify PHP `upload_max_filesize` and `post_max_size`
- Ensure GD extension is installed

#### Email Not Sending
- Verify SMTP credentials
- Check Gmail app password (not regular password)
- Enable less secure apps or use app passwords

#### Admin Panel Access Denied
- Verify user role is set to 'admin' in database
- Clear browser cache and cookies
- Check session configuration

### PHP Configuration

Recommended `php.ini` settings:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

## 🔒 Security Considerations

### Production Deployment
1. **Disable Debug Mode**:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Use HTTPS**: Configure SSL certificate

3. **Secure File Permissions**:
   ```bash
   chmod 644 src/config.php
   chmod 755 public/uploads/
   ```

4. **Database Security**: Use strong passwords and limit user privileges

5. **Regular Updates**: Keep PHP and MySQL updated

## 📚 Additional Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [TailwindCSS Documentation](https://tailwindcss.com/docs)

## 🆘 Getting Help

If you encounter issues:
1. Check this setup guide
2. Review error logs in `logs/` directory
3. Create an issue on GitHub with error details
4. Include PHP version, OS, and error messages

---

**Happy coding! 🎉**