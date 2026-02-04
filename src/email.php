<?php
require_once __DIR__ . '/config.php';

class EmailService {
    
    /**
     * Send order confirmation email to customer
     */
    public static function sendOrderConfirmation($order, $orderItems, $customer) {
        $subject = "Order Confirmation - " . $order['order_number'] . " - " . SITE_NAME;
        
        $emailBody = self::generateOrderConfirmationEmail($order, $orderItems, $customer);
        
        return self::sendEmail($customer['email'], $customer['name'], $subject, $emailBody);
    }
    
    /**
     * Send order status update email
     */
    public static function sendOrderStatusUpdate($order, $customer, $oldStatus, $newStatus) {
        $subject = "Order Status Update - " . $order['order_number'] . " - " . SITE_NAME;
        
        $emailBody = self::generateOrderStatusUpdateEmail($order, $customer, $oldStatus, $newStatus);
        
        return self::sendEmail($customer['email'], $customer['name'], $subject, $emailBody);
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordReset($user, $resetLink) {
        $subject = "Password Reset Request - " . SITE_NAME;
        
        $emailBody = self::generatePasswordResetEmail($user, $resetLink);
        
        return self::sendEmail($user['email'], $user['name'], $subject, $emailBody);
    }
    
    /**
     * Generate order confirmation email HTML
     */
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
    
    /**
     * Generate order status update email HTML
     */
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
    
    /**
     * Generate password reset email HTML
     */
    private static function generatePasswordResetEmail($user, $resetLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset Request</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9fafb;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #065f46, #047857); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>" . SITE_NAME . "</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Password Reset Request</p>
                </div>
                
                <!-- Reset Content -->
                <div style='padding: 30px; text-align: center;'>
                    <div style='width: 60px; height: 60px; background-color: #3b82f6; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;'>
                        <span style='color: white; font-size: 24px;'>🔑</span>
                    </div>
                    <h2 style='color: #374151; margin: 0 0 10px 0; font-size: 24px;'>Password Reset Request</h2>
                    <p style='color: #6b7280; margin: 0 0 20px 0;'>Hello " . htmlspecialchars($user['name']) . ",</p>
                    
                    <div style='background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left;'>
                        <p style='margin: 0 0 15px 0; color: #374151; font-size: 16px;'>We received a request to reset your password for your " . SITE_NAME . " account.</p>
                        
                        <p style='margin: 0 0 15px 0; color: #374151; font-size: 16px;'>If you made this request, click the button below to reset your password:</p>
                        
                        <div style='text-align: center; margin: 25px 0;'>
                            <a href='{$resetLink}' style='background-color: #3b82f6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; font-size: 16px;'>Reset My Password</a>
                        </div>
                        
                        <p style='margin: 15px 0 0 0; color: #6b7280; font-size: 14px;'>This link will expire in 1 hour for security reasons.</p>
                        
                        <div style='background-color: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                            <p style='margin: 0; color: #92400e; font-size: 14px;'><strong>Security Note:</strong> If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                        </div>
                        
                        <p style='margin: 15px 0 0 0; color: #6b7280; font-size: 14px;'>If the button doesn't work, you can copy and paste this link into your browser:</p>
                        <p style='margin: 5px 0 0 0; color: #3b82f6; font-size: 12px; word-break: break-all;'>{$resetLink}</p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #374151; color: #d1d5db; padding: 20px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0 0 10px 0;'>This is an automated message from " . SITE_NAME . "</p>
                    <p style='margin: 0;'>Need help? Contact us at <a href='mailto:" . ADMIN_EMAIL . "' style='color: #10b981;'>" . ADMIN_EMAIL . "</a></p>
                </div>
                
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Send email using PHP's mail function, SMTP, or MailHog
     */
    private static function sendEmail($toEmail, $toName, $subject, $htmlBody) {
        // For development/testing, you can log emails instead of sending
        if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
            return self::logEmail($toEmail, $toName, $subject, $htmlBody);
        }
        
        // Use MailHog for local testing
        if (defined('USE_MAILHOG') && USE_MAILHOG) {
            return self::sendMailHogEmail($toEmail, $toName, $subject, $htmlBody);
        }
        
        // Check if SMTP is configured
        if (defined('USE_SMTP') && USE_SMTP && defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD)) {
            return self::sendSMTPEmail($toEmail, $toName, $subject, $htmlBody);
        }
        
        // Email headers for regular mail()
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>',
            'Reply-To: ' . ADMIN_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email
        $success = mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
        
        // Log email attempt
        error_log("Email sent to {$toEmail}: " . ($success ? 'SUCCESS' : 'FAILED') . " - Subject: {$subject}");
        
        return $success;
    }
    
    /**
     * Send email using MailHog (localhost:1025)
     */
    private static function sendMailHogEmail($toEmail, $toName, $subject, $htmlBody) {
        // Configure PHP to use MailHog
        ini_set('SMTP', 'localhost');
        ini_set('smtp_port', '1025');
        ini_set('sendmail_from', ADMIN_EMAIL);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>',
            'Reply-To: ' . ADMIN_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $success = mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
        
        if ($success) {
            error_log("MailHog email sent to {$toEmail} - Subject: {$subject}");
        } else {
            error_log("MailHog email failed to {$toEmail} - Subject: {$subject}");
        }
        
        return $success;
    }
    
    /**
     * Send email using SMTP (Gmail) - Fixed SSL version
     */
    private static function sendSMTPEmail($toEmail, $toName, $subject, $htmlBody) {
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 465; // SSL port instead of 587
        $smtpUsername = ADMIN_EMAIL;
        $smtpPassword = SMTP_PASSWORD;
        
        try {
            // Create SSL context for direct SSL connection
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT
                ]
            ]);
            
            // Connect directly with SSL (bypasses STARTTLS issues)
            $socket = stream_socket_client("ssl://{$smtpHost}:{$smtpPort}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
            
            if (!$socket) {
                error_log("SMTP SSL Connection failed: $errstr ($errno)");
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("SMTP Server error: $response");
                fclose($socket);
                return false;
            }
            
            // EHLO - read all response lines
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
                $ehloResponse = $response;
            } while (substr($response, 3, 1) == '-'); // Continue reading multi-line response
            
            // AUTH LOGIN (no STARTTLS needed with SSL)
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            
            if (substr($response, 0, 3) != '334') {
                error_log("AUTH LOGIN failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send username
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            
            // Send password
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            
            if (substr($response, 0, 3) != '235') {
                error_log("SMTP Authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // MAIL FROM
            fputs($socket, "MAIL FROM: <{$smtpUsername}>\r\n");
            $response = fgets($socket, 515);
            
            // RCPT TO
            fputs($socket, "RCPT TO: <{$toEmail}>\r\n");
            $response = fgets($socket, 515);
            
            // DATA
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            
            // Email content
            $headers = "From: " . SITE_NAME . " <{$smtpUsername}>\r\n";
            $headers .= "To: {$toName} <{$toEmail}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $htmlBody . "\r\n.\r\n");
            $response = fgets($socket, 515);
            
            // QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            if (substr($response, 0, 3) == '250') {
                error_log("SMTP Email sent successfully to {$toEmail}");
                return true;
            } else {
                error_log("SMTP Send failed: $response");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("SMTP Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log email to file for development/testing
     */
    private static function logEmail($toEmail, $toName, $subject, $htmlBody) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/emails.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "
=== EMAIL LOG - {$timestamp} ===
To: {$toName} <{$toEmail}>
Subject: {$subject}
Body: {$htmlBody}
=== END EMAIL ===

";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        return true; // Always return true for logging
    }
}
?>