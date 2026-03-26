<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection error. Please try again later.");
}

$user_id = getCurrentUserId();
$user = $conn->query("SELECT name FROM users WHERE user_id = $user_id")->fetch_assoc();

$message = '';
$message_type = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $category = $conn->real_escape_string($_POST['category']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    $priority = $conn->real_escape_string($_POST['priority']);
    
    // Handle image upload (optional)
    $image_path = null;
    if (isset($_FILES['feedback_image']) && $_FILES['feedback_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/feedback/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['feedback_image']['name'], PATHINFO_EXTENSION);
        $filename = 'feedback_' . time() . '_' . $user_id . '.' . $ext;
        $upload_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['feedback_image']['tmp_name'], $upload_path)) {
            $image_path = 'uploads/feedback/' . $filename;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, category, subject, description, priority, image_path, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isssss", $user_id, $category, $subject, $description, $priority, $image_path);
    
    if ($stmt->execute()) {
        $message = "✅ Thank you for your feedback! Oscar will review it soon.";
        $message_type = "success";
    } else {
        $message = "❌ Error submitting feedback. Please try again.";
        $message_type = "error";
    }
}

// Get user's recent feedback
$user_feedback = $conn->query("
    SELECT * FROM feedback 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 10
");

// Get status counts
$status_counts = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM feedback 
    WHERE user_id = $user_id 
    GROUP BY status
");
$status_counts_array = [];
while ($row = $status_counts->fetch_assoc()) {
    $status_counts_array[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Hub - Creative Spark</title>
    <link rel="stylesheet" href="../css/feedback.css">
</head>
<body>
    <div class="feedback-container">
        <div class="feedback-header">
            <h1>💬 Share Your Feedback</h1>
            <p>Help us improve Creative Spark! Your ideas and reports matter.</p>
            <a href="dashboard.php" class="back-link" style="display: inline-block; margin-top: 15px; color: #2E7D32;">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Submit Feedback Form -->
        <div class="feedback-card">
            <h2 style="color: #2E7D32; margin-bottom: 20px;">📝 Submit New Feedback</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select a category</option>
                        <option value="bug">🐛 Bug Report - Something isn't working</option>
                        <option value="feature">💡 Feature Request - New idea or improvement</option>
                        <option value="machine_issue">🛠️ Machine Issue - Equipment problem</option>
                        <option value="training_feedback">📚 Training Feedback - Rate or suggest training</option>
                        <option value="tutorial_suggestion">🔧 New Tutorial - Request specific guide</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" placeholder="Brief summary of your feedback" required>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="5" placeholder="Please provide details..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low">Low - Nice to have</option>
                        <option value="medium" selected>Medium - Important</option>
                        <option value="high">High - Urgent issue</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Attach Photo (Optional)</label>
                    <input type="file" name="feedback_image" accept="image/*">
                    <small style="color: #666;">Upload a photo to help us understand the issue</small>
                </div>
                
                <button type="submit" name="submit_feedback" class="btn-submit">
                    📤 Submit Feedback
                </button>
            </form>
        </div>

        <!-- Recent Feedback -->
        <div class="feedback-card">
            <h2 style="color: #2E7D32; margin-bottom: 20px;">📋 My Recent Feedback</h2>
            
            <div class="stats-bar">
                <div class="stat">
                    <div class="stat-number"><?php echo $status_counts_array['pending'] ?? 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo ($status_counts_array['acknowledged'] ?? 0) + ($status_counts_array['in_progress'] ?? 0); ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $status_counts_array['completed'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            
            <?php if ($user_feedback && $user_feedback->num_rows > 0): ?>
                <?php while ($item = $user_feedback->fetch_assoc()): ?>
                    <?php
                    $category_class = '';
                    switch ($item['category']) {
                        case 'bug': $category_class = 'category-bug'; break;
                        case 'feature': $category_class = 'category-feature'; break;
                        case 'machine_issue': $category_class = 'category-machine_issue'; break;
                        case 'training_feedback': $category_class = 'category-training_feedback'; break;
                        case 'tutorial_suggestion': $category_class = 'category-tutorial_suggestion'; break;
                    }
                    $priority_class = $item['priority'] == 'high' ? 'priority-high' : ($item['priority'] == 'medium' ? 'priority-medium' : 'priority-low');
                    ?>
                    <div class="feedback-item <?php echo $priority_class; ?>">
                        <div>
                            <span class="feedback-category <?php echo $category_class; ?>">
                                <?php 
                                    $icons = ['bug' => '🐛', 'feature' => '💡', 'machine_issue' => '🛠️', 'training_feedback' => '📚', 'tutorial_suggestion' => '🔧'];
                                    echo $icons[$item['category']] . ' ' . ucfirst(str_replace('_', ' ', $item['category']));
                                ?>
                            </span>
                            <span class="status-badge status-<?php echo $item['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?>
                            </span>
                        </div>
                        <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($item['subject']); ?></h3>
                        <p style="color: #666;"><?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 150))); ?>...</p>
                        <small style="color: #999;">Submitted: <?php echo date('M j, Y', strtotime($item['created_at'])); ?></small>
                        
                        <?php if ($item['image_path']): ?>
                            <div style="margin-top: 10px;">
                                <a href="../<?php echo $item['image_path']; ?>" target="_blank" style="color: #2E7D32;">📷 View attached image</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px;">You haven't submitted any feedback yet. Share your thoughts above!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>