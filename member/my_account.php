<?php
require_once '../includes/auth.php';
requireLogin();

$user_id = getCurrentUserId();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Get current user data
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update personal details
    if (isset($_POST['update_details'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $company = $conn->real_escape_string($_POST['company']);
        $address = $conn->real_escape_string($_POST['address']);
        
        $conn->query("UPDATE users SET 
                      name = '$name',
                      email = '$email',
                      phone = '$phone',
                      company = '$company',
                      address = '$address'
                      WHERE user_id = $user_id");
        
        $message = "Profile updated successfully!";
        $message_type = "success";
        
        // Refresh user data
        $user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current, $user['password'])) {
            if ($new === $confirm) {
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password = '$new_hash' WHERE user_id = $user_id");
                $message = "Password changed successfully!";
                $message_type = "success";
            } else {
                $message = "New passwords do not match!";
                $message_type = "error";
            }
        } else {
            $message = "Current password is incorrect!";
            $message_type = "error";
        }
    }
    
    // Update membership tier
    if (isset($_POST['update_tier'])) {
        $new_tier = $_POST['tier_id'];
        $payment_type = $_POST['payment_type'];
        
        $conn->query("UPDATE user_preferences SET 
                      tier_id = '$new_tier',
                      payment_type = '$payment_type'
                      WHERE user_id = $user_id");
        
        $message = "Membership updated successfully!";
        $message_type = "success";
        
        // Refresh prefs
        $prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
    }
}

// Get tiers for dropdown
$tiers = $conn->query("SELECT * FROM membership_tiers ORDER BY tier_level");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Creative Spark</title>
    <link rel="stylesheet" href="../css/my_account.css">
</head>
<body>
    <div class="account-container">
        <div class="account-header">
            <h1>👤 My Account</h1>
            <a href="dashboard.php" class="btn back-btn">← Back to Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="account-grid">
            <!-- Personal Details Card -->
            <div class="account-card">
                <h2>📋 Personal Details</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Company/Organisation</label>
                        <input type="text" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_details" class="btn-save">
                        Save Changes
                    </button>
                </form>
            </div>
            
            <!-- Password Card -->
            <div class="account-card">
                <h2>🔐 Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <div class="password-requirements">
                        <strong>Password must:</strong>
                        <ul style="margin-top: 5px; margin-left: 20px;">
                            <li>Be at least 8 characters long</li>
                            <li>Include at least one number</li>
                            <li>Include at least one capital letter</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-save">
                        Update Password
                    </button>
                </form>
            </div>
            
            <!-- Membership Card -->
            <div class="account-card">
                <h2>🎫 Membership Details</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Membership Tier</label>
                        <select name="tier_id">
                            <?php 
                            $tiers->data_seek(0);
                            while($tier = $tiers->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $tier['tier_id']; ?>" 
                                <?php echo ($prefs['tier_id'] == $tier['tier_id']) ? 'selected' : ''; ?>>
                                <?php echo $tier['tier_name']; ?> - 
                                €<?php echo $tier['tier_id'] == 1 ? '100' : ($tier['tier_id'] == 2 ? '200' : ($tier['tier_id'] == 3 ? '500' : 'Custom')); ?>/month
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type">
                            <option value="monthly" <?php echo ($prefs['payment_type'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                            <option value="annual" <?php echo ($prefs['payment_type'] == 'annual') ? 'selected' : ''; ?>>Annual (10% discount)</option>
                        </select>
                    </div>
                    
                    <div class="info-text">
                        Changing your membership tier will take effect from your next billing cycle.
                    </div>
                    
                    <button type="submit" name="update_tier" class="btn-save" style="margin-top: 15px;">
                        Update Membership
                    </button>
                </form>
            </div>
            
            <!-- Account Info Card -->
            <div class="account-card">
                <h2>📊 Account Information</h2>
                <div style="padding: 10px 0;">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                        <span style="color: #666;">Member Since:</span>
                        <span style="font-weight: 500;"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                        <span style="color: #666;">Account Status:</span>
                        <span style="color: #4caf50; font-weight: 500;">Active</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                        <span style="color: #666;">Training Status:</span>
                        <span class="status-<?php echo $prefs['training_status']; ?>">
                            <?php echo ucfirst($prefs['training_status']); ?>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span style="color: #666;">Signature on file:</span>
                        <span><?php echo $user['signature'] ? '✅ Yes' : '❌ No'; ?></span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
                    <a href="logout.php" style="color: #f44336; text-decoration: none;">🚪 Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>