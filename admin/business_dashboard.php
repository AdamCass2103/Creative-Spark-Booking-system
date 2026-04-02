<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

// Check if user has analyst or admin access
$admin_role = getCurrentAdminRole();
if ($admin_role !== 'analyst' && $admin_role !== 'admin') {
    die("Access denied. Business dashboard requires analyst or admin access.");
}

$conn = getDatabaseConnection();

// ============================================
// FINANCIAL METRICS
// ============================================

// Total revenue (all time)
$total_revenue = $conn->query("SELECT SUM(total) as total FROM payments WHERE status = 'paid'")->fetch_assoc();
$total_revenue_amount = $total_revenue['total'] ?? 0;

// Revenue this month
$first_day_month = date('Y-m-01');
$monthly_revenue = $conn->query("SELECT SUM(total) as total FROM payments WHERE status = 'paid' AND paid_at >= '$first_day_month'")->fetch_assoc();
$monthly_revenue_amount = $monthly_revenue['total'] ?? 0;

// Revenue last month
$last_month_start = date('Y-m-01', strtotime('-1 month'));
$last_month_end = date('Y-m-t', strtotime('-1 month'));
$last_month_revenue = $conn->query("SELECT SUM(total) as total FROM payments WHERE status = 'paid' AND paid_at BETWEEN '$last_month_start' AND '$last_month_end'")->fetch_assoc();
$last_month_revenue_amount = $last_month_revenue['total'] ?? 0;

// Calculate growth percentage
$growth = 0;
if ($last_month_revenue_amount > 0) {
    $growth = (($monthly_revenue_amount - $last_month_revenue_amount) / $last_month_revenue_amount) * 100;
}

// Pending payments
$pending_payments = $conn->query("SELECT SUM(total) as total FROM payments WHERE status = 'pending'")->fetch_assoc();
$pending_amount = $pending_payments['total'] ?? 0;

// ============================================
// MEMBERSHIP METRICS
// ============================================

$total_members = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Active members (approved AND paid)
$active_members = $conn->query("
    SELECT COUNT(*) as count 
    FROM users u
    JOIN user_preferences up ON u.user_id = up.user_id
    WHERE up.training_status = 'approved' AND u.payment_status = 'paid'
")->fetch_assoc()['count'];

// Inactive members (approved but payment expired)
$inactive_members = $conn->query("
    SELECT COUNT(*) as count 
    FROM users u
    JOIN user_preferences up ON u.user_id = up.user_id
    WHERE up.training_status = 'approved' AND (u.payment_status != 'paid' OR u.payment_status IS NULL)
")->fetch_assoc()['count'];

// Pending approval
$pending_approval = $conn->query("
    SELECT COUNT(*) as count 
    FROM user_preferences 
    WHERE training_status = 'pending_approval'
")->fetch_assoc()['count'];

// ============================================
// ACTIVITY METRICS
// ============================================

// Members close to inactive (no activity in 60 days)
$close_to_inactive = $conn->query("
    SELECT COUNT(*) as count, GROUP_CONCAT(name SEPARATOR ', ') as names
    FROM users 
    WHERE last_activity IS NULL OR last_activity < DATE_SUB(NOW(), INTERVAL 60 DAY)
")->fetch_assoc();

// Active last 30 days
$active_last_30_days = $conn->query("
    SELECT COUNT(*) as count 
    FROM users 
    WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch_assoc()['count'];

// ============================================
// BOOKING METRICS
// ============================================

$total_bookings_month = $conn->query("
    SELECT COUNT(*) as count 
    FROM session_attendees 
    WHERE booking_status = 'approved' AND registered_at >= '$first_day_month'
")->fetch_assoc()['count'];

// Popular training
$popular_training = $conn->query("
    SELECT mt.tier_name, COUNT(*) as count
    FROM session_attendees sa
    JOIN training_sessions ts ON sa.session_id = ts.session_id
    JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
    WHERE sa.booking_status = 'approved'
    GROUP BY mt.tier_name
    ORDER BY count DESC
    LIMIT 1
")->fetch_assoc();

// Members by tier
$members_by_tier = $conn->query("
    SELECT mt.tier_name, COUNT(*) as count
    FROM user_preferences up
    JOIN membership_tiers mt ON up.tier_id = mt.tier_id
    GROUP BY mt.tier_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Dashboard - Creative Spark</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .business-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #2E7D32;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-unit {
            font-size: 0.9em;
            color: #666;
        }
        .trend-up { color: #4caf50; }
        .trend-down { color: #f44336; }
        .section-title {
            margin: 30px 0 20px;
            color: #2c3e50;
            border-bottom: 2px solid #2E7D32;
            padding-bottom: 10px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .logout-btn {
            background: #f44336;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 15px;
        }
        .logout-btn:hover {
            background: #d32f2f;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .warning-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .member-name {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="business-container">
        <div class="header">
            <h1>📊 Business Dashboard</h1>
            <div>
                <span style="color: #666;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>💰 Total Revenue</h3>
                <div class="stat-number">€<?php echo number_format($total_revenue_amount, 2); ?></div>
                <div>All time</div>
            </div>
            
            <div class="stat-card">
                <h3>📅 This Month</h3>
                <div class="stat-number">€<?php echo number_format($monthly_revenue_amount, 2); ?></div>
                <div>
                    <?php if ($growth > 0): ?>
                        <span class="trend-up">▲ <?php echo round($growth, 1); ?>%</span> vs last month
                    <?php elseif ($growth < 0): ?>
                        <span class="trend-down">▼ <?php echo abs(round($growth, 1)); ?>%</span> vs last month
                    <?php else: ?>
                        Same as last month
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>⏳ Pending Payments</h3>
                <div class="stat-number">€<?php echo number_format($pending_amount, 2); ?></div>
                <div>Awaiting collection</div>
            </div>
            
            <div class="stat-card">
                <h3>👥 Total Members</h3>
                <div class="stat-number"><?php echo $total_members; ?></div>
                <div>All registered users</div>
            </div>
            
            <div class="stat-card">
                <h3>✅ Active Members</h3>
                <div class="stat-number"><?php echo $active_members; ?></div>
                <div>Approved & Paid</div>
            </div>
            
            <div class="stat-card">
                <h3>❌ Inactive Members</h3>
                <div class="stat-number"><?php echo $inactive_members; ?></div>
                <div>Approved but not paid</div>
            </div>
            
            <div class="stat-card">
                <h3>⏳ Pending Approval</h3>
                <div class="stat-number"><?php echo $pending_approval; ?></div>
                <div>Waiting for review</div>
            </div>
            
            <div class="stat-card">
                <h3>📚 Bookings (This Month)</h3>
                <div class="stat-number"><?php echo $total_bookings_month; ?></div>
                <div>Approved sessions</div>
            </div>
        </div>

        <!-- Membership Breakdown -->
        <h2 class="section-title">🏷️ Members by Tier</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Membership Tier</th>
                        <th>Number of Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tier = $members_by_tier->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $tier['tier_name']; ?></td>
                            <td><?php echo $tier['count']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Popular Training -->
        <?php if ($popular_training): ?>
        <h2 class="section-title">🏆 Most Popular Training</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Top Session</h3>
                <div class="stat-number"><?php echo $popular_training['tier_name']; ?></div>
                <div><?php echo $popular_training['count']; ?> bookings</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Activity Status -->
        <h2 class="section-title">📈 Member Activity</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>🟢 Active (Last 30 days)</h3>
                <div class="stat-number"><?php echo $active_last_30_days; ?></div>
                <div>Regular users</div>
            </div>
            
            <div class="stat-card">
                <h3>🟡 Close to Inactive</h3>
                <div class="stat-number"><?php echo $close_to_inactive['count']; ?></div>
                <div>No activity in 60+ days</div>
                <?php if ($close_to_inactive['count'] > 0 && $close_to_inactive['names']): ?>
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; color: #666;">View members</summary>
                        <div class="warning-list">
                            <?php 
                            $names = explode(', ', $close_to_inactive['names']);
                            foreach ($names as $name): 
                            ?>
                                <div class="member-name">👤 <?php echo htmlspecialchars($name); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #666;">
            <small>📊 Data refreshes in real-time | Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
        </div>
    </div>
</body>
</html>