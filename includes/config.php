<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// DATABASE CONFIGURATION
// ============================================

if (getenv('VERCEL_ENV')) {
    // We're on Vercel
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
    define('ENVIRONMENT', 'production');
} else {
    // We're on local XAMPP
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'booking_system');
    define('ENVIRONMENT', 'local');
}

// ============================================
// PATH CONFIGURATION
// ============================================

if (ENVIRONMENT === 'production') {
    // On Vercel
    define('BASE_PATH', '');
    define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} else {
    // On local
    define('BASE_PATH', '/booking-system');
    define('SITE_URL', 'http://localhost/booking-system');
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function asset_url($path) {
    return SITE_URL . '/' . ltrim($path, '/');
}

function base_path($path = '') {
    if (empty($path)) {
        return BASE_PATH;
    }
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Debug - remove after testing
error_log("Config loaded - Environment: " . ENVIRONMENT);
error_log("BASE_PATH: " . BASE_PATH);
error_log("SITE_URL: " . SITE_URL);


// ============================================
// EMAIL CONFIGURATION FOR PASSWORD RESET
// ============================================

// For development (logs to file) - use this first
function sendResetEmail($to, $reset_link) {
    $subject = "Reset Your Creative Spark Password";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 500px; margin: 0 auto; padding: 20px; }
            .header { background: #2E7D32; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { background: #2E7D32; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Creative Spark FabLab</h2>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                <div style='text-align: center;'>
                    <a href='$reset_link' class='button'>Reset Password</a>
                </div>
                <p>If you didn't request this, please ignore this email.</p>
                <p>This link will expire in 1 hour.</p>
                <hr>
                <p style='font-size: 12px; color: #999;'>Creative Spark FabLab, Dundalk, Co. Louth</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Creative Spark. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Creative Spark FabLab <noreply@creativespark.ie>\r\n";
    
    // For Vercel, you'll need SMTP. For now, log it.
    if (getenv('VERCEL_ENV')) {
        // Log to error_log for debugging
        error_log("Password reset requested for: $to - Link: $reset_link");
        return true;
    } else {
        // Local development - actually send email
        return mail($to, $subject, $message, $headers);
    }
}
?>