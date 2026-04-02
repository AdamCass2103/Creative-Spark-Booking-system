<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

// Only super admin can manage other admins
$current_role = getCurrentAdminRole();
if ($current_role !== 'super_admin') {
    die("Access denied. Only Super Admins can manage admin users.");
}

$conn = getDatabaseConnection();
$message = '';
$error = '';

// Handle adding new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);
    
    // Validate
    if (empty($name) || empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if username exists
        $check = $conn->query("SELECT admin_id FROM admin_users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            // For now, store plain text (match your existing system)
            $hashed_password = $password; // Your system uses plain text
            
            $conn->query("INSERT INTO admin_users (name, username, password_hash, role) 
                          VALUES ('$name', '$username', '$hashed_password', '$role')");
            
            if ($conn->affected_rows > 0) {
                logAdminActivity(getCurrentAdminId(), 'add_admin', 'admin', $conn->insert_id, "Added new $role: $username");
                $message = "Admin user added successfully!";
            } else {
                $error = "Failed to add admin user.";
            }
        }
    }
}

// Handle deleting admin
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['delete'];
    // Don't allow deleting yourself
    if ($id != getCurrentAdminId()) {
        $admin = $conn->query("SELECT username FROM admin_users WHERE admin_id = $id")->fetch_assoc();
        $conn->query("DELETE FROM admin_users WHERE admin_id = $id");
        logAdminActivity(getCurrentAdminId(), 'delete_admin', 'admin', $id, "Deleted admin: {$admin['username']}");
        $message = "Admin user deleted successfully!";
    } else {
        $error = "You cannot delete your own account.";
    }
}

// Get all admin users
$admins = $conn->query("SELECT admin_id, name, username, role, last_login, created_at FROM admin_users ORDER BY admin_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Creative Spark</title>
    <link rel="stylesheet" href="../css/manage_admins.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Manage Admin Users</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message success">✅ <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add New Admin Form -->
        <div class="form-card">
            <h2>➕ Add New Admin User</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="e.g., John Smith">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="e.g., johnsmith">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="role-select">
                        <option value="analyst">📊 Analyst - Business Dashboard only (read-only)</option>
                        <option value="viewer">👁️ Viewer - Can view but not edit</option>
                        <option value="admin">🔧 Admin - Full admin access</option>
                        <option value="super_admin">⭐ Super Admin - Full access + can manage admins</option>
                    </select>
                </div>
                <button type="submit" name="add_admin" class="btn-save">Create Admin User</button>
            </form>
        </div>

        <!-- Existing Admins List -->
        <div class="table-container">
            <h2 style="padding: 15px 0 0 15px;">📋 Existing Admin Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($admin = $admins->fetch_assoc()): ?>
                        <?php 
                        $role_class = '';
                        switch($admin['role']) {
                            case 'super_admin': $role_class = 'role-super_admin'; break;
                            case 'admin': $role_class = 'role-admin'; break;
                            case 'viewer': $role_class = 'role-viewer'; break;
                            case 'analyst': $role_class = 'role-analyst'; break;
                        }
                        ?>
                        <tr>
                            <td><?php echo $admin['admin_id']; ?></td>
                            <td><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td><span class="role-badge <?php echo $role_class; ?>"><?php echo strtoupper(str_replace('_', ' ', $admin['role'])); ?></span></td>
                            <td><?php echo $admin['last_login'] ? date('M j, Y', strtotime($admin['last_login'])) : 'Never'; ?></td>
                            <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                            <td>
                                <?php if ($admin['admin_id'] != getCurrentAdminId()): ?>
                                    <a href="?delete=<?php echo $admin['admin_id']; ?>" class="delete-btn" onclick="return confirm('Delete this admin user?')">Delete</a>
                                <?php else: ?>
                                    <span style="color: #999;">(You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px;">
            <h3 style="color: #1976d2; margin-bottom: 10px;">📌 Role Descriptions</h3>
            <ul style="margin-left: 20px; color: #555;">
                <li><strong>⭐ Super Admin</strong> - Full access to everything + can manage other admin users</li>
                <li><strong>🔧 Admin</strong> - Full access to admin panel (users, bookings, payments)</li>
                <li><strong>👁️ Viewer</strong> - Can view all data but cannot make changes</li>
                <li><strong>📊 Analyst</strong> - Business dashboard only (financial stats, member activity - read only)</li>
            </ul>
        </div>
    </div>
</body>
</html>