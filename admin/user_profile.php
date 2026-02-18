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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/admin.css">
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