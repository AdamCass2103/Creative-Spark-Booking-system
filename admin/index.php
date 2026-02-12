<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Filter handling
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = '';

switch ($filter) {
    case 'needs_training':
        $where = 'WHERE up.needs_training = 1';
        break;
    case 'no_training':
        $where = 'WHERE up.needs_training = 0';
        break;
    case 'new_users':
        $where = 'WHERE DATE(u.created_at) = CURDATE()';
        break;
    case 'pending':
        $where = 'WHERE up.training_status = "pending"';
        break;
}

// Fetch users with preferences
$query = "SELECT u.user_id, u.name, u.email, u.created_at, 
                 up.is_returning_member, up.needs_training, 
                 up.terms_accepted, up.training_status
          FROM users u
          JOIN user_preferences up ON u.user_id = up.user_id
          $where
          ORDER BY u.created_at DESC";
$result = $conn->query($query);

// Update training status
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['training_status'];
    
    $stmt = $conn->prepare("UPDATE user_preferences SET training_status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();
    
    header("Location: index.php?filter=$filter");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Booking System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Panel</h1>
            <div class="nav">
                <a href="../dashboard.php" class="btn">Dashboard</a>
                <a href="../logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="filter-section">
            <h3>Filter Users:</h3>
            <div class="filter-buttons">
                <a href="?filter=all" class="btn filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Users</a>
                <a href="?filter=needs_training" class="btn filter-btn <?php echo $filter === 'needs_training' ? 'active' : ''; ?>">Need Training</a>
                <a href="?filter=no_training" class="btn filter-btn <?php echo $filter === 'no_training' ? 'active' : ''; ?>">No Training Needed</a>
                <a href="?filter=new_users" class="btn filter-btn <?php echo $filter === 'new_users' ? 'active' : ''; ?>">New Users</a>
                <a href="?filter=pending" class="btn filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending Approval</a>
            </div>
        </div>
        
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Returning Member</th>
                        <th>Needs Training</th>
                        <th>Terms Accepted</th>
                        <th>Training Status</th>
                        <th>Actions</th>
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
                            <form method="POST" class="status-form">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <select name="training_status" class="status-select">
                                    <option value="pending" <?php echo $row['training_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $row['training_status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $row['training_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="completed" <?php echo $row['training_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $result->num_rows; ?></p>
            </div>
        </div>
    </div>
</body>
</html>