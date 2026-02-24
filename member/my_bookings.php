<?php
require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
requireLogin();

$user_id = getCurrentUserId();
$bookings = getUserBookings($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Creative Spark</title>
    <link rel="stylesheet" href="../css/member-dashboard.css">
    <style>
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #2E7D32;
        }
        .booking-card.pending { border-left-color: #ff9800; }
        .booking-card.approved { border-left-color: #4caf50; }
        .booking-card.rejected { border-left-color: #f44336; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="dashboard.php" class="btn back-btn">← Back to Dashboard</a>
            <h1>📋 My Training Bookings</h1>
        </div>
        
        <?php if ($bookings->num_rows == 0): ?>
            <p style="text-align: center; padding: 40px;">You haven't made any bookings yet.</p>
        <?php else: ?>
            <?php while($booking = $bookings->fetch_assoc()): 
                $status_class = '';
                switch($booking['booking_status']) {
                    case 'pending_approval': $status_class = 'status-pending'; break;
                    case 'approved': $status_class = 'status-approved'; break;
                    case 'rejected': $status_class = 'status-rejected'; break;
                    default: $status_class = 'status-cancelled';
                }
            ?>
            <div class="booking-card <?php echo $booking['booking_status']; ?>">
                <h3><?php echo $booking['tier_name']; ?></h3>
                <p>📅 <?php echo date('l, jS F Y', strtotime($booking['session_date'])); ?> at <?php echo date('g:i A', strtotime($booking['session_time'])); ?></p>
                <p><span class="status-badge <?php echo $status_class; ?>">
                    <?php 
                    echo ucfirst(str_replace('_', ' ', $booking['booking_status']));
                    ?>
                </span></p>
                <?php if ($booking['rejection_reason']): ?>
                    <p style="color: #f44336;">Reason: <?php echo $booking['rejection_reason']; ?></p>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>