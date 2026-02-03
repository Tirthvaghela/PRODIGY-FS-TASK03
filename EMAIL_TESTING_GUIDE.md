# 📧 Email Testing Guide

This guide will help you test the email functionality in your Local Pantry e-commerce platform.

## 🧪 **Testing Methods**

### **Method 1: Order Confirmation Emails**

1. **Place a Test Order**
   - Go through the complete checkout process
   - Use a real email address for testing
   - Complete the order placement

2. **Check Email Delivery**
   - Check your email inbox (and spam folder)
   - Verify the order confirmation email was received
   - Review the email content for accuracy

### **Method 2: Order Status Update Emails**

1. **Access Admin Panel**
   - Login as admin
   - Navigate to Orders section

2. **Update Order Status**
   - Find any existing order
   - Change the status (pending → processing → shipped → delivered)
   - Customer will automatically receive status update email

3. **Verify Email Content**
   - Check that the email contains correct order information
   - Verify status change is clearly communicated

### **Method 2: Test Through Real User Flow**

1. **Create a Test User Account**
   - Go to `/register.php`
   - Register with your real email address
   - Use a test name like "Test Customer"

2. **Place a Test Order**
   - Login with your test account
   - Add products to cart
   - Go through checkout process
   - Complete the order
   - Check your email for order confirmation

3. **Test Status Updates**
   - Login as admin (`admin@localstore.com` / `admin123`)
   - Go to Admin → Orders
   - Find your test order
   - Update the status (e.g., pending → shipped)
   - Check your email for status update

### **Method 3: Manual PHP Testing**

Create a simple test script if needed:

```php
<?php
// manual-email-test.php
require_once 'src/config.php';
require_once 'src/email.php';

// Test basic mail function
$to = 'your-email@example.com';
$subject = 'Test Email from Local Pantry';
$message = 'This is a test email to verify mail functionality.';
$headers = 'From: ' . ADMIN_EMAIL;

if (mail($to, $subject, $message, $headers)) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send email. Check server configuration.";
}
?>
```

## 🔧 **Server Configuration for Email**

### **For Local Development (XAMPP/WAMP)**

#### **Option 1: Use MailHog (Recommended)**
```bash
# Install MailHog
go get github.com/mailhog/MailHog

# Run MailHog
MailHog

# Configure PHP to use MailHog
# In php.ini:
sendmail_path = "/usr/local/bin/MailHog sendmail test@example.com"
```

#### **Option 2: Configure XAMPP Mail**
1. Edit `php.ini`:
   ```ini
   [mail function]
   SMTP = smtp.gmail.com
   smtp_port = 587
   sendmail_from = your-email@gmail.com
   sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
   ```

2. Edit `sendmail.ini`:
   ```ini
   [sendmail]
   smtp_server=smtp.gmail.com
   smtp_port=587
   auth_username=your-email@gmail.com
   auth_password=your-app-password
   ```

### **For Production Servers**

#### **Option 1: Server Mail Configuration**
Most hosting providers have mail configured. Test with:
```bash
echo "Test email body" | mail -s "Test Subject" your-email@example.com
```

#### **Option 2: Use PHPMailer with SMTP**
```php
// Install PHPMailer
composer require phpmailer/phpmailer

// Update EmailService to use PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
```

## 🐛 **Troubleshooting Email Issues**

### **Common Problems & Solutions**

#### **1. Emails Not Sending**
```bash
# Check if mail function works
php -r "mail('test@example.com', 'Test', 'Test message');"

# Check PHP error log
tail -f /var/log/php_errors.log

# Check mail log
tail -f /var/log/mail.log
```

#### **2. Emails Going to Spam**
- Use a real domain (not localhost)
- Set proper From headers
- Include unsubscribe links
- Configure SPF/DKIM records

#### **3. XAMPP Mail Not Working**
- Install and configure sendmail
- Use MailHog for local testing
- Check firewall settings
- Verify SMTP credentials

### **Debug Steps**

1. **Check Email Debug Mode**
   ```php
   // In config.php
   define('EMAIL_DEBUG', true);  // Logs to file
   define('EMAIL_DEBUG', false); // Actually sends
   ```

2. **Check Logs**
   ```bash
   # Email debug log
   tail -f logs/emails.log
   
   # PHP error log
   tail -f /var/log/php_errors.log
   
   # Server mail log
   tail -f /var/log/mail.log
   ```

3. **Test Mail Function**
   ```php
   <?php
   if (function_exists('mail')) {
       echo "Mail function is available\n";
       
       $result = mail('test@example.com', 'Test', 'Test message');
       echo $result ? "Mail sent successfully" : "Mail failed";
   } else {
       echo "Mail function is not available";
   }
   ?>
   ```

## 📱 **Testing Email Templates**

### **Email Client Testing**
Test your emails in different clients:
- Gmail (web and mobile)
- Outlook (web and desktop)
- Apple Mail
- Yahoo Mail
- Mobile devices (iOS/Android)

### **Template Testing Tools**
- **Litmus**: Professional email testing
- **Email on Acid**: Cross-client testing
- **MailHog**: Local email testing
- **Mailtrap**: Development email testing

## 🚀 **Production Email Setup**

### **Recommended Email Services**

#### **1. SendGrid**
```php
// Install SendGrid PHP library
composer require sendgrid/sendgrid

// Update EmailService to use SendGrid API
```

#### **2. Mailgun**
```php
// Install Mailgun PHP library
composer require mailgun/mailgun-php

// Configure Mailgun API
```

#### **3. Amazon SES**
```php
// Install AWS SDK
composer require aws/aws-sdk-php

// Configure SES
```

### **Production Checklist**
- [ ] Configure SPF record
- [ ] Set up DKIM signing
- [ ] Configure DMARC policy
- [ ] Set up bounce handling
- [ ] Implement unsubscribe links
- [ ] Monitor email delivery rates
- [ ] Set up email analytics

## 📊 **Email Testing Checklist**

### **Development Testing**
- [ ] Test with debug mode ON (logs to file)
- [ ] Test with debug mode OFF (actual sending)
- [ ] Test order confirmation emails
- [ ] Test status update emails
- [ ] Test with different email addresses
- [ ] Test email template rendering

### **Production Testing**
- [ ] Test from production server
- [ ] Test email deliverability
- [ ] Check spam folder placement
- [ ] Test on different email clients
- [ ] Test mobile email rendering
- [ ] Monitor bounce rates
- [ ] Test unsubscribe functionality

## 🔍 **Quick Test Commands**

```bash
# Test server mail function
echo "Test email" | mail -s "Test Subject" your-email@example.com

# Check if sendmail is working
which sendmail

# Test SMTP connection
telnet smtp.gmail.com 587

# Check mail queue
mailq

# View mail logs
tail -f /var/log/mail.log
```

## 📧 **Sample Test Emails**

When testing, you should receive emails that look like this:

### **Order Confirmation Email**
- Professional header with Local Pantry branding
- Order details table with items and prices
- Customer information and shipping address
- Payment method and order status
- Links to track order and contact support

### **Status Update Email**
- Status change notification (old → new)
- Order number and customer details
- Status-specific messages and colors
- Action buttons for tracking and support

---

## 🎯 **Quick Start Testing**

1. **Configure Email Settings**: Set `EMAIL_DEBUG = false` in `src/config.php` for live email sending
2. **Test Order Email**: Place a test order through the checkout process
3. **Check Your Inbox**: Look for professional email from Local Pantry
4. **Test Status Email**: Enter your email → Click "Send Status Update Email"
5. **Verify Delivery**: Check both emails arrived and look professional

**If emails don't arrive, check spam folder and server mail configuration!** 📧✨