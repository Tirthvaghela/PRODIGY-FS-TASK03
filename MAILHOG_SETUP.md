# 📧 MailHog Setup for Local Email Testing

MailHog is a fantastic tool for testing emails locally without actually sending them to real email addresses.

## 🚀 **Quick Setup**

### **Step 1: Install MailHog**

#### **Windows (Recommended)**
1. Download MailHog from: https://github.com/mailhog/MailHog/releases
2. Download `MailHog_windows_amd64.exe`
3. Rename it to `mailhog.exe`
4. Place it in a folder like `C:\mailhog\`
5. Add `C:\mailhog\` to your system PATH

#### **macOS**
```bash
# Using Homebrew
brew install mailhog

# Or download binary
curl -L -o mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_darwin_amd64
chmod +x mailhog
```

#### **Linux**
```bash
# Download and install
sudo wget -O /usr/local/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64
sudo chmod +x /usr/local/bin/mailhog
```

### **Step 2: Configure PHP**

#### **For XAMPP/WAMP**
Edit your `php.ini` file:
```ini
[mail function]
SMTP = localhost
smtp_port = 1025
sendmail_path = "C:\mailhog\mailhog.exe sendmail test@example.com"
```

#### **For Built-in PHP Server**
Create a custom `php.ini`:
```ini
[mail function]
SMTP = localhost
smtp_port = 1025
```

### **Step 3: Start MailHog**

#### **Windows**
```cmd
# Open Command Prompt and run:
mailhog
```

#### **macOS/Linux**
```bash
mailhog
```

### **Step 4: Access MailHog Web Interface**
Open your browser and go to: http://localhost:8025

## 🧪 **Testing with MailHog**

### **1. Start MailHog**
```bash
mailhog
```
You should see:
```
[HTTP] Binding to address: 0.0.0.0:8025
[SMTP] Binding to address: 0.0.0.0:1025
```

### **2. Configure Your Local Pantry**
In `src/config.php`:
```php
// Email Configuration
define('ADMIN_EMAIL', 'admin@localpantry.com');
define('EMAIL_DEBUG', false); // Set to false to actually send emails
```

### **3. Test Email Sending**
1. Place a test order through the checkout process
2. Make sure `EMAIL_DEBUG` is set to `true` in config.php
3. Check `logs/emails.log` to see if emails are being generated
4. If using MailHog, check the web interface at http://localhost:8025
4. Click "Send Order Confirmation Email"
5. Go to MailHog interface: http://localhost:8025
6. You should see your email!

### **4. Test Real Order Flow**
1. Register a new account with any email
2. Place a test order
3. Check MailHog for order confirmation email
4. Update order status in admin panel
5. Check MailHog for status update email

## 🎯 **MailHog Features**

### **Web Interface (http://localhost:8025)**
- **View All Emails**: See all emails sent by your application
- **Email Preview**: View HTML and text versions
- **Email Details**: See headers, attachments, etc.
- **Search**: Find specific emails
- **Delete**: Clear emails from inbox

### **API Access**
```bash
# Get all messages
curl http://localhost:8025/api/v1/messages

# Get specific message
curl http://localhost:8025/api/v1/messages/{id}

# Delete all messages
curl -X DELETE http://localhost:8025/api/v1/messages
```

## 🔧 **Advanced Configuration**

### **Custom MailHog Settings**
```bash
# Run on different ports
mailhog -smtp-bind-addr 127.0.0.1:1026 -ui-bind-addr 127.0.0.1:8026

# Store emails in file
mailhog -storage=maildir -maildir-path=/tmp/mailhog

# Enable authentication
mailhog -auth-file=auth.txt
```

### **Docker Setup**
```yaml
# docker-compose.yml
version: '3'
services:
  mailhog:
    image: mailhog/mailhog
    ports:
      - "1025:1025"
      - "8025:8025"
```

```bash
# Run with Docker
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

## 🐛 **Troubleshooting**

### **Common Issues**

#### **1. MailHog Not Starting**
```bash
# Check if port is in use
netstat -an | grep 1025
netstat -an | grep 8025

# Kill existing processes
taskkill /f /im mailhog.exe  # Windows
pkill mailhog               # macOS/Linux
```

#### **2. PHP Not Sending to MailHog**
```php
<?php
// Test script
$to = 'test@example.com';
$subject = 'Test Email';
$message = 'This is a test email.';
$headers = 'From: admin@localpantry.com';

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>
```

#### **3. Emails Not Appearing in MailHog**
- Check PHP configuration: `php -i | grep mail`
- Verify MailHog is running on port 1025
- Check PHP error logs
- Test with simple mail() function

### **Debug Commands**
```bash
# Check if MailHog is running
curl http://localhost:8025/api/v1/messages

# Test SMTP connection
telnet localhost 1025

# Check PHP mail configuration
php -i | grep -i mail
```

## 📱 **Alternative Tools**

### **Mailtrap**
- Cloud-based email testing
- Free tier available
- Better for team collaboration
- https://mailtrap.io/

### **Papertrail**
- Simple local email testing
- Lightweight alternative
- https://github.com/chancancode/papertrail

### **smtp4dev**
- Windows-specific SMTP server
- GUI application
- https://github.com/rnwood/smtp4dev

## 🎉 **Quick Test Checklist**

- [ ] MailHog installed and running
- [ ] PHP configured to use localhost:1025
- [ ] MailHog web interface accessible (http://localhost:8025)
- [ ] Test order placed and email generated
- [ ] Email appears in MailHog interface
- [ ] Order confirmation email tested
- [ ] Status update email tested
- [ ] Email templates look correct

## 🚀 **Production Migration**

When moving to production:

1. **Update PHP Configuration**
   ```ini
   [mail function]
   SMTP = your-smtp-server.com
   smtp_port = 587
   ```

2. **Update Email Service**
   - Use PHPMailer with SMTP
   - Configure SendGrid/Mailgun
   - Set up proper authentication

3. **Remove MailHog**
   - Stop MailHog service
   - Update PHP configuration
   - Test with real email addresses

---

**MailHog makes email testing super easy! 🎯 You can see exactly what emails your application sends without worrying about spam or delivery issues.** 📧✨