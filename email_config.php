<?php
// Email Configuration for DUNZO
// Using Gmail SMTP for reliable email delivery

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Replace with your Gmail
define('SMTP_PASSWORD', 'your-app-password'); // Replace with your Gmail App Password
define('SMTP_FROM_EMAIL', 'noreply@dunzo.com');
define('SMTP_FROM_NAME', 'DUNZO Support');

// Alternative: Use a simple email function for testing
function sendSimpleEmail($to, $subject, $message) {
    // For XAMPP/local development, we'll create email log files
    // instead of trying to send real emails
    
    // Create email_logs directory if it doesn't exist
    if (!is_dir('email_logs')) {
        mkdir('email_logs', 0777, true);
    }
    
    // Create email content with headers
    $email_content = "To: $to\n";
    $email_content .= "Subject: $subject\n";
    $email_content .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\n";
    $email_content .= "Reply-To: support@dunzo.com\n";
    $email_content .= "Date: " . date('r') . "\n";
    $email_content .= "Content-Type: text/html; charset=UTF-8\n\n";
    $email_content .= $message;
    
    // Create unique filename
    $filename = "email_logs/" . date('Y-m-d_H-i-s') . "_" . uniqid() . ".txt";
    
    // Save email to file
    if (file_put_contents($filename, $email_content)) {
        return true; // Return true to show success message
    }
    
    return false;
}

// Function to get email template
function getPasswordResetEmailTemplate($user_name, $reset_link, $email = '') {
    return "
    <html>
    <head>
        <title>Password Reset - DUNZO</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #28a745; margin: 0;'>
                    <span style='color: #ffc107;'>dun</span><span style='color: #28a745;'>zo</span>
                </h1>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 10px; border-left: 4px solid #28a745;'>
                <h2 style='color: #333; margin-top: 0;'>Password Reset Request</h2>
                
                <p>Hello " . htmlspecialchars($user_name) . ",</p>
                
                <p>We received a request to reset your password for your DUNZO account. If you didn't make this request, you can safely ignore this email.</p>
                
                <p>To reset your password, click the button below:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . $reset_link . "' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold;'>
                        Reset Password
                    </a>
                </div>
                
                <p><strong>Or copy and paste this link in your browser:</strong></p>
                <p style='word-break: break-all; color: #666;'>" . $reset_link . "</p>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This link will expire in 1 hour</li>
                    <li>If you didn't request this password reset, please ignore this email</li>
                    <li>For security, this link can only be used once</li>
                </ul>
                
                <p>If you have any questions, please contact our support team.</p>
                
                <p>Best regards,<br>
                <strong>The DUNZO Team</strong></p>
            </div>
            
            <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                <p>This email was sent to " . htmlspecialchars($email) . "</p>
                <p>&copy; 2024 DUNZO Technologies Private Limited. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?> 