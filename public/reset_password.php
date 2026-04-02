<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, '25849');
$error = '';
$success = false;
$token = $_GET['token'] ?? '';

// Verify token
function verifyToken($conn, $token) {
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

// If token is invalid, show error
if (empty($token)) {
    $error = "Invalid or missing reset token.";
} else {
    $tokenData = verifyToken($conn, $token);
    if (!$tokenData) {
        $error = "This reset link is invalid or has expired. Please request a new one.";
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];
    
    // Validate password
    if (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = "Password must contain at least one number.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Verify token again
        $tokenData = verifyToken($conn, $token);
        if ($tokenData) {
            $email = $tokenData['email'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update user password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                // Mark token as used
                $conn->query("UPDATE password_resets SET used = 1 WHERE token = '$token'");
                $success = true;
                $message = "Password reset successfully! You can now log in with your new password.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            $error = "This reset link has expired. Please request a new one.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Creative Spark</title>
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
        .password-requirements {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 12px;
            color: #666;
        }
        .password-requirements ul {
            margin: 5px 0 0 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1em;
        }
        .form-group input:focus {
            border-color: #2E7D32;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #2E7D32;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 400px;">
        <div class="login-icon">🔑</div>
        <h1>Reset Password</h1>
        
        <?php if ($success): ?>
            <div class="message-box success">
                <?php echo $message; ?>
            </div>
            <div class="back-link">
                <a href="login.php">← Go to Login</a>
            </div>
        <?php elseif ($error): ?>
            <div class="message-box error">
                <?php echo $error; ?>
            </div>
            <div class="back-link">
                <a href="forgot_password.php">← Request New Reset Link</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                
                <div class="password-requirements">
                    <strong>Password must contain:</strong>
                    <ul>
                        <li>At least 8 characters</li>
                        <li>At least one uppercase letter</li>
                        <li>At least one number</li>
                    </ul>
                </div>
                
                <button type="submit" name="reset_password" class="btn">Reset Password</button>
            </form>
            
            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>