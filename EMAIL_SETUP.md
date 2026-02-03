# Email Notification System

The Local Pantry e-commerce platform now includes a comprehensive email notification system that automatically sends emails to customers for order confirmations and status updates.

## 📧 Features

### Automatic Email Notifications
- **Order Confirmation**: Sent immediately when a customer places an order
- **Order Status Updates**: Sent when admin updates order status (pending → processing → shipped → delivered)
- **Professional HTML Templates**: Beautiful, responsive email templates with your branding

### Email Types

#### 1. Order Confirmation Email
- **Trigger**: Automatically sent when order is placed
- **Content**: 
  - Order details (number, date, items, total)
  - Customer information
  - Shipping address
  - Payment method
  - Next steps and tracking link

#### 2. Order Status Update Email
- **Trigger**: Sent when admin changes order status
- **Content**:
  - Status change notification (old → new status)
  - Order number and customer details
  - Status-specific messages
  - Links to track order and contact support

## 🛠 Configuration

### Email Settings (src/config.php)
```php
// Email Configuration
define('ADMIN_EMAIL', 'admin@localpantry.com');
define('EMAIL_DEBUG', true); // Set to false in production
```

### Development vs Production

#### Development Mode (EMAIL_DEBUG = true)
- Emails are logged to `logs/emails.log` instead of being sent
- Perfect for testing without sending actual emails
- View email content in the log file

#### Production Mode (EMAIL_DEBUG = false)
- Emails are sent using PHP's `mail()` function
- Requires server mail configuration
- Consider using PHPMailer or email service (SendGrid, Mailgun) for better reliability

## 📁 File Structure

```
src/
├── email.php              # EmailService class with all email functionality
├── config.php             # Email configuration settings
└── ...

public/
├── checkout.php           # Updated to send order confirmation emails
└── admin/
    └── orders.php         # Updated to send status update emails

logs/
└── emails.log            # Email log file (development mode)
```

## 🧪 Testing Emails

### Using the Email System
1. Login as admin
2. Go to admin panel → Orders
3. Update order status to automatically send status update emails
4. Check `logs/emails.log` to see the email content (in debug mode)

### Manual Testing
```php
// Test order confirmation
$emailSent = EmailService::sendOrderConfirmation($order, $orderItems, $customer);

// Test status update
$emailSent = EmailService::sendOrderStatusUpdate($order, $customer, 'pending', 'shipped');
```

## 🎨 Email Templates

### Design Features
- **Responsive Design**: Works on desktop and mobile
- **Brand Colors**: Uses your site's emerald green color scheme
- **Professional Layout**: Clean, modern design with proper spacing
- **Call-to-Action Buttons**: Links to track orders and contact support
- **Order Details Table**: Clear presentation of order items and totals

### Customization
To customize email templates, edit the methods in `src/email.php`:
- `generateOrderConfirmationEmail()` - Order confirmation template
- `generateOrderStatusUpdateEmail()` - Status update template

## 🚀 Production Setup

### 1. Configure Mail Server
Ensure your server has mail functionality configured:
```bash
# Test if mail works
echo "Test email" | mail -s "Test Subject" your-email@example.com
```

### 2. Update Configuration
```php
// In src/config.php
define('EMAIL_DEBUG', false);           // Enable actual email sending
define('ADMIN_EMAIL', 'your-email@yourdomain.com');
define('SITE_URL', 'https://yourdomain.com');
```

### 3. Consider Email Service (Recommended)
For better deliverability, consider using:
- **PHPMailer** with SMTP
- **SendGrid** API
- **Mailgun** API
- **Amazon SES**

## 🔧 Advanced Configuration

### Using PHPMailer (Recommended for Production)
```php
// Install PHPMailer
composer require phpmailer/phpmailer

// Update EmailService to use PHPMailer instead of mail()
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
```

### Email Queue System
For high-volume sites, consider implementing an email queue:
- Store emails in database
- Process emails in background
- Retry failed emails
- Track email delivery status

## 📊 Email Analytics

### Current Logging
- Email attempts are logged to PHP error log
- Development emails are saved to `logs/emails.log`
- Success/failure status is tracked

### Future Enhancements
- Email open tracking
- Click tracking
- Delivery confirmation
- Bounce handling
- Unsubscribe management

## 🛡 Security Considerations

### Current Security Features
- HTML email content is properly escaped
- Email headers are sanitized
- No user input directly in email headers
- Proper MIME type headers

### Best Practices
- Use DKIM signing in production
- Implement SPF records
- Set up DMARC policy
- Use HTTPS for all email links
- Validate email addresses before sending

## 🐛 Troubleshooting

### Common Issues

#### Emails Not Sending
1. Check if `EMAIL_DEBUG` is set to `false`
2. Verify server mail configuration
3. Check PHP error logs
4. Test with simple mail() function

#### Emails Going to Spam
1. Configure SPF, DKIM, and DMARC records
2. Use a reputable email service
3. Avoid spam trigger words
4. Include unsubscribe links

#### Template Issues
1. Test email rendering in different clients
2. Use inline CSS for better compatibility
3. Keep HTML simple and table-based
4. Test on mobile devices

### Debug Commands
```bash
# Check mail logs
tail -f /var/log/mail.log

# Test PHP mail function
php -r "mail('test@example.com', 'Test', 'Test message');"

# Check email queue
mailq
```

## 📈 Future Enhancements

### Planned Features
- Welcome email for new users
- Password reset emails
- Newsletter subscription
- Abandoned cart reminders
- Product back-in-stock notifications
- Review request emails

### Integration Ideas
- SMS notifications for urgent updates
- Push notifications for mobile app
- Slack/Discord notifications for admins
- WhatsApp Business API integration

---

## 🎯 Quick Start

1. **Development**: Keep `EMAIL_DEBUG = true` and check `logs/emails.log`
2. **Production**: Set `EMAIL_DEBUG = false` and configure mail server
3. **Monitoring**: Check logs for email delivery status

Your email notification system is now ready to keep customers informed about their orders! 📧✨