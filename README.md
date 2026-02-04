# 🛒 LOCAL PANTRY - E-Commerce Platform

A modern, full-featured e-commerce platform built with PHP for local grocery stores and small businesses.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.0+-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## ✨ Features

### 🛍️ Customer Features
- **Modern Storefront** - Beautiful, responsive design with hero section
- **Product Catalog** - Browse products by categories with search and filters
- **Shopping Cart** - Add/remove items with quantity management
- **Wishlist System** - Save favorite products for later with database persistence
- **User Accounts** - Registration, login, and profile management
- **Password Reset** - Secure email-based password reset functionality
- **Multiple Payment Options** - Cash on Delivery, Bank Transfer, WhatsApp Pay
- **Order Tracking** - Real-time order status updates
- **Mobile Responsive** - Optimized for all devices

### 🏪 Admin Panel
- **Dashboard** - Overview with key business metrics and analytics
- **Product Management** - Full CRUD operations with image upload
- **Inventory Management** - Stock tracking with low stock alerts and bulk updates
- **Order Management** - Process orders, update status, and view detailed order information
- **Customer Management** - View customer data and activity
- **Analytics & Reports** - Interactive sales charts, revenue tracking, top products
- **Category Management** - Organize products efficiently

### 🔧 Technical Features
- **MVC Architecture** - Clean, maintainable code structure
- **Secure Authentication** - Password hashing and session management
- **Email System** - Order confirmations, status updates, and password reset
- **Image Management** - Upload, resize, and optimize product images
- **Database Migrations** - Version-controlled database schema
- **Responsive Design** - TailwindCSS for modern UI
- **AJAX Integration** - Smooth user experience with real-time updates

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
   
   # Run migrations for additional features
   mysql -u root -p local_pantry < database/wishlist-migration.sql
   mysql -u root -p local_pantry < database/password-reset-migration.sql
   ```

3. **Configure the application**
   ```bash
   # Copy and edit configuration
   cp src/config.php.example src/config.php
   # Edit src/config.php with your database credentials and settings
   ```

4. **Set up file permissions**
   ```bash
   chmod 755 public/uploads/
   chmod 755 logs/
   ```

5. **Create admin account**
   - Visit `/register.php` and create your first account
   - Manually update the user role to 'admin' in the database
   - Or create an admin creation script based on your needs

6. **Start the application**
   - Point your web server to the `public/` directory
   - Visit your domain to see the storefront
   - Access admin panel at `/admin/`

## 📁 Project Structure

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
├── logs/                  # Application logs
└── docs/                  # Documentation
```

## ⚙️ Configuration

### Database Configuration
Edit `src/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Email Configuration
Configure email settings in `src/config.php`:
```php
define('ADMIN_EMAIL', 'your-email@example.com');
define('USE_SMTP', true);
define('SMTP_PASSWORD', 'your-app-password');
```

### Payment Configuration
For Razorpay integration:
```php
define('RAZORPAY_KEY_ID', 'your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_key_secret');
define('PAYMENT_ENABLED', true);
```

## 🛡️ Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Secure session management
- File upload validation
- Email-based password reset with secure tokens

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

## � Key Pages

### Customer Pages
- **Homepage** - Hero section with featured products
- **Product Catalog** - Browse and search products
- **Product Details** - Individual product pages
- **Shopping Cart** - Cart management
- **Checkout** - Order placement with multiple payment options
- **User Profile** - Account management and address book
- **Order History** - Track past orders
- **Wishlist** - Save favorite products

### Admin Pages
- **Dashboard** - Business overview and metrics
- **Products** - Product management with image upload
- **Inventory** - Stock management with alerts
- **Orders** - Order processing and status updates
- **Customers** - Customer management
- **Analytics** - Sales reports and insights

## � Recent Updates

- ✅ Enhanced admin panel with inventory management and analytics
- ✅ Wishlist system with database persistence
- ✅ Improved password reset functionality
- ✅ Order details modal for admin
- ✅ Bulk inventory updates
- ✅ Interactive sales charts
- ✅ Mobile-responsive design improvements

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