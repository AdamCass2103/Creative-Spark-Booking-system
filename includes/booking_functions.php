<?php
// ============================================
// TRAINING BOOKING FUNCTIONS
// ============================================

require_once 'db_connect.php';
//require_once 'email_functions.php'; // We'll create this later

/**
 * Get all available training sessions for a user
 */
function getAvailableSessions($user_id) {
    global $conn;
    
    // Validate user_id
    if (!is_numeric($user_id) || $user_id <= 0) {
        return [];
    }
    
    // Get user's tier and training status
    $user = $conn->query("SELECT tier_id, needs_training FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
    $tier_id = $user['tier_id'] ?? 0;
    
    // Get completed trainings
    $completed = $conn->query("SELECT tier_id FROM user_training_completed WHERE user_id = $user_id");
    $completed_tiers = [];
    while($row = $completed->fetch_assoc()) {
        $completed_tiers[] = $row['tier_id'];
    }
    
    // Build tier filter
    $tier_filter = '';
    if ($tier_id > 0) {
        $tier_filter = "AND ts.tier_id <= $tier_id"; // Users can book their tier or lower
    }
    
    // Get available sessions
    $query = "
        SELECT ts.*, mt.tier_name, mt.tier_level,
               (SELECT COUNT(*) FROM session_attendees WHERE session_id = ts.session_id) as registered_count,
               (SELECT booking_status FROM session_attendees 
                WHERE session_id = ts.session_id AND user_id = $user_id) as user_status
        FROM training_sessions ts
        JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        WHERE ts.session_date >= CURDATE()
        $tier_filter
        AND ts.session_id NOT IN (
            SELECT session_id FROM session_attendees 
            WHERE user_id = $user_id AND booking_status IN ('approved', 'pending_approval')
        )
        ORDER BY ts.session_date ASC, ts.session_time ASC
    ";
    
    $result = $conn->query($query);
    $sessions = [];
    while($row = $result->fetch_assoc()) {
        $row['spots_left'] = $row['max_attendees'] - $row['registered_count'];
        $row['is_eligible'] = !in_array($row['tier_id'], $completed_tiers);
        $sessions[] = $row;
    }
    
    return $sessions;
}

/**
 * Get user's booking requests
 */
function getUserBookings($user_id) {
    global $conn;
    
    // Validate user_id
    if (!is_numeric($user_id) || $user_id <= 0) {
        return $conn->query("SELECT * FROM session_attendees WHERE 1=0"); // Return empty result
    }
    
    $query = "
        SELECT sa.*, ts.session_date, ts.session_time, ts.max_attendees,
               mt.tier_name, mt.tier_level
        FROM session_attendees sa
        JOIN training_sessions ts ON sa.session_id = ts.session_id
        JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        WHERE sa.user_id = $user_id
        ORDER BY 
            CASE sa.booking_status
                WHEN 'pending_approval' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                WHEN 'cancelled' THEN 4
            END,
            ts.session_date ASC
    ";
    
    return $conn->query($query);
}

/**
 * Request a training session
 */
function requestTraining($user_id, $session_id) {
    global $conn;
    
    // Validate inputs
    if (!is_numeric($user_id) || $user_id <= 0 || !is_numeric($session_id) || $session_id <= 0) {
        return ['success' => false, 'message' => 'Invalid user or session ID'];
    }
    
    // Check if already registered
    $check = $conn->query("SELECT * FROM session_attendees 
                           WHERE user_id = $user_id AND session_id = $session_id");
    if ($check->num_rows > 0) {
        return ['success' => false, 'message' => 'You already have a request for this session'];
    }
    
    // Check if session has space
    $session = $conn->query("SELECT * FROM training_sessions WHERE session_id = $session_id")->fetch_assoc();
    if (!$session) {
        return ['success' => false, 'message' => 'Session not found'];
    }
    
    $registered = $conn->query("SELECT COUNT(*) as count FROM session_attendees 
                                WHERE session_id = $session_id AND booking_status = 'approved'")->fetch_assoc()['count'];
    
    if ($registered >= $session['max_attendees']) {
        return ['success' => false, 'message' => 'This session is full'];
    }
    
    // Insert request - NO ID COLUMN NEEDED, using composite key
    $conn->query("INSERT INTO session_attendees (session_id, user_id, booking_status) 
                  VALUES ($session_id, $user_id, 'pending_approval')");
    
    // TODO: Send email notification to Oscar
    // sendAdminNotification('new_booking_request', $session_id, $user_id);
    
    return ['success' => true, 'message' => 'Booking request sent to Oscar'];
}

/**
 * Cancel a booking request - FIXED to use composite key
 */
function cancelBooking($session_id, $user_id) {
    global $conn;
    
    // Validate inputs
    if (!is_numeric($session_id) || $session_id <= 0 || !is_numeric($user_id) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Invalid parameters'];
    }
    
    // Using composite key (session_id, user_id) - NO id column used
    $conn->query("UPDATE session_attendees 
                  SET booking_status = 'cancelled' 
                  WHERE session_id = $session_id AND user_id = $user_id");
    
    if ($conn->affected_rows > 0) {
        return ['success' => true, 'message' => 'Booking cancelled'];
    } else {
        return ['success' => false, 'message' => 'Booking not found or already cancelled'];
    }
}

/**
 * Get pending approvals for admin
 */
function getPendingApprovals() {
    global $conn;
    
    $query = "
        SELECT sa.*, u.name as user_name, u.email, 
               ts.session_date, ts.session_time, ts.max_attendees,
               mt.tier_name,
               (SELECT COUNT(*) FROM session_attendees 
                WHERE session_id = ts.session_id AND booking_status = 'approved') as approved_count
        FROM session_attendees sa
        JOIN users u ON sa.user_id = u.user_id
        JOIN training_sessions ts ON sa.session_id = ts.session_id
        JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        WHERE sa.booking_status = 'pending_approval'
        ORDER BY ts.session_date ASC
    ";
    
    return $conn->query($query);
}

/**
 * Approve a booking request - FIXED to use composite key
 */
function approveBooking($session_id, $user_id, $admin_id) {
    global $conn;
    
    // Validate inputs
    if (!is_numeric($session_id) || $session_id <= 0 || !is_numeric($user_id) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Invalid session or user ID'];
    }
    if (!is_numeric($admin_id) || $admin_id <= 0) {
        return ['success' => false, 'message' => 'Invalid admin ID'];
    }
    
    // Get details for email using composite key
    $booking_result = $conn->query("
        SELECT sa.*, u.name as user_name, u.email, u.user_id,
               ts.session_date, ts.session_time, ts.session_id, mt.tier_name
        FROM session_attendees sa
        JOIN users u ON sa.user_id = u.user_id
        JOIN training_sessions ts ON sa.session_id = ts.session_id
        JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        WHERE sa.session_id = $session_id AND sa.user_id = $user_id
    ");
    
    if (!$booking_result || $booking_result->num_rows == 0) {
        return ['success' => false, 'message' => 'Booking not found'];
    }
    
    $booking = $booking_result->fetch_assoc();
    
    // Check if session has space
    $approved = $conn->query("SELECT COUNT(*) as count FROM session_attendees 
                              WHERE session_id = $session_id AND booking_status = 'approved'")->fetch_assoc()['count'];
    $session = $conn->query("SELECT max_attendees FROM training_sessions WHERE session_id = $session_id")->fetch_assoc();
    
    if ($approved >= $session['max_attendees']) {
        return ['success' => false, 'message' => 'Session is now full'];
    }
    
    // Update status using composite key
    $conn->query("UPDATE session_attendees 
                  SET booking_status = 'approved' 
                  WHERE session_id = $session_id AND user_id = $user_id");
    
    // Log admin action
    if (function_exists('logAdminActivity')) {
        logAdminActivity($admin_id, 'approve_booking', 'training', $session_id, 
                        "Approved booking for {$booking['user_name']} (Session: $session_id)");
    }
    
    // TODO: Send approval email to user
    // sendApprovalEmail($booking['email'], $booking['user_name'], $booking);
    
    return ['success' => true, 'message' => 'Booking approved', 'user_email' => $booking['email']];
}

/**
 * Reject a booking request - FIXED to use composite key
 */
function rejectBooking($session_id, $user_id, $admin_id, $reason = '') {
    global $conn;
    
    // Validate inputs
    if (!is_numeric($session_id) || $session_id <= 0 || !is_numeric($user_id) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Invalid session or user ID'];
    }
    if (!is_numeric($admin_id) || $admin_id <= 0) {
        return ['success' => false, 'message' => 'Invalid admin ID'];
    }
    
    // Get details using composite key
    $booking_result = $conn->query("
        SELECT sa.*, u.name as user_name, u.email
        FROM session_attendees sa
        JOIN users u ON sa.user_id = u.user_id
        WHERE sa.session_id = $session_id AND sa.user_id = $user_id
    ");
    
    if (!$booking_result || $booking_result->num_rows == 0) {
        return ['success' => false, 'message' => 'Booking not found'];
    }
    
    $booking = $booking_result->fetch_assoc();
    
    // Update status using composite key
    $reason_escaped = $conn->real_escape_string($reason);
    $conn->query("UPDATE session_attendees 
                  SET booking_status = 'rejected', rejection_reason = '$reason_escaped'
                  WHERE session_id = $session_id AND user_id = $user_id");
    
    // Log admin action
    if (function_exists('logAdminActivity')) {
        logAdminActivity($admin_id, 'reject_booking', 'training', $session_id, 
                        "Rejected booking for {$booking['user_name']} (Session: $session_id): $reason");
    }
    
    // TODO: Send rejection email to user
    // sendRejectionEmail($booking['email'], $booking['user_name'], $reason);
    
    return ['success' => true, 'message' => 'Booking rejected'];
}

/**
 * Get booking status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'pending_approval' => '<span class="badge badge-warning">⏳ Pending Approval</span>',
        'approved' => '<span class="badge badge-success">✅ Approved</span>',
        'rejected' => '<span class="badge badge-danger">❌ Rejected</span>',
        'cancelled' => '<span class="badge badge-secondary">🗑️ Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge">Unknown</span>';
}
?>