<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Get user data if exists
$user_id = $_SESSION['user_id'] ?? 1;
$user_name = $_SESSION['user_name'] ?? 'Guest User';

// Get preferences
$prefs_result = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id");
$preferences = $prefs_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Booking System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { color: #1a73e8; margin-bottom: 15px; }
        .nav { display: flex; gap: 15px; flex-wrap: wrap; }
        .btn { padding: 12px 25px; background: #1a73e8; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; }
        .btn:hover { background: #0d62d9; }
        .admin-btn { background: #9c27b0; }
        .admin-btn:hover { background: #7b1fa2; }
        .create-btn { background: #28a745; }
        .create-btn:hover { background: #218838; }
        .logout-btn { background: #dc3545; }
        .logout-btn:hover { background: #c82333; }
        .dashboard-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h2 { color: #333; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .preferences p { padding: 15px; background: #f8f9fa; margin-bottom: 10px; border-radius: 6px; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-approved { color: #4caf50; font-weight: bold; }
        .status-rejected { color: #f44336; font-weight: bold; }
        .actions { margin-top: 20px; }
        .quick-nav { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; }
        .quick-nav h3 { margin-bottom: 15px; color: #333; }
        .nav-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .welcome-box { background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 4px solid #1a73e8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-box">
            <h2>Welcome to the Booking System Prototype</h2>
        </div>
        
        <div class="header">
            <h1>Dashboard</h1>
            <div class="nav">
                <a href="index.php" class="btn create-btn">➕ Create New User</a>
                <a href="admin.php" class="btn admin-btn">👨‍💼 Admin Panel</a>
                <a href="dashboard.php" class="btn">🔄 Refresh</a>
            </div>
        </div>
        
        <div class="quick-nav">
            <h3>Quick Actions:</h3>
            <div class="nav-buttons">
                <a href="index.php" class="btn create-btn">➕ Create New User</a>
                <a href="admin.php" class="btn admin-btn">👁️ View All Users</a>
                <a href="terms.html" class="btn" target="_blank">📄 View Terms</a>
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="card">
                <h2>📋 Current User Info</h2>
                <div class="preferences">
                    <?php if ($preferences): ?>
                    <p><strong>Name:</strong> <?php echo $user_name; ?></p>
                    <p><strong>Returning Member:</strong> <?php echo $preferences['is_returning_member'] ? '✅ Yes' : '❌ No'; ?></p>
                    <p><strong>Needs Training:</strong> <?php echo $preferences['needs_training'] ? '✅ Yes' : '❌ No'; ?></p>
                    <p><strong>Terms Accepted:</strong> <?php echo $preferences['terms_accepted'] ? '✅ Yes' : '❌ No'; ?></p>
                    <p><strong>Training Status:</strong> 
                        <span class="status-<?php echo $preferences['training_status']; ?>">
                            <?php echo ucfirst($preferences['training_status']); ?>
                        </span>
                    </p>
                    <?php else: ?>
                    <p>No user data yet. <a href="index.php">Create a new user first</a>.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <h2>🚀 Get Started</h2>
                <p><strong>To demo:</strong></p>
                <ol style="margin: 15px 0; padding-left: 20px;">
                    <li>Click <strong>"Create New User"</strong> to sign up</li>
                    <li>Fill form with 3 tick boxes</li>
                    <li>Click <strong>"Admin Panel"</strong> to view all users</li>
                    <li>Use filters and update training status</li>
                </ol>
                <div class="actions">
                    <a href="index.php" class="btn create-btn">➕ Create New User</a>
                    <a href="admin.php" class="btn admin-btn">👨‍💼 Go to Admin Panel</a>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>📊 System Stats</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background: #e8f5e8; padding: 15px; border-radius: 6px;">
                    <h3 style="color: #4caf50;">Total Users</h3>
                    <p style="font-size: 2em; font-weight: bold;">
                        <?php 
                        $count_result = $conn->query("SELECT COUNT(*) as count FROM users");
                        $count = $count_result->fetch_assoc()['count'];
                        echo $count;
                        ?>
                    </p>
                </div>
                <div style="background: #fff3cd; padding: 15px; border-radius: 6px;">
                    <h3 style="color: #ff9800;">Need Training</h3>
                    <p style="font-size: 2em; font-weight: bold;">
                        <?php 
                        $training_result = $conn->query("SELECT COUNT(*) as count FROM user_preferences WHERE needs_training = 1");
                        $training_count = $training_result->fetch_assoc()['count'];
                        echo $training_count;
                        ?>
                    </p>
                </div>
                <div style="background: #e3f2fd; padding: 15px; border-radius: 6px;">
                    <h3 style="color: #2196f3;">Pending Approval</h3>
                    <p style="font-size: 2em; font-weight: bold;">
                        <?php 
                        $pending_result = $conn->query("SELECT COUNT(*) as count FROM user_preferences WHERE training_status = 'pending'");
                        $pending_count = $pending_result->fetch_assoc()['count'];
                        echo $pending_count;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 14px;">
            <p>Prototype Demo • Booking System with MySQL Database</p>
        </div>
    </div>
</body>
</html>