<?php
require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
requireLogin();

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();

$message = '';
$message_type = '';

// Handle booking request
if (isset($_POST['request_booking'])) {
    $session_id = $_POST['session_id'];
    $result = requestTraining($user_id, $session_id);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

// Get available sessions
$available_sessions = getAvailableSessions($user_id);

// Get user's bookings
$user_bookings = getUserBookings($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Training - Creative Spark</title>
    <link rel="stylesheet" href="../css/book_training.css">
</head>
<body>
    <div class="booking-container">
        <!-- Header -->
        <div class="page-header">
            <h1>📅 Book Training</h1>
            <div>
                <a href="dashboard.php" class="btn back-btn">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter Section (Optional) -->
        <div class="filter-section">
            <h3>🔍 Filter Sessions</h3>
            <div class="filter-grid">
                <select id="machineFilter" class="filter-select">
                    <option value="all">All Machines</option>
                    <option value="Laser">Laser Cutters</option>
                    <option value="3D">3D Printers</option>
                    <option value="CNC">CNC</option>
                    <option value="Vinyl">Vinyl Cutters</option>
                </select>
                
                <select id="dateFilter" class="filter-select">
                    <option value="all">All Dates</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
        </div>
        
        <!-- Available Sessions -->
        <h2>📋 Available Training Sessions</h2>
        
        <?php if (empty($available_sessions)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                <p style="font-size: 1.2em; color: #666;">No training sessions available right now.</p>
                <p style="color: #999;">Check back later or contact Oscar.</p>
            </div>
        <?php else: ?>
            <div class="sessions-grid">
                <?php foreach ($available_sessions as $session): 
                    $spots = $session['spots_left'];
                    $spots_class = $spots > 3 ? 'spots-high' : ($spots > 1 ? 'spots-medium' : 'spots-low');
                    $is_full = $spots <= 0;
                ?>
                <div class="session-card <?php echo $is_full ? 'full' : ''; ?>">
                    <div class="session-header">
                        <span class="session-title"><?php echo $session['tier_name']; ?></span>
                        <span class="tier-badge">Tier <?php echo $session['tier_level']; ?></span>
                    </div>
                    
                    <div class="session-details">
                        <div class="detail-row">
                            <span class="detail-icon">📅</span>
                            <span><?php echo date('l, jS F Y', strtotime($session['session_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-icon">⏰</span>
                            <span><?php echo date('g:i A', strtotime($session['session_time'])); ?></span>
                        </div>
                        <?php if ($session['notes']): ?>
                        <div class="detail-row">
                            <span class="detail-icon">📝</span>
                            <span><?php echo $session['notes']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
                        <span class="spots-left <?php echo $spots_class; ?>">
                            🎯 <?php echo $spots; ?> spot<?php echo $spots != 1 ? 's' : ''; ?> left
                        </span>
                        <span>👥 Max: <?php echo $session['max_attendees']; ?></span>
                    </div>
                    
                    <?php if ($is_full): ?>
                        <button class="btn-request" disabled>Session Full</button>
                    <?php elseif (!$session['is_eligible']): ?>
                        <button class="btn-request" disabled>Already Certified</button>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                            <button type="submit" name="request_booking" class="btn-request">
                                📌 Request Booking
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- My Bookings -->
        <h2 style="margin-top: 40px;">📋 My Booking Requests</h2>
        
        <?php if ($user_bookings->num_rows == 0): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                <p style="color: #666;">You haven't made any booking requests yet.</p>
            </div>
        <?php else: ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Training</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $user_bookings->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $booking['tier_name']; ?></strong></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($booking['session_date'])); ?> 
                            at <?php echo date('g:i A', strtotime($booking['session_time'])); ?>
                        </td>
                        <td>
                            <?php 
                            $status = $booking['booking_status'];
                            $badge_class = 'badge-warning';
                            $status_text = 'Pending';
                            
                            if ($status == 'approved') {
                                $badge_class = 'badge-success';
                                $status_text = 'Approved ✅';
                            } elseif ($status == 'rejected') {
                                $badge_class = 'badge-danger';
                                $status_text = 'Rejected ❌';
                            } elseif ($status == 'cancelled') {
                                $badge_class = 'badge-secondary';
                                $status_text = 'Cancelled';
                            }
                            ?>
                            <span class="badge <?php echo $badge_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                            <?php if ($status == 'rejected' && $booking['rejection_reason']): ?>
                                <br><small><?php echo $booking['rejection_reason']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status == 'pending_approval'): ?>
                            <form method="POST" action="cancel_booking.php" style="display: inline;">
                                <input type="hidden" name="session_id" value="<?php echo $booking['session_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                <button type="submit" name="cancel_booking" class="btn-cancel" 
                                        onclick="return confirm('Cancel this booking request?')">
                                    Cancel
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script>
        // Simple filter functionality (can be enhanced later)
        document.getElementById('machineFilter').addEventListener('change', function() {
            // This would filter the sessions - we can add this later
            console.log('Filter by machine:', this.value);
        });
        
        document.getElementById('dateFilter').addEventListener('change', function() {
            console.log('Filter by date:', this.value);
        });
    </script>
</body>
</html>