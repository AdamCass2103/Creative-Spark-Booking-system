<?php
// ============================================
// TRAINING BOOKING FUNCTIONS
// ============================================

require_once __DIR__ . '/config.php';

/**
 * Get all available training sessions for a user
 */
function getAvailableSessions($user_id) {
    global $conn;
    
    // Validate user_id
    if (!is_numeric($user_id) || $user_id <= 0) {
        return [];
    }
    
    // Get completed trainings (by tier_id since that's what your table uses)
    $completed = $conn->query("SELECT tier_id FROM user_training_completed WHERE user_id = $user_id");
    $completed_tiers = [];
    while($row = $completed->fetch_assoc()) {
        $completed_tiers[] = $row['tier_id'];
    }
    
    // Get sessions user already has bookings for (approved or pending)
    $booked_sessions = [];
    $booked_result = $conn->query("
        SELECT session_id FROM session_attendees 
        WHERE user_id = $user_id AND booking_status IN ('approved', 'pending_approval')
    ");
    while($row = $booked_result->fetch_assoc()) {
        $booked_sessions[] = $row['session_id'];
    }
    
    // Build the exclusion list
    $exclude_clause = '';
    if (!empty($booked_sessions)) {
        $exclude_clause = "AND ts.session_id NOT IN (" . implode(',', $booked_sessions) . ")";
    }
    
    // Get available sessions - show machine-based sessions
    $query = "
        SELECT ts.*, 
               COALESCE(m.machine_name, 'Training Session') as machine_name,
               m.machine_category,
               (SELECT COUNT(*) FROM session_attendees WHERE session_id = ts.session_id) as registered_count
        FROM training_sessions ts
        LEFT JOIN machines m ON ts.machine_id = m.machine_id
        WHERE ts.session_date >= CURDATE()
        $exclude_clause
        ORDER BY ts.session_date ASC, ts.session_time ASC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        error_log("getAvailableSessions query error: " . $conn->error);
        return [];
    }
    
    $sessions = [];
    while($row = $result->fetch_assoc()) {
        $row['spots_left'] = $row['max_attendees'] - $row['registered_count'];
        // Check eligibility based on tier_id (since your training sessions might have tier_id)
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
    
    if (!is_numeric($user_id) || $user_id <= 0) {
        return $conn->query("SELECT * FROM session_attendees WHERE 1=0");
    }
    
    $query = "
        SELECT sa.*, 
               ts.session_date, ts.session_time, ts.max_attendees,
               COALESCE(mt.tier_name, m.machine_name, 'Training Session') as tier_name
        FROM session_attendees sa
        JOIN training_sessions ts ON sa.session_id = ts.session_id
        LEFT JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        LEFT JOIN machines m ON ts.machine_id = m.machine_id
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
    
    // Insert request
    $conn->query("INSERT INTO session_attendees (session_id, user_id, booking_status) 
                  VALUES ($session_id, $user_id, 'pending_approval')");
    
    return ['success' => true, 'message' => 'Booking request sent to Oscar'];
}

/**
 * Cancel a booking request
 */
function cancelBooking($session_id, $user_id) {
    global $conn;
    
    if (!is_numeric($session_id) || $session_id <= 0 || !is_numeric($user_id) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Invalid parameters'];
    }
    
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
               COALESCE(mt.tier_name, m.machine_name) as training_name,
               (SELECT COUNT(*) FROM session_attendees 
                WHERE session_id = ts.session_id AND booking_status = 'approved') as approved_count
        FROM session_attendees sa
        JOIN users u ON sa.user_id = u.user_id
        JOIN training_sessions ts ON sa.session_id = ts.session_id
        LEFT JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        LEFT JOIN machines m ON ts.machine_id = m.machine_id
        WHERE sa.booking_status = 'pending_approval'
        ORDER BY ts.session_date ASC
    ";
    
    return $conn->query($query);
}

/**
 * Approve a booking request
 */
function approveBooking($session_id, $user_id, $admin_id) {
    global $conn;
    
    if (!is_numeric($session_id) || $session_id <= 0 || !is_numeric($user_id) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Invalid session or user ID'];
    }
    
    // Check if session has space
    $approved = $conn->query("SELECT COUNT(*) as count FROM session_attendees 
                              WHERE session_id = $session_id AND booking_status = 'approved'")->fetch_assoc()['count'];
    $session = $conn->query("SELECT max_attendees FROM training_sessions WHERE session_id = $session_id")->fetch_assoc();
    
    if ($approved >= $session['max_attendees']) {
        return ['success' => false, 'message' => 'Session is now full'];
    }
    
    // Update status
    $conn->query("UPDATE session_attendees 
                  SET booking_status = 'approved' 
                  WHERE session_id = $session_id AND user_id = $user_id");
    
    return ['success' => true, 'message' => 'Booking approved'];
}

/**
 * Reject a booking request
 */
function rejectBooking($session_id, $user_id, $admin_id, $reason = '') {
    global $conn;
    
    if (!is_numeric($session_id) || $session_id <= 0 || !is_numeric($user_id) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Invalid session or user ID'];
    }
    
    $reason_escaped = $conn->real_escape_string($reason);
    $conn->query("UPDATE session_attendees 
                  SET booking_status = 'rejected', rejection_reason = '$reason_escaped'
                  WHERE session_id = $session_id AND user_id = $user_id");
    
    return ['success' => true, 'message' => 'Booking rejected'];
}
?>