<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $is_returning = isset($_POST['is_returning']) ? 1 : 0;
    $needs_training = isset($_POST['needs_training']) ? 1 : 0;
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields required!';
    } elseif (!$terms_accepted) {
        $error = 'You must accept terms!';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed')");
        $user_id = $conn->insert_id;
        $conn->query("INSERT INTO user_preferences (user_id, is_returning_member, needs_training, terms_accepted) VALUES ($user_id, $is_returning, $needs_training, $terms_accepted)");
        $success = 'Account created! <a href="admin.php">Go to Admin Panel</a>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Booking System</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #1a73e8; margin-bottom: 20px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .checkbox { margin: 10px 0; }
        .btn { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 5px; font-size: 16px; }
        .error { background: #fee; color: #c00; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { background: #dfd; color: #080; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <div class="checkbox">
                <label><input type="checkbox" name="is_returning"> Are you a returning member?</label>
            </div>
            <div class="checkbox">
                <label><input type="checkbox" name="needs_training"> Do you need training?</label>
            </div>
            <div class="checkbox">
                <label><input type="checkbox" name="terms_accepted" required> I accept the <a href="terms.html" target="_blank">Terms and Services</a></label>
            </div>
            
            <button type="submit" class="btn">Sign Up</button>
        </form>
        
        <p style="text-align:center; margin-top:20px;">
            <a href="dashboard.php">Go to Dashboard</a>
        </p>
    </div>
</body>
<?php
// Check if we should use the new multi-step or old signup
if (isset($_GET['legacy'])) {
    // Show old signup
    include 'index.php';
} else {
    // Redirect to new multi-step
    header('Location: signup/step1.php');
    exit();
}
?>
</html>