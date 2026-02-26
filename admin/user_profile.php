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

// Get membership history
$history = $conn->query("
    SELECT mh.*, mt.tier_name 
    FROM membership_history mh
    LEFT JOIN membership_tiers mt ON mh.tier_id = mt.tier_id
    WHERE mh.user_id = $user_id
    ORDER BY mh.start_date DESC
");
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
        
        /* History Section Styles */
        .history-card {
            grid-column: span 2;
            margin-top: 20px;
        }
        
        .history-timeline {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .history-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-item.current {
            background: #f0f7f0;
            border-radius: 10px;
            margin: 5px 0;
        }
        
        .history-icon {
            width: 40px;
            font-size: 1.5em;
            text-align: center;
        }
        
        .history-status {
            flex: 2;
            font-weight: 600;
        }
        
        .history-status.active { color: #4caf50; }
        .history-status.inactive { color: #f44336; }
        .history-status.reactivating { color: #ff9800; }
        
        .history-dates {
            flex: 3;
            color: #333;
        }
        
        .history-duration {
            color: #999;
            margin-left: 10px;
            font-size: 0.9em;
        }
        
        .history-notes {
            color: #666;
            font-style: italic;
            font-size: 0.9em;
            margin-left: 15px;
            flex: 2;
        }
        
        .history-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .history-badge.step {
            background: #ff9800;
            color: white;
        }
        
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-mini-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .stat-mini-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #2E7D32;
        }
        
        .stat-mini-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .action-button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            margin-right: 10px;
        }
        
        .action-button.force {
            background: #ff9800;
            color: white;
        }
        
        .action-button.archive {
            background: #f44336;
            color: white;
        }
        
        .action-button.history {
            background: #2196f3;
            color: white;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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

            <!-- Selected Machines with Skill Levels -->
            <div class="card">
                <div class="card-header">
                    <span class="header-icon">🔧</span>
                    <h2>Selected Machines & Skill Levels</h2>
                </div>
                <?php if ($areas && $areas->num_rows > 0): ?>
                    <div class="areas-list">
                        <?php 
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

            <!-- Work Description -->
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

        <!-- ============================================ -->
        <!-- MEMBERSHIP HISTORY SECTION - NEW -->
        <!-- ============================================ -->
        
        <?php if ($history && $history->num_rows > 0): 
            // Calculate statistics
            $total_months = 0;
            $active_months = 0;
            $inactive_months = 0;
            
            $history->data_seek(0);
            while($period = $history->fetch_assoc()) {
                $start = strtotime($period['start_date']);
                $end = $period['end_date'] ? strtotime($period['end_date']) : time();
                $months = floor(($end - $start) / (30 * 24 * 60 * 60));
                $total_months += $months;
                
                if($period['status'] == 'active') $active_months += $months;
                if($period['status'] == 'inactive') $inactive_months += $months;
            }
            $history->data_seek(0);
        ?>
        
        <div class="history-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #2E7D32;">📊 Membership History</h2>
                <a href="user_history.php?id=<?php echo $user_id; ?>" class="btn" style="background: #2196f3;">View Full Timeline</a>
            </div>
            
            <!-- Mini Stats -->
            <div class="stats-mini">
                <div class="stat-mini-card">
                    <div class="stat-mini-number"><?php echo $total_months; ?></div>
                    <div class="stat-mini-label">Total Months</div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-number"><?php echo $active_months; ?></div>
                    <div class="stat-mini-label">Active Months</div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-number"><?php echo $inactive_months; ?></div>
                    <div class="stat-mini-label">Inactive Months</div>
                </div>
            </div>
            
            <!-- History Timeline -->
            <div class="history-timeline">
                <?php while($period = $history->fetch_assoc()): 
                    $start = date('M Y', strtotime($period['start_date']));
                    $end = $period['end_date'] ? date('M Y', strtotime($period['end_date'])) : 'Present';
                    
                    $start_ts = strtotime($period['start_date']);
                    $end_ts = $period['end_date'] ? strtotime($period['end_date']) : time();
                    $months = floor(($end_ts - $start_ts) / (30 * 24 * 60 * 60));
                    
                    $status_icon = [
                        'active' => '🟢',
                        'inactive' => '🔴',
                        'reactivating' => '🟡'
                    ][$period['status']];
                    
                    $is_current = !$period['end_date'];
                ?>
                <div class="history-item <?php echo $is_current ? 'current' : ''; ?>">
                    <div class="history-icon"><?php echo $status_icon; ?></div>
                    
                    <div class="history-status <?php echo $period['status']; ?>">
                        <strong><?php echo ucfirst($period['status']); ?></strong>
                        <?php if($period['tier_name']): ?>
                            <span style="color: #666; margin-left: 8px; font-size: 0.9em;">
                                <?php echo $period['tier_name']; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="history-dates">
                        <?php echo $start; ?> - <?php echo $end; ?>
                        <span class="history-duration">(<?php echo $months; ?> months)</span>
                    </div>
                    
                    <?php if($period['notes']): ?>
                        <div class="history-notes"><?php echo $period['notes']; ?></div>
                    <?php endif; ?>
                    
                    <?php if($period['status'] == 'reactivating' && $user['reactivation_step'] > 0): ?>
                        <span class="history-badge step">Step <?php echo $user['reactivation_step']; ?>/4</span>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Admin Action Buttons -->
            <div style="display: flex; gap: 15px; margin-top: 20px; justify-content: flex-end;">
                <?php 
                // Check if user is reactivating
                $is_reactivating = false;
                $history->data_seek(0);
                while($period = $history->fetch_assoc()) {
                    if($period['status'] == 'reactivating' && !$period['end_date']) {
                        $is_reactivating = true;
                        break;
                    }
                }
                ?>
                
                <?php if($is_reactivating): ?>
                    <button onclick="forceReactivate(<?php echo $user_id; ?>)" class="action-button force">
                        🔄 Force Reactivate
                    </button>
                <?php endif; ?>
                
                <button onclick="archiveUser(<?php echo $user_id; ?>)" class="action-button archive">
                    📦 Archive Account
                </button>
                
                <a href="user_history.php?id=<?php echo $user_id; ?>" class="action-button history">
                    📜 Full History
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="admin.php" class="btn btn-primary">← Back to Admin Panel</a>
            <?php if($prefs['training_status'] == 'pending'): ?>
                <a href="admin.php?approve=<?php echo $user_id; ?>" class="btn btn-primary">✅ Approve Membership</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function forceReactivate(userId) {
        if(confirm('Force reactivate this membership? This will override the current reactivation process.')) {
            window.location.href = 'force_reactivate.php?id=' + userId;
        }
    }
    
    function archiveUser(userId) {
        if(confirm('Archive this account? This will mark it as permanently archived.')) {
            window.location.href = 'archive_user.php?id=' + userId;
        }
    }
    </script>
</body>
</html>