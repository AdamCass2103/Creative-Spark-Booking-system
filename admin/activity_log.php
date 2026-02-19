<?php
require_once '../includes/auth.php';
require_once '../includes/admin_functions.php';
requireAdmin();

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$admin_id = getCurrentAdminId();

// Get filter
$filter_action = $_GET['action'] ?? 'all';
$filter_admin = $_GET['admin'] ?? 'all';

// Build query
$query = "SELECT * FROM admin_activity_log WHERE 1=1";
if ($filter_action != 'all') {
    $query .= " AND action = '$filter_action'";
}
if ($filter_admin != 'all') {
    $query .= " AND admin_id = $filter_admin";
}
$query .= " ORDER BY created_at DESC LIMIT 100";

$activities = $conn->query($query);
$admins = $conn->query("SELECT admin_id, name FROM admin_users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Activity Log</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .log-entry {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .log-entry:hover {
            background: #f5f5f5;
        }
        .log-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .log-icon.approve { background: #e8f5e9; color: #2E7D32; }
        .log-icon.reject { background: #ffebee; color: #f44336; }
        .log-icon.delete { background: #ffebee; color: #f44336; }
        .log-icon.update { background: #e3f2fd; color: #2196f3; }
        .log-icon.login { background: #e8f5e9; color: #2E7D32; }
        .log-content {
            flex: 1;
        }
        .log-title {
            font-weight: bold;
            color: #333;
        }
        .log-meta {
            display: flex;
            gap: 15px;
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .log-time {
            color: #999;
        }
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Admin Activity Log</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin Panel</a>
                <a href="manage_admins.php" class="btn">👥 Manage Admins</a>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <select name="action">
                    <option value="all" <?php echo $filter_action == 'all' ? 'selected' : ''; ?>>All Actions</option>
                    <option value="login" <?php echo $filter_action == 'login' ? 'selected' : ''; ?>>Logins</option>
                    <option value="update_status" <?php echo $filter_action == 'update_status' ? 'selected' : ''; ?>>Status Updates</option>
                    <option value="delete_user" <?php echo $filter_action == 'delete_user' ? 'selected' : ''; ?>>User Deletions</option>
                    <option value="add_admin" <?php echo $filter_action == 'add_admin' ? 'selected' : ''; ?>>Admin Added</option>
                </select>
                
                <select name="admin">
                    <option value="all" <?php echo $filter_admin == 'all' ? 'selected' : ''; ?>>All Admins</option>
                    <?php while($admin = $admins->fetch_assoc()): ?>
                    <option value="<?php echo $admin['admin_id']; ?>" <?php echo $filter_admin == $admin['admin_id'] ? 'selected' : ''; ?>>
                        <?php echo $admin['name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit" class="btn" style="padding: 8px 20px;">Filter</button>
            </form>
        </div>
        
        <!-- Activity List -->
        <div class="table-container">
            <?php if($activities->num_rows == 0): ?>
                <p style="text-align: center; padding: 40px; color: #666;">No activity found</p>
            <?php else: ?>
                <?php while($log = $activities->fetch_assoc()): 
                    $icon_class = '';
                    if(strpos($log['action'], 'approve') !== false) $icon_class = 'approve';
                    elseif(strpos($log['action'], 'reject') !== false) $icon_class = 'reject';
                    elseif(strpos($log['action'], 'delete') !== false) $icon_class = 'delete';
                    elseif(strpos($log['action'], 'update') !== false) $icon_class = 'update';
                    elseif(strpos($log['action'], 'login') !== false) $icon_class = 'login';
                    else $icon_class = 'update';
                ?>
                <div class="log-entry">
                    <div class="log-icon <?php echo $icon_class; ?>">
                        <?php 
                        if($icon_class == 'approve') echo '✅';
                        elseif($icon_class == 'reject') echo '❌';
                        elseif($icon_class == 'delete') echo '🗑️';
                        elseif($icon_class == 'login') echo '🔑';
                        else echo '📝';
                        ?>
                    </div>
                    <div class="log-content">
                        <div class="log-title">
                            <strong><?php echo $log['admin_name']; ?></strong> 
                            <?php echo str_replace('_', ' ', $log['action']); ?>
                            <?php if($log['target_type'] == 'user'): ?>
                                user #<?php echo $log['target_id']; ?>
                            <?php endif; ?>
                        </div>
                        <?php if($log['details']): ?>
                        <div class="log-meta"><?php echo $log['details']; ?></div>
                        <?php endif; ?>
                        <div class="log-meta">
                            <span>🕐 <?php echo formatActivityTime($log['created_at']); ?></span>
                            <span>🌐 IP: <?php echo $log['ip_address']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>