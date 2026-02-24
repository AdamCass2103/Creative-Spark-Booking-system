<?php
// Turn off error reporting to prevent file paths from showing
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
require_once '../includes/admin_functions.php';
requireAdmin();

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$admin_id = getCurrentAdminId();

$message = '';
$message_type = '';

// Handle approval - UPDATED to use session_id and user_id
if (isset($_POST['approve_booking'])) {
    $session_id = $_POST['session_id'];
    $user_id = $_POST['user_id'];
    $result = approveBooking($session_id, $user_id, $admin_id);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

// Handle rejection - UPDATED to use session_id and user_id
if (isset($_POST['reject_booking'])) {
    $session_id = $_POST['session_id'];
    $user_id = $_POST['user_id'];
    $reason = $_POST['rejection_reason'] ?? '';
    $result = rejectBooking($session_id, $user_id, $admin_id, $reason);
    
    $message = $result['message'];
    $message_type = 'success';
}

// Get pending approvals
$pending = getPendingApprovals();
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
        
        <?php if ($pending->num_rows == 0): ?>
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
                    <!-- UPDATED: Using session_id and user_id instead of attendee_id -->
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
                
                <!-- Rejection Form (Hidden by default) - UPDATED to use session_id and user_id -->
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