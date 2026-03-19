<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // Try this instead
requireLogin();

$user_id = getCurrentUserId();

// Use the database connection from functions.php
$conn = getDatabaseConnection();

$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In real system, you'd process payment here
    // For now, just mark as paid
    $conn->query("UPDATE users SET payment_status = 'paid', payment_date = CURDATE() WHERE user_id = $user_id");
    header('Location: dashboard.php?payment=success');
    exit();
}

$amount = $prefs['tier_id'] == 1 ? '100' : ($prefs['tier_id'] == 2 ? '200' : ($prefs['tier_id'] == 3 ? '500' : 'Custom'));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Payment</title>
    <link rel="stylesheet" href="../css/member-dashboard.css">
    <style>
        .payment-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .amount {
            font-size: 2.5em;
            font-weight: bold;
            color: #2E7D32;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h1 style="color: #2E7D32;">💳 Complete Payment</h1>
            
            <div class="amount">€<?php echo $amount; ?></div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p><strong>Plan:</strong> <?php echo $tier['tier_name'] ?? 'Fabber'; ?></p>
                <p><strong>Payment:</strong> <?php echo ucfirst($prefs['payment_type'] ?? 'monthly'); ?></p>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn-book" style="width: 100%; background: #ff9800;">
                    ✅ Confirm Payment
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 20px;">
                <a href="dashboard.php" style="color: #666;">← Cancel</a>
            </p>
        </div>
    </div>
</body>
</html>