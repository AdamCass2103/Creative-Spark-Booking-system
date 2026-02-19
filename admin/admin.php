<?php
require_once '../includes/auth.php';
requireAdmin();

$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Handle status updates
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['training_status'];
    $conn->query("UPDATE user_preferences SET training_status = '$status' WHERE user_id = $user_id");
}

// Handle user deletion - ADDED
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Delete user (foreign keys will cascade)
    $conn->query("DELETE FROM users WHERE user_id = $user_id");
    
    if ($conn->affected_rows > 0) {
        $delete_success = "User deleted successfully!";
    } else {
        $delete_error = "Failed to delete user.";
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
        $where = 'WHERE up.training_status = "pending"';
        break;
}

// Get all users
$result = $conn->query("
    SELECT u.user_id, u.name, u.email, u.created_at,
           up.is_returning_member, up.needs_training, 
           up.terms_accepted, up.training_status
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
</head>
<body>
    <div class="container">
        <!-- Success/Error Messages - ADDED -->
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
            <h1>Admin Panel</h1>
            <div class="nav">
                <a href="../member/dashboard.php" class="btn back-btn">← Back to Dashboard</a>
                <a href="../public/index.php" class="btn">➕ Create New User</a>
                <a href="training_sessions.php" class="btn" style="background: #9c27b0;">
                    📅 Training Sessions
                </a>
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" onclick="window.location='?filter=all'">All Users</button>
                <button class="filter-btn <?php echo $filter === 'needs_training' ? 'active' : ''; ?>" onclick="window.location='?filter=needs_training'">Need Training</button>
                <button class="filter-btn <?php echo $filter === 'no_training' ? 'active' : ''; ?>" onclick="window.location='?filter=no_training'">No Training Needed</button>
                <button class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>" onclick="window.location='?filter=pending'">Pending Approval</button>
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
                        <th>Update Status</th>
                        <th>Action</th>
                        <th>Delete</th>  <!-- NEW COLUMN -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['is_returning_member'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row['needs_training'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $row['terms_accepted'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <span class="status-<?php echo $row['training_status']; ?>">
                                <?php echo ucfirst($row['training_status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <select name="training_status">
                                    <option value="pending" <?php echo $row['training_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
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
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="stats">
            <?php
            $total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
            $need_training = $conn->query("SELECT COUNT(*) as count FROM user_preferences WHERE needs_training = 1")->fetch_assoc()['count'];
            $pending = $conn->query("SELECT COUNT(*) as count FROM user_preferences WHERE training_status = 'pending'")->fetch_assoc()['count'];
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
                <h3>Pending Approval</h3>
                <p><?php echo $pending; ?></p>
            </div>
        </div>
    </div>

    <!-- JavaScript for Delete Confirmation - ADDED -->
    <script>
    function confirmDelete(userId, userName) {
        if (confirm(`Are you sure you want to delete ${userName}?\n\nThis action cannot be undone! All user data will be permanently removed.`)) {
            // Create a form and submit it
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
    </script>
</body>
</html>