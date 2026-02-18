<?php
require_once '../includes/auth.php';  // ✅ Correct path
session_start();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

$error = '';

if (isLoggedIn()) {
    redirect('../member/dashboard.php');  // ✅ Fixed path
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
            header('Location: ../member/dashboard.php');  // ✅ Fixed
            exit();
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
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; text-align: center; }
        .form-group { margin: 20px 0; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .error { background: #fee; color: #c00; padding: 10px; border-radius: 5px; }
        .link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Member Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="link">
            <a href="../public/index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>