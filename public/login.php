<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';  // ADD THIS LINE

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$error = '';

if (isLoggedIn()) {
    redirect('../member/dashboard.php');  // Now this works!
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
            redirect('../member/dashboard.php');  // Using the function
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