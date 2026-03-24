<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';     
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

// Use the same database connection pattern
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (getenv('VERCEL_ENV')) {
        $conn = mysqli_init();
        
        // Handle SSL certificate
        if (getenv('CA_CERT')) {
            $cert_path = '/tmp/ca.pem';
            file_put_contents($cert_path, getenv('CA_CERT'));
            $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
        } else {
            $conn->ssl_set(NULL, NULL, __DIR__ . '/../certs/ca.pem', NULL, NULL);
        }
        
        $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, 25849, NULL, MYSQLI_CLIENT_SSL);
    } else {
        // Local XAMPP connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
} catch (mysqli_sql_exception $e) {
    error_log("Admin connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

$admin_id = getCurrentAdminId();
$admin_role = getCurrentAdminRole();
$is_viewer = ($admin_role == 'viewer');

// Only allow modifications if NOT a viewer
if (!$is_viewer) {
    // Handle status updates
    if (isset($_POST['update_status'])) {
        $user_id = $_POST['user_id'];
        $status = $_POST['training_status'];
        
        // Get old status for logging
        $old = $conn->query("SELECT training_status FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
        $old_status = $old['training_status'] ?? 'unknown';
        
        $conn->query("UPDATE user_preferences SET training_status = '$status' WHERE user_id = $user_id");
        
        // Log the action
        logAdminActivity($admin_id, 'update_status', 'user', $user_id, "Changed status from $old_status to $status");
    }

    // Handle user deletion
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        // Get user name for logging
        $user = $conn->query("SELECT name FROM users WHERE user_id = $user_id")->fetch_assoc();
        $user_name = $user['name'] ?? 'Unknown';
        
        // Delete user
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
        
        if ($conn->affected_rows > 0) {
            logAdminActivity($admin_id, 'delete_user', 'user', $user_id, "Deleted user: $user_name");
            $delete_success = "User deleted successfully!";
        } else {
            $delete_error = "Failed to delete user.";
        }
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$where = '';

switch ($filter) {
    case 'needs_training':
        $where = 'WHERE up.needs_training = 1';
        break;
    case 'no_training':
        $where = 'WHERE up.needs_training = 0';
        break;
    case 'pending':
        // FIXED: Use correct status value 'pending_approval'
        $where = 'WHERE up.training_status = "pending_approval"';
        break;
    case 'has_bookings':
        // Show users with pending booking approvals
        $where = 'WHERE u.user_id IN (SELECT DISTINCT user_id FROM session_attendees WHERE booking_status IN ("pending", "pending_approval"))';
        break;
}

// Get all users
$result = $conn->query("
    SELECT u.user_id, u.name, u.email, u.created_at,
           up.is_returning_member, up.needs_training, 
           up.terms_accepted, up.training_status,
           (SELECT COUNT(*) FROM session_attendees WHERE user_id = u.user_id AND booking_status IN ('pending', 'pending_approval')) as pending_bookings
    FROM users u
    JOIN user_preferences up ON u.user_id = up.user_id
    $where
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Creative Spark</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        /* Viewer mode styles */
        .viewer-badge {
            background: #9c27b0;
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.8em;
            margin-left: 15px;
            display: inline-block;
        }
        .viewer-notice {
            background: #e3f2fd;
            color: #0d47a1;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .disabled-action {
            opacity: 0.5;
            pointer-events: none;
        }
        .pending-badge {
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Viewer Notice -->
        <?php if ($is_viewer): ?>
        <div class="viewer-notice">
            <strong>👁️ Viewer Mode:</strong> You can view all data but cannot make changes. Contact a Super Admin to modify records.
        </div>
        <?php endif; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($delete_success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                ✅ <?php echo $delete_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($delete_error)): ?>
            <div style="background: #fee; color: #c00; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #f44336;">
                ❌ <?php echo $delete_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Admin Panel 
                <?php if ($is_viewer): ?>
                    <span class="viewer-badge">Viewer Mode</span>
                <?php endif; ?>
            </h1>
            <div class="nav">
                <a href="../member/dashboard.php" class="btn back-btn">← Back to Dashboard</a>
                <?php if (!$is_viewer): ?>
                <a href="../public/index.php" class="btn">➕ Create New User</a>
                <?php endif; ?>
                <a href="training_sessions.php" class="btn" style="background: #9c27b0;">📅 Training Sessions</a>
                <a href="pending_bookings.php" class="btn" style="background: #ff9800;">⏳ Pending Bookings</a>
                
                <div style="position: relative; display: inline-block;">
                    <button class="btn" style="background: #2E7D32;" onclick="toggleAdminMenu()">
                        👥 Admin ▼
                    </button>
                    <div id="adminMenu" style="display: none; position: absolute; background: white; min-width: 200px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); z-index: 1; border-radius: 5px; margin-top: 5px; right: 0;">
                        <a href="manage_admins.php" style="color: #333; padding: 12px 16px; text-decoration: none; display: block; border-bottom: 1px solid #eee;">👥 Manage Admins</a>
                        <a href="activity_log.php" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">📋 Activity Log</a>
                    </div>
                </div>
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" onclick="window.location='?filter=all'">All Users</button>
                <button class="filter-btn <?php echo $filter === 'needs_training' ? 'active' : ''; ?>" onclick="window.location='?filter=needs_training'">Need Training</button>
                <button class="filter-btn <?php echo $filter === 'no_training' ? 'active' : ''; ?>" onclick="window.location='?filter=no_training'">No Training Needed</button>
                <button class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>" onclick="window.location='?filter=pending'">Pending Approval</button>
                <button class="filter-btn <?php echo $filter === 'has_bookings' ? 'active' : ''; ?>" onclick="window.location='?filter=has_bookings'">📌 Has Pending Bookings</button>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Returning</th>
                        <th>Needs Training</th>
                        <th>Terms Accepted</th>
                        <th>Training Status</th>
                        <th>Pending Bookings</th>
                        <?php if (!$is_viewer): ?>
                        <th>Update Status</th>
                        <th>Action</th>
                        <th>Delete</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['is_returning_member'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row['needs_training'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row['terms_accepted'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <span class="status-<?php echo $row['training_status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $row['training_status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['pending_bookings'] > 0): ?>
                                <span class="pending-badge"><?php echo $row['pending_bookings']; ?> pending</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <?php if (!$is_viewer): ?>
                        <td>
                            <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <select name="training_status">
                                    <option value="pending_approval" <?php echo $row['training_status'] == 'pending_approval' ? 'selected' : ''; ?>>Pending Approval</option>
                                    <option value="approved" <?php echo $row['training_status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $row['training_status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="completed" <?php echo $row['training_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">Update</button>
                            </form>
                        </td>
                        <td>
                            <a href="user_profile.php?id=<?php echo $row['user_id']; ?>" 
                               style="background: #9c27b0; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px;">
                                View
                            </a>
                        </td>
                        <td>
                            <button onclick="confirmDelete(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" 
                                    style="background: #f44336; color: white; padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; font-size: 13px;">
                                🗑️ Delete
                            </button>
                        </td>
                        <?php else: ?>
                        <td>
                            <a href="user_profile.php?id=<?php echo $row['user_id']; ?>" 
                               style="background: #9c27b0; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px;">
                                View Profile
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="stats">
            <?php
            $total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
            $need_training = $conn->query("SELECT COUNT(*) as count FROM user_preferences WHERE needs_training = 1")->fetch_assoc()['count'];
            $pending = $conn->query("SELECT COUNT(*) as count FROM user_preferences WHERE training_status = 'pending_approval'")->fetch_assoc()['count'];
            $pending_bookings = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM session_attendees WHERE booking_status IN ('pending', 'pending_approval')")->fetch_assoc()['count'];
            ?>
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $total; ?></p>
            </div>
            <div class="stat-card">
                <h3>Need Training</h3>
                <p><?php echo $need_training; ?></p>
            </div>
            <div class="stat-card">
                <h3>Training Pending</h3>
                <p><?php echo $pending; ?></p>
            </div>
            <div class="stat-card">
                <h3>📌 Booking Requests</h3>
                <p><?php echo $pending_bookings; ?></p>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(userId, userName) {
        if (confirm(`Are you sure you want to delete ${userName}?\n\nThis action cannot be undone! All user data will be permanently removed.`)) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_id';
            input.value = userId;
            
            var deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_user';
            deleteInput.value = '1';
            
            form.appendChild(input);
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleAdminMenu() {
        var menu = document.getElementById('adminMenu');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
        } else {
            menu.style.display = 'none';
        }
    }

    window.onclick = function(event) {
        if (!event.target.matches('.btn')) {
            var menu = document.getElementById('adminMenu');
            if (menu && menu.style.display === 'block') {
                menu.style.display = 'none';
            }
        }
    }
    </script>
</body>
</html>