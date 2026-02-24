<?php
require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
require_once '../includes/admin_functions.php';
requireAdmin();

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$admin_id = getCurrentAdminId();

$message = '';
$message_type = '';

// Handle approval
if (isset($_POST['approve_booking'])) {
    $attendee_id = $_POST['attendee_id'];
    $result = approveBooking($attendee_id, $admin_id);
    
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
    $attendee_id = $_POST['attendee_id'];
    $reason = $_POST['rejection_reason'] ?? '';
    $result = rejectBooking($attendee_id, $admin_id, $reason);
    
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
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .pending-container {
            padding: 20px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .request-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #ff9800;
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .user-info {
            font-size: 1.1em;
        }
        
        .user-name {
            font-weight: bold;
            color: #333;
        }
        
        .user-email {
            color: #666;
            font-size: 0.9em;
        }
        
        .session-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .info-row {
            display: flex;
            margin: 8px 0;
        }
        
        .info-label {
            width: 100px;
            font-weight: bold;
            color: #666;
        }
        
        .spots-warning {
            color: #dc3545;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-approve:hover {
            background: #218838;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-reject:hover {
            background: #c82333;
        }
        
        .rejection-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f8d7da;
            border-radius: 8px;
        }
        
        .rejection-form.show {
            display: block;
        }
        
        .rejection-reason {
            width: 100%;
            padding: 10px;
            border: 1px solid #dc3545;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .btn-confirm-reject {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
    </style>
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
                        <span class="user-name"><?php echo $request['user_name']; ?></span>
                        <span class="user-email">(<?php echo $request['email']; ?>)</span>
                    </div>
                    <span class="badge badge-warning">⏳ Awaiting Approval</span>
                </div>
                
                <div class="session-info">
                    <div class="info-row">
                        <span class="info-label">Training:</span>
                        <span><strong><?php echo $request['tier_name']; ?></strong></span>
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
                        <input type="hidden" name="attendee_id" value="<?php echo $request['id']; ?>">
                        <button type="submit" name="approve_booking" class="btn-approve"
                                onclick="return confirm('Approve this booking?')">
                            ✅ Approve
                        </button>
                    </form>
                    
                    <button type="button" class="btn-reject" 
                            onclick="showRejectForm(<?php echo $request['id']; ?>)">
                        ❌ Reject
                    </button>
                </div>
                
                <!-- Rejection Form (Hidden by default) -->
                <div id="reject-form-<?php echo $request['id']; ?>" class="rejection-form">
                    <form method="POST">
                        <input type="hidden" name="attendee_id" value="<?php echo $request['id']; ?>">
                        <label for="reason-<?php echo $request['id']; ?>">Reason for rejection:</label>
                        <select name="rejection_reason" id="reason-<?php echo $request['id']; ?>" class="rejection-reason">
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
                            <button type="button" class="btn-cancel" onclick="hideRejectForm(<?php echo $request['id']; ?>)">
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
        function showRejectForm(id) {
            document.getElementById('reject-form-' + id).classList.add('show');
        }
        
        function hideRejectForm(id) {
            document.getElementById('reject-form-' + id).classList.remove('show');
        }
    </script>
</body>
</html>