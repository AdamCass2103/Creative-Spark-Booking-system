<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
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

// Determine account status
$account_status = $user['account_status'] ?? 'unknown';
$status_color = [
    'active' => '#4caf50',
    'inactive' => '#f44336',
    'reactivating' => '#ff9800',
    'unknown' => '#999'
][$account_status];

$status_icon = [
    'active' => '🟢',
    'inactive' => '🔴',
    'reactivating' => '🟡',
    'unknown' => '⚪'
][$account_status];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?php echo htmlspecialchars($user['name']); ?></title>
    <link rel="stylesheet" href="../css/user_profile.css">
</head>
<body>
    <div class="container">
        <!-- Account Status Banner - NEW -->
        <div class="status-banner">
            <div class="status-banner-left">
                <span class="status-banner-icon"><?php echo $status_icon; ?></span>
                <span class="status-banner-text">
                    Account Status: <strong><?php echo ucfirst($account_status); ?></strong>
                </span>
            </div>
            <div class="status-banner-badge">
                <?php 
                if ($account_status == 'active') echo '✅ Active Member';
                elseif ($account_status == 'inactive') echo '⏳ Inactive';
                elseif ($account_status == 'reactivating') echo '🔄 Reactivating';
                else echo '⚪ Unknown';
                ?>
            </div>
        </div>
        
        <!-- Inactive Notice with Quick Reactivate Button -->
        <?php if ($account_status == 'inactive'): ?>
        <div class="inactive-notice">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #f44336; font-size: 1.1em;">⏳ Account Inactive</strong>
                    <p style="color: #666; margin-top: 5px;">
                        Inactive since: <?php echo $user['inactive_since'] ? date('F j, Y', strtotime($user['inactive_since'])) : 'Unknown'; ?>
                    </p>
                </div>
                <a href="../reactivate/step1.php" class="reactivate-quick-btn">
                    🔄 Quick Reactivate
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Reactivating Notice -->
        <?php if ($account_status == 'reactivating'): ?>
        <div class="inactive-notice" style="border-left-color: #ff9800;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #ff9800; font-size: 1.1em;">🔄 Account Reactivating</strong>
                    <p style="color: #666; margin-top: 5px;">
                        Reactivation started: <?php echo $user['reactivation_started'] ? date('F j, Y', strtotime($user['reactivation_started'])) : 'Unknown'; ?>
                        | Step: <?php echo $user['reactivation_step'] ?? 1; ?>/4
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

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
                <div class="stat-label">Application Status</div>
            </div>
        </div>

        <!-- Profile Grid (rest of your existing code remains the same) -->
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
            <span class="info-label">Application Status</span>
            <span class="info-value">
                <span class="status-badge status-<?php echo $prefs['training_status']; ?>">
                    <?php echo ucfirst($prefs['training_status']); ?>
                </span>
            </span>
        </div>
        
        <!-- NEW: Payment Status for Admin -->
        <div class="info-row" style="border-top: 2px dashed #2E7D32; padding-top: 15px; margin-top: 10px;">
            <span class="info-label">💰 Payment Status</span>
            <span class="info-value">
                <?php 
                $payment_status = $user['payment_status'] ?? 'pending';
                $payment_amount = $user['payment_amount'] ?? ($prefs['tier_id'] == 1 ? '100' : ($prefs['tier_id'] == 2 ? '200' : ($prefs['tier_id'] == 3 ? '500' : 'Custom')));
                
                if ($payment_status == 'paid'): ?>
                    <span style="color: #4caf50; font-weight: bold;">✅ Paid</span>
                    <?php if ($user['payment_date']): ?>
                        <br><small>Paid on: <?php echo date('F j, Y', strtotime($user['payment_date'])); ?></small>
                    <?php endif; ?>
                <?php elseif ($prefs['training_status'] == 'approved'): ?>
                    <span style="color: #ff9800; font-weight: bold;">⏳ Awaiting Payment</span>
                    <br><small>Amount: €<?php echo $payment_amount; ?></small>
                    <?php if ($user['payment_due']): ?>
                        <br><small>Due: <?php echo date('F j, Y', strtotime($user['payment_due'])); ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color: #999;">Not applicable (pending approval)</span>
                <?php endif; ?>
            </span>
        </div>
        
        <!-- Admin Action - Mark as Paid (only if approved and not paid) -->
        <?php if ($prefs['training_status'] == 'approved' && $payment_status != 'paid'): ?>
        <div style="margin-top: 15px;">
            <form method="POST" action="mark_paid.php">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <button type="submit" class="btn" style="background: #4caf50; width: 100%;">
                    💰 Mark as Paid
                </button>
            </form>
        </div>
        <?php endif; ?>
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

        <!-- Membership History Section -->
<?php if ($history && $history->num_rows > 0): 
    // Calculate statistics
    $total_months = 0;
    $active_months = 0;
    $inactive_months = 0;
    
    $history->data_seek(0);
    while($period = $history->fetch_assoc()) {
        $start = strtotime($period['start_date']);
        $end = $period['end_date'] ? strtotime($period['end_date']) : time();
        
        // Calculate months more accurately
        $start_year = date('Y', $start);
        $start_month = date('n', $start);
        $end_year = date('Y', $end);
        $end_month = date('n', $end);
        
        $months = (($end_year - $start_year) * 12) + ($end_month - $start_month);
        $months = max(1, $months); // At least 1 month
        
        $total_months += $months;
        
        if($period['status'] == 'active') $active_months += $months;
        if($period['status'] == 'inactive') $inactive_months += $months;
    }
    $history->data_seek(0);
?>

<div class="history-card">
    <h2 style="color: #2E7D32; margin-bottom: 20px;">📊 Membership History</h2>
    
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
            
            // Calculate months for display
            $start_year = date('Y', $start_ts);
            $start_month = date('n', $start_ts);
            $end_year = date('Y', $end_ts);
            $end_month = date('n', $end_ts);
            
            $months = (($end_year - $start_year) * 12) + ($end_month - $start_month);
            $months = max(1, $months);
            
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
                <span class="history-duration">(<?php echo $months; ?> month<?php echo $months > 1 ? 's' : ''; ?>)</span>
            </div>
            
            <?php if($period['notes']): ?>
                <div class="history-notes"><?php echo $period['notes']; ?></div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
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
</body>
</html>