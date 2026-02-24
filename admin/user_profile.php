<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$user_id = $_GET['id'] ?? 0;

// Get user details with error handling
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
if (!$user) {
    header('Location: admin.php?error=User not found');
    exit();
}

$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
$tier = $conn->query("SELECT tier_name FROM membership_tiers WHERE tier_id = " . ($prefs['tier_id'] ?? 1))->fetch_assoc();
$areas = $conn->query("SELECT area_name, skill_level FROM user_areas WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?php echo htmlspecialchars($user['name']); ?></title>
    <link rel="stylesheet" href="../css/user_profile.css">
    <style>
        /* Additional styles for skill badges */
        .skill-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .skill-badge.beginner {
            background: #ff9800;
            color: white;
        }
        .skill-badge.intermediate {
            background: #2196f3;
            color: white;
        }
        .skill-badge.expert {
            background: #4caf50;
            color: white;
        }
        .area-tag {
            display: inline-flex;
            align-items: center;
            background: #e8f5e9;
            color: #2E7D32;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.9em;
            font-weight: 500;
            margin: 5px;
        }
        .work-description-box {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #2E7D32;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <span class="user-id">🔖 ID: #<?php echo str_pad($user['user_id'], 4, '0', STR_PAD_LEFT); ?></span>
            <h1>👤 <?php echo htmlspecialchars($user['name']); ?></h1>
            <div class="user-meta">
                <span>📧 <?php echo $user['email']; ?></span>
                <span>📅 Joined <?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                <span>⏰ <?php echo timeAgo($user['created_at']); ?></span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎫</div>
                <div class="stat-value"><?php echo $tier['tier_name'] ?? 'N/A'; ?></div>
                <div class="stat-label">Membership Tier</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💳</div>
                <div class="stat-value"><?php echo ucfirst($prefs['payment_type'] ?? 'Monthly'); ?></div>
                <div class="stat-label">Payment Plan</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-value"><?php echo $areas->num_rows; ?></div>
                <div class="stat-label">Machines Selected</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-value status-<?php echo $prefs['training_status']; ?>">
                    <?php echo ucfirst($prefs['training_status']); ?>
                </div>
                <div class="stat-label">Account Status</div>
            </div>
        </div>

        <!-- Profile Grid -->
        <div class="profile-grid">
            <!-- Personal Details -->
            <div class="card">
                <div class="card-header">
                    <span class="header-icon">📋</span>
                    <h2>Personal Details</h2>
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo $user['name']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo $user['email']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo $user['phone'] ?? 'Not provided'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Company</span>
                        <span class="info-value"><?php echo $user['company'] ?? 'Not provided'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address</span>
                        <span class="info-value"><?php echo nl2br($user['address'] ?? 'Not provided'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Membership Details -->
            <div class="card">
                <div class="card-header">
                    <span class="header-icon">🎫</span>
                    <h2>Membership Details</h2>
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Membership Tier</span>
                        <span class="info-value"><strong><?php echo $tier['tier_name'] ?? 'Not selected'; ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Type</span>
                        <span class="info-value"><?php echo ucfirst($prefs['payment_type'] ?? 'monthly'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Returning Member</span>
                        <span class="info-value">
                            <span class="status-badge <?php echo $prefs['is_returning_member'] ? 'status-approved' : 'status-pending'; ?>">
                                <?php echo $prefs['is_returning_member'] ? 'Yes' : 'No'; ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Training Required</span>
                        <span class="info-value">
                            <span class="status-badge <?php echo $prefs['needs_training'] ? 'status-approved' : 'status-completed'; ?>">
                                <?php echo $prefs['needs_training'] ? 'Yes' : 'No'; ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Account Status</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $prefs['training_status']; ?>">
                                <?php echo ucfirst($prefs['training_status']); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Selected Machines with Skill Levels - UPDATED -->
            <div class="card">
                <div class="card-header">
                    <span class="header-icon">🔧</span>
                    <h2>Selected Machines & Skill Levels</h2>
                </div>
                <?php if ($areas && $areas->num_rows > 0): ?>
                    <div class="areas-list">
                        <?php 
                        // Reset pointer to fetch again
                        $areas->data_seek(0);
                        while($area = $areas->fetch_assoc()): 
                        ?>
                            <span class="area-tag">
                                <?php echo $area['area_name']; ?>
                                <span class="skill-badge <?php echo $area['skill_level'] ?? 'beginner'; ?>">
                                    <?php echo ucfirst($area['skill_level'] ?? 'beginner'); ?>
                                </span>
                            </span>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">No machines selected yet</p>
                <?php endif; ?>
            </div>

            <!-- Work Description - UPDATED (removed experience) -->
            <div class="card">
                <div class="card-header">
                    <span class="header-icon">📝</span>
                    <h2>Work Description</h2>
                </div>
                <div class="work-description-box">
                    <?php if (!empty($prefs['work_description'])): ?>
                        <p><?php echo nl2br($prefs['work_description']); ?></p>
                    <?php else: ?>
                        <p style="color: #999; font-style: italic;">No work description provided</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Safety & Agreements -->
            <div class="card" style="grid-column: span 2;">
                <div class="card-header">
                    <span class="header-icon">⚠️</span>
                    <h2>Safety & Agreements</h2>
                </div>
                
                <div class="safety-grid">
                    <div class="safety-item <?php echo $prefs['terms_accepted'] ? 'yes' : 'no'; ?>">
                        <span style="font-size: 1.2em;"><?php echo $prefs['terms_accepted'] ? '✅' : '❌'; ?></span>
                        <div>
                            <strong>Terms Accepted</strong>
                            <br>
                            <small><?php echo $prefs['terms_accepted'] ? 'Yes' : 'No'; ?></small>
                        </div>
                    </div>
                    
                    <div class="safety-item <?php echo $user['signature'] ? 'yes' : 'no'; ?>">
                        <span style="font-size: 1.2em;">✍️</span>
                        <div>
                            <strong>Signature</strong>
                            <br>
                            <small><?php echo $user['signature'] ?? 'Not signed'; ?></small>
                        </div>
                    </div>
                    
                    <div class="safety-item <?php echo $user['signed_date'] ? 'yes' : 'no'; ?>">
                        <span style="font-size: 1.2em;">📅</span>
                        <div>
                            <strong>Signed Date</strong>
                            <br>
                            <small><?php echo $user['signed_date'] ?? 'Not dated'; ?></small>
                        </div>
                    </div>
                    
                    <div class="safety-item yes">
                        <span style="font-size: 1.2em;">🕐</span>
                        <div>
                            <strong>Member Since</strong>
                            <br>
                            <small><?php echo date('F j, Y g:i a', strtotime($user['created_at'])); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="admin.php" class="btn btn-primary">← Back to Admin Panel</a>
            <?php if($prefs['training_status'] == 'pending'): ?>
                <a href="admin.php?approve=<?php echo $user_id; ?>" class="btn btn-primary">✅ Approve Membership</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>