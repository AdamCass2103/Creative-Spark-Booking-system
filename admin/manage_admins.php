<?php
require_once '../includes/auth.php';
require_once '../includes/admin_functions.php';
requireSuperAdmin(); // Only super admin can manage admins

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$admin_id = getCurrentAdminId();

// Handle add admin
if (isset($_POST['add_admin'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; // In production, hash this!
    $role = $_POST['role'];
    
    $conn->query("INSERT INTO admin_users (name, username, password_hash, role) 
                  VALUES ('$name', '$username', '$password', '$role')");
    
    logAdminActivity($admin_id, 'add_admin', 'admin', $conn->insert_id, "Added admin: $name");
}

// Handle delete admin
if (isset($_POST['delete_admin'])) {
    $target_admin_id = $_POST['admin_id'];
    if ($target_admin_id != $admin_id) { // Can't delete yourself
        $admin = $conn->query("SELECT name FROM admin_users WHERE admin_id = $target_admin_id")->fetch_assoc();
        $conn->query("DELETE FROM admin_users WHERE admin_id = $target_admin_id");
        logAdminActivity($admin_id, 'delete_admin', 'admin', $target_admin_id, "Deleted admin: " . $admin['name']);
    }
}

// Get all admins
$admins = $conn->query("SELECT * FROM admin_users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Admins</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .admin-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #2E7D32;
        }
        .admin-card.super {
            border-left-color: #9c27b0;
        }
        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            background: #e8f5e9;
            color: #2E7D32;
        }
        .role-badge.super {
            background: #f3e5f5;
            color: #9c27b0;
        }
        .add-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Manage Administrators</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin Panel</a>
                <a href="activity_log.php" class="btn">📋 Activity Log</a>
            </div>
        </div>
        
        <!-- Add New Admin Form -->
        <div class="add-form">
            <h2>Add New Admin</h2>
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="role">
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <button type="submit" name="add_admin" class="btn" style="margin-top: 10px;">➕ Add Admin</button>
            </form>
        </div>
        
        <!-- Admin List -->
        <div class="admin-grid">
            <?php while($admin = $admins->fetch_assoc()): ?>
            <div class="admin-card <?php echo $admin['role'] == 'super_admin' ? 'super' : ''; ?>">
                <div style="display: flex; justify-content: space-between;">
                    <h3><?php echo $admin['name']; ?></h3>
                    <span class="role-badge <?php echo $admin['role'] == 'super_admin' ? 'super' : ''; ?>">
                        <?php echo ucfirst($admin['role']); ?>
                    </span>
                </div>
                <p style="color: #666; margin: 5px 0;">@<?php echo $admin['username']; ?></p>
                <p style="color: #999; font-size: 0.9em;">
                    Last login: <?php echo $admin['last_login'] ? date('M j, Y', strtotime($admin['last_login'])) : 'Never'; ?>
                </p>
                <?php if($admin['admin_id'] != $admin_id && $admin['role'] != 'super_admin'): ?>
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                    <button type="submit" name="delete_admin" class="btn" style="background: #f44336; padding: 5px 10px;">Delete</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>