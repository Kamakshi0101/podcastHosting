<?php
require_once 'mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendWelcomeEmail($userEmail, $firstName) {
    // Include PHPMailer autoload file
    require 'vendor/autoload.php';
    
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->SMTPDebug = 0; // Disable debug output in production
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // DKIM Configuration
        $mail->DKIM_domain = SMTP_HOST;
        $mail->DKIM_private = __DIR__ . '/dkim/private.key';
        $mail->DKIM_selector = 'podcastpro';
        $mail->DKIM_passphrase = '';
        $mail->DKIM_identity = $mail->From;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($userEmail);
        
        // Headers to improve deliverability
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . SMTP_FROM_EMAIL . '?subject=unsubscribe>');
        $mail->Priority = 1;
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Your PodcastPro Account';
        
        // Email body with improved formatting and less spammy content
        $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #333; margin-bottom: 20px;'>Hi {$firstName},</h2>
            <p style='color: #555; line-height: 1.6;'>Thank you for creating your PodcastPro account. We're excited to help you share your voice with the world.</p>
            <div style='background: #f7f7f7; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                <p style='color: #444; margin-bottom: 10px;'>Your account gives you access to:</p>
                <ul style='color: #555; line-height: 1.6;'>
                    <li>Professional podcast hosting</li>
                    <li>Analytics dashboard</li>
                    <li>Community features</li>
                    <li>Distribution tools</li>
                </ul>
            </div>
            <p style='color: #555; line-height: 1.6;'>Ready to start? <a href='https://podcastpro.com/dashboard' style='color: #007bff; text-decoration: none;'>Visit your dashboard</a> to create your first podcast.</p>
            <p style='color: #666; margin-top: 30px;'>Best regards,<br>The PodcastPro Team</p>
            <div style='color: #999; font-size: 12px; margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee;'>
                This email was sent to you because you created an account on PodcastPro. If you didn't create this account, please <a href='mailto:support@podcastpro.com' style='color: #666;'>contact us</a>.
            </div>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

// Test sending email to specified address
$testEmail = 'arshchouhan246@gmail.com';
$testName = 'Arsh';
$result = sendWelcomeEmail($testEmail, $testName);
if ($result) {
    echo "Email sent successfully to {$testEmail}\n";
} else {
    echo "Failed to send email to {$testEmail}. Please check error logs.\n";
}
?>