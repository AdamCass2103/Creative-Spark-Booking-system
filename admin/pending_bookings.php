<?php
// Turn off error reporting to prevent file paths from showing
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection error. Please try again later.");
}

$admin_id = getCurrentAdminId();

$message = '';
$message_type = '';

// Handle approval
if (isset($_POST['approve_booking'])) {
    $session_id = (int)$_POST['session_id'];
    $user_id = (int)$_POST['user_id'];
    $result = approveBooking($session_id, $user_id, $admin_id);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

// Handle rejection
if (isset($_POST['reject_booking'])) {
    $session_id = (int)$_POST['session_id'];
    $user_id = (int)$_POST['user_id'];
    $reason = $_POST['rejection_reason'] ?? '';
    $result = rejectBooking($session_id, $user_id, $admin_id, $reason);
    
    $message = $result['message'];
    $message_type = 'success';
}

// Get pending approvals
$pending = getPendingApprovals();
// ===== START DEBUGGING =====
echo "<div style='background: #ffcccc; padding: 20px; margin: 20px; border: 3px solid red; border-radius: 10px;'>";
echo "<h2 style='color: red; margin-top: 0;'>🔍 DEBUG MODE - Database Check</h2>";

// Check 1: What does $pending contain?
echo "<h3>1. getPendingApprovals() Result:</h3>";
if ($pending === false) {
    echo "<p style='color: red;'>❌ getPendingApprovals() returned FALSE - there's an error in the function</p>";
} elseif ($pending === null) {
    echo "<p style='color: red;'>❌ getPendingApprovals() returned NULL</p>";
} elseif ($pending->num_rows === 0) {
    echo "<p style='color: orange;'>⚠️ getPendingApprovals() returned 0 rows</p>";
} else {
    echo "<p style='color: green;'>✅ getPendingApprovals() returned " . $pending->num_rows . " rows</p>";
    
    // Show the first row to see structure
    $first = $pending->fetch_assoc();
    echo "<p><strong>First row data:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    print_r($first);
    echo "</pre>";
    // Reset pointer
    $pending->data_seek(0);
}

// Check 2: Direct query to see all session_attendees records
echo "<h3>2. All records in session_attendees table:</h3>";
$all_query = "SELECT 
                sa.*,
                u.name,
                u.email,
                ts.session_date,
                ts.session_time,
                mt.tier_name
              FROM session_attendees sa
              LEFT JOIN users u ON sa.user_id = u.user_id
              LEFT JOIN training_sessions ts ON sa.session_id = ts.session_id
              LEFT JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
              ORDER BY sa.registered_at DESC";
$all_result = $conn->query($all_query);

if ($all_result && $all_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Session</th><th>User</th><th>Booking Status</th><th>Date</th><th>Training</th></tr>";
    while($row = $all_result->fetch_assoc()) {
        $status = $row['booking_status'];
        $highlight = ($status == 'pending' || $status == 'pending_approval') ? 'style="background: #ffffcc;"' : '';
        echo "<tr $highlight>";
        echo "<td>" . $row['session_id'] . "/" . $row['user_id'] . "</td>";
        echo "<td>" . $row['session_id'] . "</td>";
        echo "<td>" . ($row['name'] ?? 'Unknown') . " (ID: " . $row['user_id'] . ")</td>";
        echo "<td><strong>" . $row['booking_status'] . "</strong></td>";
        echo "<td>" . ($row['session_date'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['tier_name'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found in session_attendees table</p>";
}

// Check 3: Count by status
echo "<h3>3. Count by booking_status:</h3>";
$status_query = "SELECT booking_status, COUNT(*) as count FROM session_attendees GROUP BY booking_status";
$status_result = $conn->query($status_query);
if ($status_result && $status_result->num_rows > 0) {
    echo "<ul>";
    while($row = $status_result->fetch_assoc()) {
        echo "<li><strong>" . $row['booking_status'] . "</strong>: " . $row['count'] . " records</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No status data found</p>";
}

// Check 4: Show the exact query being used
echo "<h3>4. The query being used in getPendingApprovals():</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px;'>";
echo "SELECT 
    sa.session_id,
    sa.user_id,
    u.name as user_name,
    u.email,
    mt.tier_name,
    ts.session_date,
    ts.session_time,
    ts.max_attendees,
    sa.registered_at,
    (SELECT COUNT(*) FROM session_attendees WHERE session_id = ts.session_id AND booking_status = 'approved') as approved_count
FROM session_attendees sa
JOIN users u ON sa.user_id = u.user_id
JOIN training_sessions ts ON sa.session_id = ts.session_id
JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
WHERE sa.booking_status = 'pending_approval'
ORDER BY sa.registered_at ASC";
echo "</pre>";

echo "<h3>5. Try with IN ('pending', 'pending_approval') instead:</h3>";
$test_query = "
    SELECT 
        sa.session_id,
        sa.user_id,
        u.name as user_name,
        u.email,
        mt.tier_name,
        ts.session_date,
        ts.session_time,
        ts.max_attendees,
        sa.registered_at,
        sa.booking_status
    FROM session_attendees sa
    JOIN users u ON sa.user_id = u.user_id
    JOIN training_sessions ts ON sa.session_id = ts.session_id
    JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
    WHERE sa.booking_status IN ('pending', 'pending_approval')
    ORDER BY sa.registered_at ASC";
$test_result = $conn->query($test_query);

if ($test_result && $test_result->num_rows > 0) {
    echo "<p style='color: green;'>✅ This query found " . $test_result->num_rows . " records!</p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>User</th><th>Training</th><th>Date</th><th>Status</th></tr>";
    while($row = $test_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['user_name'] . "</td>";
        echo "<td>" . $row['tier_name'] . "</td>";
        echo "<td>" . $row['session_date'] . "</td>";
        echo "<td>" . $row['booking_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Query with IN found 0 records</p>";
}

echo "</div>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Bookings - Admin</title>
    <link rel="stylesheet" href="../css/pending_bookings.css">
</head>
<body>
    <div class="container pending-container">
        <div class="header">
            <h1>⏳ Pending Booking Approvals</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
                <a href="training_sessions.php" class="btn">📅 Training Sessions</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$pending || $pending->num_rows == 0): ?>
            <div class="empty-state">
                <h2>✨ All caught up!</h2>
                <p>No pending booking requests at the moment.</p>
            </div>
        <?php else: ?>
            <?php while($request = $pending->fetch_assoc()): 
                $spots_taken = $request['approved_count'];
                $spots_left = $request['max_attendees'] - $spots_taken;
                $is_almost_full = $spots_left <= 2;
            ?>
            <div class="request-card">
                <div class="request-header">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($request['user_name']); ?></span>
                        <span class="user-email">(<?php echo htmlspecialchars($request['email']); ?>)</span>
                    </div>
                    <span class="badge badge-warning">⏳ Awaiting Approval</span>
                </div>
                
                <div class="session-info">
                    <div class="info-row">
                        <span class="info-label">Training:</span>
                        <span><strong><?php echo htmlspecialchars($request['tier_name']); ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span><?php echo date('l, jS F Y', strtotime($request['session_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time:</span>
                        <span><?php echo date('g:i A', strtotime($request['session_time'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Capacity:</span>
                        <span>
                            <?php echo $spots_taken; ?>/<?php echo $request['max_attendees']; ?> approved
                            <?php if ($is_almost_full): ?>
                                <span class="spots-warning">(Only <?php echo $spots_left; ?> spots left!)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Requested:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($request['registered_at'])); ?></span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="session_id" value="<?php echo (int)$request['session_id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo (int)$request['user_id']; ?>">
                        <button type="submit" name="approve_booking" class="btn-approve"
                                onclick="return confirm('Approve this booking?')">
                            ✅ Approve
                        </button>
                    </form>
                    
                    <button type="button" class="btn-reject" 
                            onclick="showRejectForm(<?php echo (int)$request['session_id']; ?>, <?php echo (int)$request['user_id']; ?>)">
                        ❌ Reject
                    </button>
                </div>
                
                <!-- Rejection Form (Hidden by default) -->
                <div id="reject-form-<?php echo (int)$request['session_id']; ?>-<?php echo (int)$request['user_id']; ?>" class="rejection-form">
                    <form method="POST">
                        <input type="hidden" name="session_id" value="<?php echo (int)$request['session_id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo (int)$request['user_id']; ?>">
                        <label for="reason-<?php echo (int)$request['session_id']; ?>-<?php echo (int)$request['user_id']; ?>">Reason for rejection:</label>
                        <select name="rejection_reason" id="reason-<?php echo (int)$request['session_id']; ?>-<?php echo (int)$request['user_id']; ?>" class="rejection-reason">
                            <option value="Session is full">Session is full</option>
                            <option value="Not eligible (needs prerequisite training)">Not eligible (needs prerequisite)</option>
                            <option value="User already certified">User already certified</option>
                            <option value="Training cancelled">Training cancelled</option>
                            <option value="Other">Other</option>
                        </select>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="reject_booking" class="btn-confirm-reject">
                                Confirm Rejection
                            </button>
                            <button type="button" class="btn-cancel" onclick="hideRejectForm(<?php echo (int)$request['session_id']; ?>, <?php echo (int)$request['user_id']; ?>)">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function showRejectForm(session_id, user_id) {
            var form = document.getElementById('reject-form-' + session_id + '-' + user_id);
            if (form) {
                form.classList.add('show');
            }
        }
        
        function hideRejectForm(session_id, user_id) {
            var form = document.getElementById('reject-form-' + session_id + '-' + user_id);
            if (form) {
                form.classList.remove('show');
            }
        }
    </script>
</body>
</html>