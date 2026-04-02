<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, '25849');
$message = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate unique token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete any existing tokens for this email
            $conn->query("DELETE FROM password_resets WHERE email = '$email'");
            
            // Insert new token
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expires_at);
            
            if ($stmt->execute()) {
                // Create reset link
                $reset_link = SITE_URL . "/reset_password.php?token=" . $token;
                
                // Send email
                sendResetEmail($email, $reset_link);
                
                $success = true;
                $message = "If an account exists with that email, you will receive a password reset link shortly.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            // Don't reveal if email exists or not (security best practice)
            $success = true;
            $message = "If an account exists with that email, you will receive a password reset link shortly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Creative Spark</title>
    <link rel="stylesheet" href="/css/login.css">
    <style>
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message-box.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .message-box.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .info-text {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 400px;">
        <div class="login-icon">🔐</div>
        <h1>Forgot Password?</h1>
        <div class="info-text">Enter your email address and we'll send you a link to reset your password.</div>
        
        <?php if ($message): ?>
            <div class="message-box <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required style="width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; font-size: 1em;">
            </div>
            <button type="submit" class="btn" style="width: 100%;">Send Reset Link</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>