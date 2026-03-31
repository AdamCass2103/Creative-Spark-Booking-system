<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, '25849');
$error = '';


if (isLoggedIn()) {
    redirect('../member/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password!';
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $name, $hashed_password);
        
        if ($stmt->fetch() && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['is_admin'] = ($email === 'admin@booking.com');
            
            // ============================================
            // ACCOUNT STATUS CHECK
            // ============================================
            
            // Get user's account status
            $status_query = $conn->query("SELECT account_status FROM users WHERE user_id = $user_id");
            $status = $status_query->fetch_assoc();
            
            // Update last activity
            $conn->query("UPDATE users SET last_activity = CURDATE() WHERE user_id = $user_id");
            
            // ============================================
            // MEMBERSHIP HISTORY TRACKING - MOVED INSIDE
            // ============================================
            
            $today = date('Y-m-d');
            
            // Check if there's an open history record
            $history_check = $conn->query("
                SELECT * FROM membership_history 
                WHERE user_id = $user_id AND end_date IS NULL
            ");
            
            if ($history_check && $history_check->num_rows == 0) {
                // No open record, create one
                $current_status = ($status['account_status'] == 'active') ? 'active' : 'inactive';
                $conn->query("
                    INSERT INTO membership_history (user_id, status, start_date) 
                    VALUES ($user_id, '$current_status', '$today')
                ");
            }
            
            // Always redirect to member dashboard
                redirect('../member/dashboard.php');
                
        } else {
            $error = 'Invalid email or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Login</title>
    <link rel="stylesheet" href="/booking-system/css/login.css">
</head>
<body>
    <div class="container" style="max-width: 400px;">
        <h1>Member Login</h1>
        
        <?php if ($error): ?>
            <div class="error" style="background: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <button type="submit" class="get-started-btn" style="padding: 12px; font-size: 16px;">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #666;">← Back to Home</a>
        </div>
    </div>
</body>
</html>