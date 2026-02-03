# 🛒 LOCAL PANTRY - E-Commerce Platform

A modern, full-featured e-commerce platform built with PHP for local grocery stores and small businesses.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.0+-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## ✨ Features

### 🛍️ Customer Features
- **Modern Storefront** - Beautiful, responsive design
- **Product Catalog** - Browse products by categories with search and filters
- **Shopping Cart** - Add/remove items with quantity management
- **Wishlist** - Save favorite products for later
- **User Accounts** - Registration, login, and profile management
- **Multiple Payment Options** - Cash on Delivery, Bank Transfer, WhatsApp Pay
- **Order Tracking** - Real-time order status updates
- **Mobile Responsive** - Optimized for all devices

### �️ Admin Panel
- **Dashboard** - Overview with key business metrics
- **Product Management** - Full CRUD operations with image upload
- **Inventory Management** - Stock tracking with low stock alerts
- **Order Management** - Process orders and update status
- **Customer Management** - View customer data and activity
- **Analytics & Reports** - Sales charts, revenue tracking, top products
- **Category Management** - Organize products efficiently

### 🔧 Technical Features
- **MVC Architecture** - Clean, maintainable code structure
- **Secure Authentication** - Password hashing and session management
- **Email System** - Order confirmations and notifications
- **Image Management** - Upload, resize, and optimize product images
- **Database Migrations** - Version-controlled database schema
- **Responsive Design** - TailwindCSS for modern UI

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/local-pantry.git
   cd local-pantry
   ```

2. **Set up the database**
   ```bash
   # Create a MySQL database
   mysql -u root -p -e "CREATE DATABASE local_pantry;"
   
   # Import the schema
   mysql -u root -p local_pantry < database/schema.sql
   ```

3. **Configure the application**
   ```bash
   # Copy the example configuration
   cp src/config.php.example src/config.php
   
   # Edit src/config.php with your settings:
   # - Database credentials
   # - Email configuration
   # - Site URL and name
   ```

4. **Set up file permissions**
   ```bash
   chmod 755 public/uploads/products/
   chmod 755 logs/
   ```

5. **Create admin account**
   ```bash
   php create-admin.php
   ```

6. **Start the application**
   - Point your web server to the `public/` directory
   - Visit your domain to see the storefront
   - Access admin panel at `/admin/`

## 🔒 Security & Configuration

### Important Security Notes
- **NEVER commit `src/config.php` to version control** - it contains sensitive data
- Always use `src/config.php.example` as a template for your configuration
- Update database credentials, email settings, and API keys in your local `src/config.php`
- The `.gitignore` file is configured to exclude sensitive files automatically

### Production Deployment Checklist
- [ ] Set `EMAIL_DEBUG = false` for production email sending
- [ ] Set `error_reporting(0)` and `ini_set('display_errors', 0)` for production
- [ ] Configure HTTPS for secure connections
- [ ] Update all placeholder credentials with real values
- [ ] Test email functionality with real SMTP settings
- [ ] Regularly update PHP version and dependencies

## � Project Structure

```
local-pantry/
├── public/                 # Web root
│   ├── admin/             # Admin panel
│   ├── assets/            # CSS, JS, images
│   └── uploads/           # User uploaded files
├── src/                   # Application source
│   ├── models/            # Data models
│   ├── includes/          # Shared components
│   └── *.php             # Core application files
├── database/              # Database schema and migrations
└── docs/                  # Documentation
```

## � Configuration

### Database Configuration
Edit `src/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'local_pantry');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Email Configuration
Configure email settings in `src/config.php`:
```php
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_USER', 'your-email@domain.com');
define('SMTP_PASS', 'your-password');
```

## � Admin Panel

Access the admin panel at `/admin/` with your admin credentials.

### Default Admin Features:
- **Dashboard** - Business overview and metrics
- **Products** - Add, edit, delete products
- **Inventory** - Monitor stock levels and alerts
- **Orders** - Process and track customer orders
- **Customers** - Manage customer accounts
- **Analytics** - Sales reports and insights

## �️ Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Secure session management
- File upload validation

## 🎨 Customization

### Styling
- Built with TailwindCSS for easy customization
- Custom CSS in `public/assets/css/custom.css`
- Responsive design components

### Adding Features
- Follow MVC pattern in `src/` directory
- Add new models in `src/models/`
- Create views in `public/`
- Update database schema in `database/`

## 📝 API Endpoints

### Wishlist API
- `POST /wishlist-api.php` - Add/remove items from wishlist
- Requires authentication

### Cart Management
- Session-based cart management
- AJAX updates for smooth UX

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- TailwindCSS for the beautiful UI components
- Chart.js for analytics visualizations
- PHP community for excellent documentation

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Check the documentation in `/docs/`
- Review the setup guides in the repository

---

**Built with ❤️ for local businesses**