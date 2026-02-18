<?php
session_start();
require_once '../includes/db_connect.php';

$error = '';

// Simple hardcoded admin password (Oscar can change it)
define('ADMIN_PASSWORD', 'fablab2026'); // Change this!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit();
    } else {
        $error = 'Invalid admin code';
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
        <div class="sub">For Oscar and FabLab team only</div>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Admin Code</label>
                <input type="password" name="password" required placeholder="Enter admin code">
            </div>
            
            <button type="submit" class="btn">Access Admin Panel</button>
        </form>
        
        <div class="back-link">
            <a href="../public/index.php">← Back to main page</a>
        </div>
    </div>
</body>
</html>