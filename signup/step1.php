<?php
session_start();
require_once '../includes/db_connect.php';

// Clear any existing session data for new signup
if (!isset($_SESSION['step1_complete'])) {
    $_SESSION['signup'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $company = $_POST['company'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // ADD THESE LINES - Get checkbox values
    $is_returning = isset($_POST['is_returning']) ? 1 : 0;
    $needs_training = isset($_POST['needs_training']) ? 1 : 0;
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;
    
    $error = '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email and password are required!';
    } elseif (!$terms_accepted) {
        $error = 'You must accept the terms and services!';
    } else {
        // Store in session - ADD checkbox values
        $_SESSION['signup']['name'] = $name;
        $_SESSION['signup']['email'] = $email;
        $_SESSION['signup']['password'] = $password;
        $_SESSION['signup']['company'] = $company;
        $_SESSION['signup']['phone'] = $phone;
        $_SESSION['signup']['address'] = $address;
        
        // ADD THESE LINES - Store checkbox values
        $_SESSION['signup']['is_returning'] = $is_returning;
        $_SESSION['signup']['needs_training'] = $needs_training;
        $_SESSION['signup']['terms_accepted'] = $terms_accepted;
        
        $_SESSION['step1_complete'] = true;
        
        header('Location: step2.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Title</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
    <div class="container">
        <h1>📋 Create Your Account</h1>
        
        <div class="step-indicator">
            <div class="step active">1. Account</div>
            <div class="step">2. Membership</div>
            <div class="step">3. Experience</div>
            <div class="step">4. Safety</div>
            <div class="step">5. Review</div>
        </div>
        
        <?php if (isset($error) && $error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Company/Organisation <span class="optional">(optional)</span></label>
                <input type="text" name="company">
            </div>
            
            <div class="form-group">
                <label>Phone <span class="optional">(optional)</span></label>
                <input type="text" name="phone">
            </div>
            
            <div class="form-group">
                <label>Address <span class="optional">(optional)</span></label>
                <textarea name="address" rows="3"></textarea>
            </div>
            
            <!-- ADD THIS SECTION - Your checkboxes -->
            <div class="checkbox-group">
                <h3>Membership Options</h3>
                
                <div class="checkbox">
                    <input type="checkbox" name="is_returning" id="is_returning" value="1">
                    <label for="is_returning">Are you a returning member?</label>
                </div>
                
                <div class="checkbox">
                    <input type="checkbox" name="needs_training" id="needs_training" value="1">
                    <label for="needs_training">Do you need training?</label>
                </div>
                
                <div class="checkbox">
                    <input type="checkbox" name="terms_accepted" id="terms_accepted" required>
                    <label for="terms_accepted">I accept the <a href="../terms.html" target="_blank">Terms and Services</a> <span class="required">*</span></label>
                </div>
            </div>
            
            <button type="submit" class="btn">Continue →</button>
        </form>
        
        <a href="../public/login.php" class="back-link">Already have an account? Login</a>
    </div>
</body>
</html>