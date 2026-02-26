<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
requireLogin();

$user_id = getCurrentUserId();

// Get user data
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Initialize reactivation session
$_SESSION['reactivation'] = [
    'step' => 1,
    'user_id' => $user_id,
    'original_tier' => $prefs['tier_id'] ?? 1,
    'original_machines' => []
];

// Get original machines
$machines_result = $conn->query("SELECT area_name, skill_level FROM user_areas WHERE user_id = $user_id");
while($machine = $machines_result->fetch_assoc()) {
    $_SESSION['reactivation']['original_machines'][] = $machine;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reactivation']['step'] = 2;
    header('Location: step2.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivate Membership - Step 1</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .reactivate-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            color: #999;
            position: relative;
        }
        .step.active {
            color: #ff9800;
            font-weight: bold;
        }
        .step.active::before {
            content: '●';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: #ff9800;
            font-size: 20px;
        }
        .step.completed {
            color: #4caf50;
        }
        .confirm-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .detail-label {
            width: 120px;
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            flex: 1;
            color: #333;
        }
        .confirm-check {
            color: #4caf50;
            font-size: 1.2em;
            margin-right: 10px;
        }
        .btn-continue {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
            transition: all 0.3s;
        }
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 152, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="reactivate-container">
        <div class="step-indicator">
            <div class="step active">1. Confirm Details</div>
            <div class="step">2. Choose Tier</div>
            <div class="step">3. Update Machines</div>
            <div class="step">4. Confirm</div>
        </div>

        <div class="confirm-card">
            <h2 style="color: #ff9800; margin-bottom: 30px;">📋 Confirm Your Details</h2>
            
            <p style="color: #666; margin-bottom: 25px;">Please verify your information is still correct:</p>
            
            <form method="POST">
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['name']); ?></span>
                    <span class="confirm-check">✓</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    <span class="confirm-check">✓</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Company:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['company'] ?? 'Not provided'); ?></span>
                    <span class="confirm-check">✓</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                    <span class="confirm-check">✓</span>
                </div>
                
                <button type="submit" class="btn-continue">Continue →</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="../member/inactive_dashboard.php" style="color: #666;">← Back</a>
            </div>
        </div>
    </div>
</body>
</html>