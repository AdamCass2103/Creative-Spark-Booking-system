<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/admin_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        // Get admin user
        $stmt = $conn->prepare("SELECT admin_id, name, username, password_hash, role FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($admin = $result->fetch_assoc()) {
            // Simple password check (you should hash these properly)
            if ($password === $admin['password_hash']) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_logged_in'] = true;
                
                // Update last login
                $conn->query("UPDATE admin_users SET last_login = NOW() WHERE admin_id = " . $admin['admin_id']);
                
                // Log the login
                logAdminActivity($admin['admin_id'], 'login', 'admin', $admin['admin_id'], 'Logged in');
                
                header('Location: admin.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Invalid username';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Access - Creative Spark FabLab</title>
    <link rel="stylesheet" href="../css/adminlogin.css">
</head>
<body>
    <div class="container">
        <h1>👨‍💼 Staff Access</h1>
        <div class="sub">For FabLab team only</div>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            
            <button type="submit" class="btn">Access Admin Panel</button>
        </form>
        
        <div class="back-link">
            <a href="../public/index.php">← Back to main page</a>
        </div>
    </div>
</body>
</html>