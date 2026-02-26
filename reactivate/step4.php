<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
requireLogin();

if (!isset($_SESSION['reactivation']) || $_SESSION['reactivation']['step'] < 3) {
    header('Location: step1.php');
    exit();
}

$user_id = getCurrentUserId();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Get tier name
$tier_result = $conn->query("SELECT tier_name FROM membership_tiers WHERE tier_id = " . $_SESSION['reactivation']['new_tier']);
$tier_name = $tier_result->fetch_assoc()['tier_name'];

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        // Update user status to active
        $conn->query("UPDATE users SET account_status = 'active', last_activity = CURDATE() WHERE user_id = $user_id");
        
        // Update preferences with new tier
        $conn->query("UPDATE user_preferences SET tier_id = " . $_SESSION['reactivation']['new_tier'] . " WHERE user_id = $user_id");
        
        // Delete old machine selections
        $conn->query("DELETE FROM user_areas WHERE user_id = $user_id");
        
        // Insert new machine selections
        if (!empty($_SESSION['reactivation']['new_machines'])) {
            foreach ($_SESSION['reactivation']['new_machines'] as $machine) {
                $area = $conn->real_escape_string($machine['area']);
                $skill = $machine['skill'];
                $conn->query("INSERT INTO user_areas (user_id, area_name, skill_level) VALUES ($user_id, '$area', '$skill')");
            }
        }
        
        // Clear reactivation session
        unset($_SESSION['reactivation']);
        
        header('Location: complete.php');
        exit();
    } else {
        header('Location: ../member/dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivate - Confirmation</title>
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
        .step.completed {
            color: #4caf50;
        }
        .step.completed::before {
            content: '✓';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: #4caf50;
            font-size: 20px;
        }
        .step.active {
            color: #ff9800;
            font-weight: bold;
        }
        .confirm-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .machines-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        .machine-badge {
            background: #e8f5e9;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 0.9em;
        }
        .btn-confirm {
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
        }
        .btn-cancel {
            background: #f44336;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="reactivate-container">
        <div class="step-indicator">
            <div class="step completed">1. Confirm Details</div>
            <div class="step completed">2. Choose Tier</div>
            <div class="step completed">3. Update Machines</div>
            <div class="step active">4. Confirm</div>
        </div>

        <div class="confirm-card">
            <h2 style="color: #ff9800; margin-bottom: 30px;">📝 Review Your Reactivation</h2>
            
            <div class="summary-row">
                <span>Membership Tier:</span>
                <strong><?php echo $tier_name; ?></strong>
            </div>
            
            <div class="summary-row">
                <span>Start Date:</span>
                <strong><?php echo date('F j, Y'); ?></strong>
            </div>
            
            <div style="margin: 20px 0;">
                <h4 style="margin-bottom: 10px;">Selected Machines:</h4>
                <div class="machines-list">
                    <?php foreach($_SESSION['reactivation']['new_machines'] as $machine): ?>
                        <span class="machine-badge">
                            <?php echo $machine['area']; ?> (<?php echo ucfirst($machine['skill']); ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <form method="POST">
                <button type="submit" name="confirm" value="yes" class="btn-confirm">
                    ✓ Confirm Reactivation
                </button>
            </form>
            
            <form method="POST" style="text-align: center; margin-top: 15px;">
                <button type="submit" name="cancel" class="btn-cancel">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>