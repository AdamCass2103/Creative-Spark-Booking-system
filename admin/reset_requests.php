<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

$conn = getDatabaseConnection();
$generated_link = '';
$error = '';

// Handle generating reset link
if (isset($_GET['generate']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $reset = $conn->query("SELECT email, token FROM password_resets WHERE id = $id AND used = 0 AND expires_at > NOW()")->fetch_assoc();
    
    if ($reset) {
        $reset_link = SITE_URL . "/reset_password.php?token=" . $reset['token'];
        
        // Mark as notified (Oscar has seen it)
        $conn->query("UPDATE password_resets SET notified = 1 WHERE id = $id");
        
        $generated_link = $reset_link;
    } else {
        $error = "This reset request has expired or already been processed.";
    }
}

// Mark as completed (after Oscar has sent the email)
if (isset($_GET['complete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE password_resets SET used = 1 WHERE id = $id");
    header("Location: reset_requests.php");
    exit;
}

// Get all pending reset requests
$requests = $conn->query("
    SELECT * FROM password_resets 
    WHERE expires_at > NOW() AND used = 0 
    ORDER BY created_at DESC
");

// Get counts for badges
$pending_count = $conn->query("SELECT COUNT(*) as count FROM password_resets WHERE notified = 0 AND expires_at > NOW() AND used = 0")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Requests - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .link-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #dee2e6;
        }
        .link-box input {
            width: 100%;
            padding: 10px;
            font-family: monospace;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        .copy-btn {
            background: #2E7D32;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .copy-btn:hover {
            background: #1B5E20;
        }
        .badge-pending {
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7em;
            margin-left: 10px;
        }
        .status-new {
            color: #ff9800;
            font-weight: bold;
        }
        .status-sent {
            color: #2E7D32;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                🔐 Password Reset Requests
                <?php if ($pending_count['count'] > 0): ?>
                    <span class="badge-pending"><?php echo $pending_count['count']; ?> new</span>
                <?php endif; ?>
            </h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
            </div>
        </div>

        <?php if ($generated_link): ?>
            <div class="success-message">
                <strong>✅ Reset Link Generated</strong>
                <div class="link-box">
                    <input type="text" id="resetLink" value="<?php echo $generated_link; ?>" readonly onclick="this.select()">
                    <br>
                    <button class="copy-btn" onclick="copyToClipboard()">📋 Copy Link</button>
                </div>
                <p><strong>Next steps:</strong></p>
                <ol>
                    <li>Copy the link above</li>
                    <li>Email it to the user</li>
                    <li>Click "Mark as Sent" below when done</li>
                </ol>
                <a href="?complete=1&id=<?php echo $_GET['id']; ?>" class="btn" style="background: #28a745;">✓ Mark as Sent</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Requested</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests && $requests->num_rows > 0): ?>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['expires_at'])); ?></td>
                                <td>
                                    <?php if ($row['notified']): ?>
                                        <span class="status-sent">✓ Link Generated</span>
                                    <?php else: ?>
                                        <span class="status-new">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$row['notified']): ?>
                                        <a href="?generate=1&id=<?php echo $row['id']; ?>" class="btn-small" style="background: #2E7D32;">Generate Link</a>
                                    <?php else: ?>
                                        <span style="color: #999;">Sent</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                No pending password reset requests.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            var copyText = document.getElementById('resetLink');
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
    </script>
</body>
</html>