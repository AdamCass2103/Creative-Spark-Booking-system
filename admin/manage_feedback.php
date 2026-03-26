<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection error. Please try again later.");
}

$admin_id = getCurrentAdminId();
$admin_role = getCurrentAdminRole();
$is_viewer = ($admin_role == 'viewer');

$message = '';
$message_type = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $admin_notes = $conn->real_escape_string($_POST['admin_notes'] ?? '');
    
    $conn->query("UPDATE feedback SET status = '$status', admin_notes = '$admin_notes', updated_at = NOW() WHERE id = $feedback_id");
    $message = "Feedback status updated!";
    $message_type = "success";
    
    // Log activity
    logAdminActivity($admin_id, 'update_feedback', 'feedback', $feedback_id, "Changed status to $status");
}

// Handle reply
if (isset($_POST['add_reply'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $response = $conn->real_escape_string($_POST['response']);
    
    $stmt = $conn->prepare("INSERT INTO feedback_responses (feedback_id, admin_id, response, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $feedback_id, $admin_id, $response);
    $stmt->execute();
    
    $message = "Reply added!";
    $message_type = "success";
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$category_filter = $_GET['category'] ?? 'all';

$where = [];
if ($status_filter !== 'all') {
    $where[] = "f.status = '$status_filter'";
}
if ($category_filter !== 'all') {
    $where[] = "f.category = '$category_filter'";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get all feedback
$feedback = $conn->query("
    SELECT f.*, u.name as user_name, u.email,
           (SELECT response FROM feedback_responses WHERE feedback_id = f.id ORDER BY created_at DESC LIMIT 1) as last_response
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    $where_clause
    ORDER BY 
        CASE f.priority
            WHEN 'high' THEN 1
            WHEN 'medium' THEN 2
            WHEN 'low' THEN 3
        END,
        f.created_at DESC
");

// Get stats
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as urgent
    FROM feedback
")->fetch_assoc();

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category, COUNT(*) as count FROM feedback GROUP BY category");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Feedback Dashboard</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
                <a href="pending_bookings.php" class="btn">⏳ Pending Bookings</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="success-message">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="feedback-stats">
            <div class="stat-card pending">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card in-progress">
                <h3><?php echo $stats['in_progress']; ?></h3>
                <p>In Progress</p>
            </div>
            <div class="stat-card completed">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Completed</p>
            </div>
            <div class="stat-card urgent">
                <h3><?php echo $stats['urgent']; ?></h3>
                <p>Urgent</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <strong>Status:</strong>
            <a href="?status=all&category=<?php echo $category_filter; ?>" class="<?php echo $status_filter == 'all' ? 'active' : ''; ?>">All</a>
            <a href="?status=pending&category=<?php echo $category_filter; ?>" class="<?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?status=in_progress&category=<?php echo $category_filter; ?>" class="<?php echo $status_filter == 'in_progress' ? 'active' : ''; ?>">In Progress</a>
            <a href="?status=completed&category=<?php echo $category_filter; ?>" class="<?php echo $status_filter == 'completed' ? 'active' : ''; ?>">Completed</a>
            
            <strong>Category:</strong>
            <a href="?status=<?php echo $status_filter; ?>&category=all" class="<?php echo $category_filter == 'all' ? 'active' : ''; ?>">All</a>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="?status=<?php echo $status_filter; ?>&category=<?php echo $cat['category']; ?>" class="<?php echo $category_filter == $cat['category'] ? 'active' : ''; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $cat['category'])); ?> (<?php echo $cat['count']; ?>)
                </a>
            <?php endwhile; ?>
        </div>

        <!-- Feedback List -->
        <?php if ($feedback && $feedback->num_rows > 0): ?>
            <?php while ($item = $feedback->fetch_assoc()): 
                $priority_class = $item['priority'] == 'high' ? 'priority-high' : ($item['priority'] == 'medium' ? 'priority-medium' : 'priority-low');
                $category_icons = ['bug' => '🐛', 'feature' => '💡', 'machine_issue' => '🛠️', 'training_feedback' => '📚', 'tutorial_suggestion' => '🔧'];
                $category_names = ['bug' => 'Bug Report', 'feature' => 'Feature Request', 'machine_issue' => 'Machine Issue', 'training_feedback' => 'Training Feedback', 'tutorial_suggestion' => 'Tutorial Suggestion'];
            ?>
                <div class="feedback-item <?php echo $priority_class; ?>">
                    <div class="feedback-header">
                        <div>
                            <span class="category-badge" style="background: #e8f5e9;">
                                <?php echo $category_icons[$item['category']] . ' ' . $category_names[$item['category']]; ?>
                            </span>
                            <span class="status-badge status-<?php echo $item['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?>
                            </span>
                            <?php if ($item['priority'] == 'high'): ?>
                                <span style="color: #f44336; font-weight: bold;">🔴 URGENT</span>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            👤 <?php echo htmlspecialchars($item['user_name']); ?> (<?php echo $item['email']; ?>)
                        </div>
                    </div>
                    
                    <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($item['subject']); ?></h3>
                    <p style="color: #333;"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    
                    <?php if ($item['image_path']): ?>
                        <div style="margin: 10px 0;">
                            <a href="../<?php echo $item['image_path']; ?>" target="_blank" style="color: #2E7D32;">📷 View attached image</a>
                        </div>
                    <?php endif; ?>
                    
                    <div style="font-size: 0.85em; color: #666; margin: 10px 0;">
                        Submitted: <?php echo date('M j, Y \a\t g:i A', strtotime($item['created_at'])); ?>
                    </div>
                    
                    <?php if ($item['last_response']): ?>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin: 10px 0;">
                            <strong>📝 Latest reply:</strong><br>
                            <?php echo nl2br(htmlspecialchars($item['last_response'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Update Status Form -->
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="feedback_id" value="<?php echo $item['id']; ?>">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                            <select name="status" class="status-select">
                                <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="acknowledged" <?php echo $item['status'] == 'acknowledged' ? 'selected' : ''; ?>>Acknowledged</option>
                                <option value="in_progress" <?php echo $item['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $item['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="declined" <?php echo $item['status'] == 'declined' ? 'selected' : ''; ?>>Declined</option>
                            </select>
                            <input type="text" name="admin_notes" placeholder="Internal notes (optional)" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <button type="submit" name="update_status" class="btn-sm">Update Status</button>
                        </div>
                    </form>
                    
                    <!-- Add Reply Form -->
                    <form method="POST" class="reply-form">
                        <input type="hidden" name="feedback_id" value="<?php echo $item['id']; ?>">
                        <textarea name="response" rows="3" placeholder="Write a reply to the user..."></textarea>
                        <button type="submit" name="add_reply" class="btn-sm" style="margin-top: 10px;">Send Reply</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 60px; background: white; border-radius: 10px;">
                <p>No feedback found matching your filters.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>