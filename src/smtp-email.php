<?php
require_once __DIR__ . '/config.php';

class SMTPEmailService {
    
    /**
     * Send email using Gmail SMTP
     * You'll need to set up Gmail App Password
     */
    public static function sendOrderConfirmation($order, $orderItems, $customer) {
        $subject = "Order Confirmation - " . $order['order_number'] . " - " . SITE_NAME;
        $emailBody = self::generateOrderConfirmationEmail($order, $orderItems, $customer);
        
        return self::sendSMTPEmail($customer['email'], $customer['name'], $subject, $emailBody);
    }
    
    public static function sendOrderStatusUpdate($order, $customer, $oldStatus, $newStatus) {
        $subject = "Order Status Update - " . $order['order_number'] . " - " . SITE_NAME;
        $emailBody = self::generateOrderStatusUpdateEmail($order, $customer, $oldStatus, $newStatus);
        
        return self::sendSMTPEmail($customer['email'], $customer['name'], $subject, $emailBody);
    }
    
    /**
     * Send email using SMTP (Gmail)
     */
    private static function sendSMTPEmail($toEmail, $toName, $subject, $htmlBody) {
        // Gmail SMTP Configuration
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;
        $smtpUsername = 'your-email@gmail.com';         // Your Gmail
        $smtpPassword = 'your-app-password-here';       // Gmail App Password (not regular password)
        
        // Create socket connection
        $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP Connection failed: $errstr ($errno)");
            return false;
        }
        
        // Enable TLS encryption
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: $response");
            fclose($socket);
            return false;
        }
        
        // SMTP Commands
        $commands = [
            "EHLO localhost\r\n",
            "STARTTLS\r\n"
        ];
        
        foreach ($commands as $command) {
            fputs($socket, $command);
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220' && substr($response, 0, 3) != '250') {
                error_log("SMTP Command failed: $command - Response: $response");
                fclose($socket);
                return false;
            }
        }
        
        // Upgrade to TLS
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("Failed to enable TLS encryption");
            fclose($socket);
            return false;
        }
        
        // Continue with authentication
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 515);
        
        // Authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, base64_encode($smtpUsername) . "\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, base64_encode($smtpPassword) . "\r\n");
        $response = fgets($socket, 515);
        
        if (substr($response, 0, 3) != '235') {
            error_log("SMTP Authentication failed: $response");
            fclose($socket);
            return false;
        }
        
        // Send email
        fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "RCPT TO: <$toEmail>\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        
        // Email headers and body
        $headers = "From: " . SITE_NAME . " <$smtpUsername>\r\n";
        $headers .= "To: $toName <$toEmail>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "\r\n";
        
        fputs($socket, $headers . $htmlBody . "\r\n.\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        if (substr($response, 0, 3) == '250') {
            error_log("Email sent successfully to $toEmail");
            return true;
        } else {
            error_log("Email sending failed: $response");
            return false;
        }
    }
    
    // Copy the email template methods from EmailService
    private static function generateOrderConfirmationEmail($order, $orderItems, $customer) {
        $orderDate = date('M j, Y g:i A', strtotime($order['created_at']));
        $itemsHtml = '';
        
        foreach ($orderItems as $item) {
            $itemsHtml .= "
                <tr>
                    <td style='padding: 12px; border-bottom: 1px solid #e5e7eb;'>
                        <strong>{$item['product_name']}</strong><br>
                        <small style='color: #6b7280;'>Qty: {$item['quantity']} × ₹" . number_format($item['price'], 2) . "</small>
                    </td>
                    <td style='padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right;'>
                        <strong>₹" . number_format($item['subtotal'], 2) . "</strong>
                    </td>
                </tr>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Order Confirmation</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9fafb;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #065f46, #047857); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>" . SITE_NAME . "</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Fresh Local Products</p>
                </div>
                
                <!-- Success Message -->
                <div style='padding: 30px; text-align: center; background-color: #ecfdf5; border-bottom: 1px solid #d1fae5;'>
                    <div style='width: 60px; height: 60px; background-color: #10b981; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;'>
                        <span style='color: white; font-size: 24px;'>✓</span>
                    </div>
                    <h2 style='color: #065f46; margin: 0 0 10px 0; font-size: 24px;'>Order Confirmed!</h2>
                    <p style='color: #047857; margin: 0; font-size: 16px;'>Thank you for your order, " . htmlspecialchars($customer['name']) . "!</p>
                </div>
                
                <!-- Order Details -->
                <div style='padding: 30px;'>
                    <h3 style='color: #374151; margin: 0 0 20px 0; font-size: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>Order Details</h3>
                    
                    <div style='background-color: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; color: #6b7280; font-weight: 500;'>Order Number:</td>
                                <td style='padding: 8px 0; text-align: right; font-weight: bold; color: #111827;'>" . htmlspecialchars($order['order_number']) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #6b7280; font-weight: 500;'>Order Date:</td>
                                <td style='padding: 8px 0; text-align: right; color: #111827;'>{$orderDate}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #6b7280; font-weight: 500;'>Payment Method:</td>
                                <td style='padding: 8px 0; text-align: right; color: #111827;'>" . ($order['payment_method'] === 'COD' ? 'Cash on Delivery' : $order['payment_method']) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #6b7280; font-weight: 500;'>Status:</td>
                                <td style='padding: 8px 0; text-align: right;'>
                                    <span style='background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase;'>
                                        " . ucfirst($order['status']) . "
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Order Items -->
                    <h4 style='color: #374151; margin: 0 0 15px 0; font-size: 16px;'>Order Items</h4>
                    <table style='width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
                        <thead>
                            <tr style='background-color: #f9fafb;'>
                                <th style='padding: 12px; text-align: left; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;'>Item</th>
                                <th style='padding: 12px; text-align: right; color: #374151; font-weight: 600; border-bottom: 1px solid #e5e7eb;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                        <tfoot>
                            <tr style='background-color: #f9fafb;'>
                                <td style='padding: 15px; font-weight: bold; color: #111827; border-top: 2px solid #e5e7eb;'>Total Amount</td>
                                <td style='padding: 15px; text-align: right; font-weight: bold; color: #059669; font-size: 18px; border-top: 2px solid #e5e7eb;'>₹" . number_format($order['total_amount'], 2) . "</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <!-- Shipping Address -->
                    <h4 style='color: #374151; margin: 25px 0 15px 0; font-size: 16px;'>Shipping Address</h4>
                    <div style='background-color: #f9fafb; padding: 15px; border-radius: 8px; color: #374151;'>
                        <strong>" . htmlspecialchars($customer['name']) . "</strong><br>
                        " . htmlspecialchars($customer['email']) . "<br>
                        " . (isset($customer['phone']) ? htmlspecialchars($customer['phone']) . "<br>" : "") . "
                        <div style='margin-top: 8px;'>
                            " . nl2br(htmlspecialchars($order['shipping_address'])) . "
                        </div>
                    </div>
                </div>
                
                <!-- What's Next -->
                <div style='background-color: #ecfdf5; padding: 25px; border-top: 1px solid #d1fae5;'>
                    <h4 style='color: #065f46; margin: 0 0 15px 0; font-size: 16px;'>What happens next?</h4>
                    <div style='color: #047857; font-size: 14px; line-height: 1.6;'>
                        <p style='margin: 0 0 10px 0;'>• We'll prepare your order with care and attention to quality</p>
                        <p style='margin: 0 0 10px 0;'>• You'll receive updates about your order status via email</p>
                        <p style='margin: 0 0 15px 0;'>• Your order will be delivered to your specified address</p>
                        
                        <div style='text-align: center; margin-top: 20px;'>
                            <a href='" . SITE_URL . "/track-order.php' style='background-color: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-block;'>Track Your Order</a>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #374151; color: #d1d5db; padding: 20px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0 0 10px 0;'>Thank you for choosing " . SITE_NAME . "!</p>
                    <p style='margin: 0;'>Need help? Contact us at <a href='mailto:" . ADMIN_EMAIL . "' style='color: #10b981;'>" . ADMIN_EMAIL . "</a></p>
                </div>
                
            </div>
        </body>
        </html>
        ";
    }
    
    private static function generateOrderStatusUpdateEmail($order, $customer, $oldStatus, $newStatus) {
        $statusMessages = [
            'pending' => 'Your order has been received and is being processed.',
            'processing' => 'Your order is being prepared with care.',
            'shipped' => 'Your order is on its way to you!',
            'delivered' => 'Your order has been successfully delivered.',
            'cancelled' => 'Your order has been cancelled.'
        ];
        
        $statusColors = [
            'pending' => '#f59e0b',
            'processing' => '#3b82f6',
            'shipped' => '#8b5cf6',
            'delivered' => '#10b981',
            'cancelled' => '#ef4444'
        ];
        
        $message = $statusMessages[$newStatus] ?? 'Your order status has been updated.';
        $color = $statusColors[$newStatus] ?? '#6b7280';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Order Status Update</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9fafb;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #065f46, #047857); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>" . SITE_NAME . "</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Order Status Update</p>
                </div>
                
                <!-- Status Update -->
                <div style='padding: 30px; text-align: center;'>
                    <div style='width: 60px; height: 60px; background-color: {$color}; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;'>
                        <span style='color: white; font-size: 24px;'>📦</span>
                    </div>
                    <h2 style='color: #374151; margin: 0 0 10px 0; font-size: 24px;'>Order Status Updated</h2>
                    <p style='color: #6b7280; margin: 0 0 20px 0;'>Hello " . htmlspecialchars($customer['name']) . ",</p>
                    
                    <div style='background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 0 0 15px 0; color: #374151; font-size: 16px;'>Your order <strong>" . htmlspecialchars($order['order_number']) . "</strong> status has been updated:</p>
                        
                        <div style='display: flex; align-items: center; justify-content: center; gap: 20px; margin: 20px 0;'>
                            <span style='background-color: #e5e7eb; color: #6b7280; padding: 8px 16px; border-radius: 20px; font-size: 14px; text-transform: uppercase; font-weight: 500;'>" . ucfirst($oldStatus) . "</span>
                            <span style='color: #6b7280; font-size: 18px;'>→</span>
                            <span style='background-color: {$color}; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; text-transform: uppercase; font-weight: 500;'>" . ucfirst($newStatus) . "</span>
                        </div>
                        
                        <p style='margin: 15px 0 0 0; color: #374151; font-size: 16px;'>{$message}</p>
                    </div>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . SITE_URL . "/track-order.php' style='background-color: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-block; margin-right: 10px;'>Track Order</a>
                        <a href='" . SITE_URL . "/support.php' style='background-color: #6b7280; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-block;'>Contact Support</a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #374151; color: #d1d5db; padding: 20px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0 0 10px 0;'>Thank you for choosing " . SITE_NAME . "!</p>
                    <p style='margin: 0;'>Need help? Contact us at <a href='mailto:" . ADMIN_EMAIL . "' style='color: #10b981;'>" . ADMIN_EMAIL . "</a></p>
                </div>
                
            </div>
        </body>
        </html>
        ";
    }
}
?>