<?php
// Gmail SMTP Configuration Example
// You'll need to enable "App Passwords" in your Gmail account

class SMTPEmailService {
    private static $smtpHost = 'smtp.gmail.com';
    private static $smtpPort = 587;
    private static $smtpUsername = 'your-email@gmail.com'; // Your Gmail
    private static $smtpPassword = 'your-app-password';    // Gmail App Password
    
    public static function sendEmail($to, $subject, $body) {
        // This would require PHPMailer library
        // composer require phpmailer/phpmailer
        
        /*
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = self::$smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$smtpUsername;
            $mail->Password   = self::$smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::$smtpPort;
            
            $mail->setFrom(self::$smtpUsername, 'Local Pantry');
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email failed: {$mail->ErrorInfo}");
            return false;
        }
        */
        
        return false; // Placeholder - install PHPMailer to use
    }
}
?>