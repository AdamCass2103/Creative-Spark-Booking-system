<?php
$conn = new mysqli('localhost', 'root', '', 'booking_system');
$user_id = $_GET['id'] ?? 0;

// Get user details
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
$tier = $conn->query("SELECT tier_name FROM membership_tiers WHERE tier_id = " . ($prefs['tier_id'] ?? 1))->fetch_assoc();
$areas = $conn->query("SELECT area_name FROM user_areas WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        h1 { color: #9c27b0; }
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #1a73e8;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .info-row {
            margin: 10px 0;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            width: 140px;
            color: #666;
        }
        .info-value {
            flex: 1;
        }
        .areas-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .area-tag {
            background: #e3f2fd;
            color: #0d47a1;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #757575;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .back-btn:hover { background: #616161; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👤 User Profile: <?php echo $user['name']; ?></h1>
        </div>
        
        <div class="profile-grid">
            <div class="card">
                <h2>📋 Personal Details</h2>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo $user['name']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo $user['email']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value"><?php echo $user['company'] ?? 'Not provided'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo $user['phone'] ?? 'Not provided'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?php echo nl2br($user['address'] ?? 'Not provided'); ?></span>
                </div>
            </div>
            
            <div class="card">
                <h2>🎫 Membership</h2>
                <div class="info-row">
                    <span class="info-label">Tier:</span>
                    <span class="info-value"><?php echo $tier['tier_name'] ?? 'Not selected'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment:</span>
                    <span class="info-value"><?php echo ucfirst($prefs['payment_type'] ?? 'monthly'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Returning Member:</span>
                    <span class="info-value"><?php echo $prefs['is_returning_member'] ? 'Yes' : 'No'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Needs Training:</span>
                    <span class="info-value"><?php echo $prefs['needs_training'] ? 'Yes' : 'No'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value status-<?php echo $prefs['training_status']; ?>">
                        <?php echo ucfirst($prefs['training_status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="card">
                <h2>🔧 Selected Areas</h2>
                <div class="areas-list">
                    <?php if ($areas->num_rows > 0): ?>
                        <?php while($area = $areas->fetch_assoc()): ?>
                            <span class="area-tag"><?php echo $area['area_name']; ?></span>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No areas selected</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <h2>📝 Experience</h2>
                <p><strong>Experience:</strong></p>
                <p><?php echo nl2br($prefs['experience_text'] ?? 'Not provided'); ?></p>
                <p><strong>Work description:</strong></p>
                <p><?php echo nl2br($prefs['work_description'] ?? 'Not provided'); ?></p>
            </div>
            
            <div class="card" style="grid-column: span 2;">
                <h2>⚠️ Safety & Agreements</h2>
                <div class="info-row">
                    <span class="info-label">Terms Accepted:</span>
                    <span class="info-value"><?php echo $prefs['terms_accepted'] ? '✅ Yes' : '❌ No'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Signature:</span>
                    <span class="info-value"><?php echo $user['signature'] ?? 'Not signed'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Signed Date:</span>
                    <span class="info-value"><?php echo $user['signed_date'] ?? 'Not dated'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Joined:</span>
                    <span class="info-value"><?php echo $user['created_at']; ?></span>
                </div>
            </div>
        </div>
        
        <a href="admin.php" class="back-btn">← Back to Admin Panel</a>
    </div>
</body>
</html>