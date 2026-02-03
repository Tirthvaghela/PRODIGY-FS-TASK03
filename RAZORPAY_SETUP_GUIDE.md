# 🔑 How to Get Razorpay API Keys - Complete Guide

## Step 1: Sign Up for Razorpay Account

### 1.1 Visit Razorpay Website
- Go to **[https://razorpay.com](https://razorpay.com)**
- Click on **"Sign Up"** or **"Get Started"** button

### 1.2 Create Account
- **Email**: Use your business email
- **Mobile**: Your phone number
- **Password**: Create a strong password
- Click **"Sign Up"**

### 1.3 Verify Your Account
- Check your email for verification link
- Click the verification link
- Verify your mobile number with OTP

## Step 2: Complete Business Information

### 2.1 Business Details
- **Business Name**: "Local Pantry" (or your business name)
- **Business Type**: Select "E-commerce" or "Retail"
- **Business Category**: "Food & Beverages" or "Grocery"
- **Website URL**: Your website URL (if you have one)

### 2.2 Personal Information
- **Full Name**: Your legal name
- **PAN Number**: Your PAN card number
- **Address**: Your business address

### 2.3 Bank Account Details
- **Account Number**: Your business bank account
- **IFSC Code**: Your bank's IFSC code
- **Account Holder Name**: Should match PAN name

## Step 3: Get Test API Keys (For Development)

### 3.1 Access Dashboard
- After signup, you'll be taken to Razorpay Dashboard
- Look for **"Settings"** in the left sidebar
- Click on **"API Keys"**

### 3.2 Generate Test Keys
- You'll see **"Test Mode"** section
- Click **"Generate Test Key"**
- You'll get:
  - **Key ID**: Starts with `rzp_test_`
  - **Key Secret**: A long string (keep this secret!)

### 3.3 Copy Your Test Keys
```
Key ID: rzp_test_xxxxxxxxxx
Key Secret: xxxxxxxxxxxxxxxx
```

## Step 4: Update Your Application

### 4.1 Open Configuration File
- Open `src/config.php` in your project
- Find the Razorpay configuration section

### 4.2 Replace Demo Keys
Replace these lines:
```php
define('RAZORPAY_KEY_ID', 'rzp_test_1234567890'); // Replace this
define('RAZORPAY_KEY_SECRET', 'demo_secret_key_12345'); // Replace this
define('PAYMENT_ENABLED', false); // Change to true
```

With your actual keys:
```php
define('RAZORPAY_KEY_ID', 'rzp_test_your_actual_key_id');
define('RAZORPAY_KEY_SECRET', 'your_actual_secret_key');
define('PAYMENT_ENABLED', true); // Enable payments
```

### 4.3 Save the File
- Save `src/config.php`
- Your application will now use real Razorpay keys

## Step 5: Test Payments

### 5.1 Test Mode Features
- In test mode, no real money is charged
- Use test card numbers provided by Razorpay
- All transactions are simulated

### 5.2 Test Card Numbers
```
Card Number: 4111 1111 1111 1111
Expiry: Any future date
CVV: Any 3 digits
Name: Any name
```

### 5.3 Test Your Application
1. Add items to cart
2. Go to checkout
3. Select "Online Payment"
4. Use test card details
5. Payment should succeed

## Step 6: Go Live (For Production)

### 6.1 Complete KYC Verification
- Upload required documents:
  - **PAN Card**
  - **Bank Statement** (last 3 months)
  - **Business Registration** (if applicable)
  - **Address Proof**

### 6.2 Wait for Approval
- Razorpay will review your documents
- This usually takes 1-3 business days
- You'll get email notification when approved

### 6.3 Get Live Keys
- After approval, go to Dashboard
- Switch to **"Live Mode"**
- Generate **Live API Keys**
- Live keys start with `rzp_live_`

### 6.4 Update Configuration for Production
```php
define('RAZORPAY_KEY_ID', 'rzp_live_your_live_key_id');
define('RAZORPAY_KEY_SECRET', 'your_live_secret_key');
define('PAYMENT_ENABLED', true);
```

## 🔒 Security Best Practices

### 1. Keep Keys Secret
- ❌ Never commit keys to Git
- ❌ Never share keys publicly
- ✅ Store in environment variables (production)
- ✅ Use different keys for test/live

### 2. Environment Variables (Advanced)
For production, consider using environment variables:
```php
define('RAZORPAY_KEY_ID', $_ENV['RAZORPAY_KEY_ID'] ?? 'fallback_key');
define('RAZORPAY_KEY_SECRET', $_ENV['RAZORPAY_KEY_SECRET'] ?? 'fallback_secret');
```

## 📋 Quick Checklist

- [ ] Sign up at razorpay.com
- [ ] Verify email and phone
- [ ] Complete business information
- [ ] Generate test API keys
- [ ] Update `src/config.php` with test keys
- [ ] Set `PAYMENT_ENABLED = true`
- [ ] Test payments with test cards
- [ ] Complete KYC for live mode (optional)
- [ ] Get live keys when ready for production

## 🆘 Troubleshooting

### Common Issues:

**1. "Invalid Key ID" Error**
- Check if you copied the key correctly
- Make sure there are no extra spaces
- Verify the key starts with `rzp_test_` or `rzp_live_`

**2. "Authentication Failed"**
- Check if Key Secret is correct
- Make sure you're using matching Key ID and Secret
- Verify you're in the right mode (test/live)

**3. "Account Not Activated"**
- Complete your profile information
- Verify email and phone number
- Wait for account activation (usually instant for test mode)

## 💡 Tips

1. **Start with Test Mode**: Always test thoroughly before going live
2. **Keep Backups**: Save your keys securely
3. **Monitor Transactions**: Use Razorpay dashboard to track payments
4. **Read Documentation**: Razorpay has excellent docs at docs.razorpay.com
5. **Contact Support**: Razorpay has good customer support if you need help

## 🎯 Next Steps After Setup

1. Test the complete payment flow
2. Verify order creation and email notifications
3. Check admin panel for payment tracking
4. Test refund functionality (if needed)
5. Set up webhooks for real-time updates (advanced)

---

**Need Help?** 
- Razorpay Support: support@razorpay.com
- Documentation: https://docs.razorpay.com
- Community: https://community.razorpay.com