<?php
require_once '../includes/auth.php';
requireLogin();

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();

// Get user data from database
$conn = new mysqli('localhost', 'root', '', 'booking_system');
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        h1 { color: #9c27b0; }
        .welcome { color: #666; }
        .logout { float: right; background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h2 { color: #1a73e8; margin-bottom: 15px; }
        .info-row { margin: 10px 0; padding: 5px 0; border-bottom: 1px solid #eee; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-approved { color: #4caf50; font-weight: bold; }
        .status-completed { color: #2196f3; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="logout.php" class="logout">Logout</a>
            <h1>👋 Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="welcome">Member since: <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h2>📋 Your Profile</h2>
                <div class="info-row"><strong>Email:</strong> <?php echo $user['email']; ?></div>
                <div class="info-row"><strong>Phone:</strong> <?php echo $user['phone'] ?? 'Not provided'; ?></div>
                <div class="info-row"><strong>Company:</strong> <?php echo $user['company'] ?? 'Not provided'; ?></div>
            </div>
            
            <div class="card">
                <h2>🎫 Membership</h2>
                <div class="info-row"><strong>Returning Member:</strong> <?php echo $prefs['is_returning_member'] ? 'Yes' : 'No'; ?></div>
                <div class="info-row"><strong>Training Required:</strong> <?php echo $prefs['needs_training'] ? 'Yes' : 'No'; ?></div>
                <div class="info-row">
                    <strong>Status:</strong> 
                    <span class="status-<?php echo $prefs['training_status']; ?>">
                        <?php echo ucfirst($prefs['training_status']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>