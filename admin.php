<?php
$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Handle status updates
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['training_status'];
    $conn->query("UPDATE user_preferences SET training_status = '$status' WHERE user_id = $user_id");
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
    <title>Admin Panel - Booking System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { color: #9c27b0; margin-bottom: 15px; }
        .nav { display: flex; gap: 15px; margin-bottom: 15px; }
        .btn { padding: 12px 25px; background: #1a73e8; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .btn:hover { background: #0d62d9; }
        .back-btn { background: #757575; }
        .back-btn:hover { background: #616161; }
        .filter-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
        .filter-btn { padding: 10px 20px; background: #e0e0e0; color: #333; border: none; border-radius: 6px; cursor: pointer; }
        .filter-btn.active { background: #1a73e8; color: white; }
        .table-container { background: white; padding: 25px; border-radius: 10px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #9c27b0; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-approved { color: #4caf50; font-weight: bold; }
        .status-rejected { color: #f44336; font-weight: bold; }
        .status-completed { color: #2196f3; font-weight: bold; }
        select { padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
        .update-btn { padding: 8px 15px; background: #4caf50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .update-btn:hover { background: #388e3c; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; margin-bottom: 10px; }
        .stat-card p { font-size: 2em; font-weight: bold; color: #1a73e8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Panel</h1>
            <div class="nav">
                <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
                <a href="index.php" class="btn">Create New User</a>
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
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="stats">
            <?php
            // Count stats
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
</body>
</html>