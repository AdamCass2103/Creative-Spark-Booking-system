<?php
require_once '../includes/auth.php';
requireLogin();

$user_id = getCurrentUserId();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Get user data
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Get tier name if exists
$tier_name = 'Fabber 2'; // Default
if ($prefs && isset($prefs['tier_id'])) {
    $tier_result = $conn->query("SELECT tier_name FROM membership_tiers WHERE tier_id = " . $prefs['tier_id']);
    if ($tier_result && $tier_result->num_rows > 0) {
        $tier_name = $tier_result->fetch_assoc()['tier_name'];
    }
}

// Get last activity from users table - FIXED
$last_active = $user['last_activity'] ?? 'Unknown';
if ($last_active != 'Unknown') {
    $last_active = date('F Y', strtotime($last_active));
}

// Get expiry date
$expiry = isset($user['membership_expiry']) ? date('F Y', strtotime($user['membership_expiry'])) : 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Inactive - Creative Spark</title>
    <link rel="stylesheet" href="../css/member-dashboard.css">
    <style>
        /* Inactive Dashboard Specific Styles */
        .inactive-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .inactive-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid #ff9800;
        }
        
        .warning-icon {
            font-size: 5em;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .warning-title {
            color: #ff9800;
            font-size: 2em;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .info-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid #2E7D32;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .restrictions-list {
            background: #fff3e0;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .restrictions-list h3 {
            color: #e65100;
            margin-bottom: 15px;
        }
        
        .restrictions-list ul {
            list-style: none;
            padding: 0;
        }
        
        .restrictions-list li {
            padding: 8px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .restrictions-list li::before {
            content: "❌";
            color: #f44336;
            font-size: 1.1em;
        }
        
        .reactivate-btn {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-size: 1.3em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin: 30px 0 20px;
            box-shadow: 0 5px 20px rgba(255, 152, 0, 0.3);
            width: 100%;
        }
        
        .reactivate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 152, 0, 0.4);
        }
        
        .help-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .help-link {
            color: #666;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .help-link:hover {
            color: #ff9800;
        }
        
        .profile-summary {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 10px;
        }
        
        .profile-icon {
            font-size: 3em;
        }
    </style>
</head>
<body>
    <div class="inactive-container">
        <div class="inactive-card">
            <div class="warning-icon">⚠️</div>
            <div class="warning-title">Membership Expired</div>
            
            <div class="profile-summary">
                <div class="profile-icon">👤</div>
                <div style="text-align: left;">
                    <h2 style="color: #2E7D32;">Welcome back, <?php echo htmlspecialchars($user['name']); ?></h2>
                    <p style="color: #666;">We've missed you at Creative Spark!</p>
                </div>
            </div>
            
            <div class="info-box">
                <h3 style="color: #2E7D32; margin-bottom: 15px;">📋 Membership Status</h3>
                <div class="info-row">
                    <span class="info-label">Last Active:</span>
                    <span class="info-value"><?php echo $last_active; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Previous Tier:</span>
                    <span class="info-value"><?php echo $tier_name; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Membership Expired:</span>
                    <span class="info-value"><?php echo $expiry; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member Since:</span>
                    <span class="info-value"><?php echo date('F Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
            
            <div class="restrictions-list">
                <h3>⛔ While inactive, you cannot:</h3>
                <ul>
                    <li>Book training sessions</li>
                    <li>Request machine time</li>
                    <li>Access member resources</li>
                    <li>Book equipment</li>
                    <li>Attend workshops</li>
                </ul>
            </div>
            
            <button onclick="window.location.href='../reactivate/step1.php'" class="reactivate-btn">
                🔄 REACTIVATE MEMBERSHIP →
            </button>
            
            <div style="color: #666; font-size: 0.9em; margin: 20px 0;">
                Your profile and training history are safely stored.<br>
                Reactivating takes just 2 minutes.
            </div>
            
            <div class="help-links">
                <a href="contact_oscar.php" class="help-link">📧 Contact Oscar</a>
                <a href="faq.php" class="help-link">❓ FAQ</a>
                <a href="logout.php" class="help-link" style="color: #f44336;">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>
</html>