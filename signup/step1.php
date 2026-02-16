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
    <title>Sign Up - Step 1 of 5</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; margin-bottom: 10px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; margin: 0 5px; border-radius: 5px; }
        .step.active { background: #9c27b0; color: white; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="password"], textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;
        }
        .checkbox-group {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .checkbox {
            margin: 10px 0;
        }
        .checkbox label {
            font-weight: normal;
            display: inline;
            margin-left: 5px;
        }
        .btn { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #0d62d9; }
        .error { background: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .optional { color: #999; font-size: 12px; font-weight: normal; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #666; }
        .required { color: #c00; }
    </style>
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
        
        <a href="../dashboard.php" class="back-link">Already have an account? Login</a>
    </div>
</body>
</html>