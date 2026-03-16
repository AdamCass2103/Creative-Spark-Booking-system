<?php
// ============================================
// ADMIN FUNCTIONS FOR ACTIVITY LOGGING
// ============================================

// No direct database connection here - will use getDatabaseConnection()

/**
 * Log admin activity
 */
function logAdminActivity($admin_id, $action, $target_type, $target_id = null, $details = null) {
    $conn = getDatabaseConnection();
    if (!$conn) return false;
    
    // Get admin name
    $admin_result = $conn->query("SELECT name FROM admin_users WHERE admin_id = $admin_id");
    $admin = $admin_result->fetch_assoc();
    $admin_name = $admin['name'] ?? 'Unknown';
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Escape details if provided
    $details = $details ? "'" . $conn->real_escape_string($details) . "'" : "NULL";
    $target_id = $target_id ?? "NULL";
    
    $conn->query("INSERT INTO admin_activity_log 
                  (admin_id, admin_name, action, target_type, target_id, details, ip_address) 
                  VALUES ($admin_id, '$admin_name', '$action', '$target_type', $target_id, $details, '$ip_address')");
    
    return $conn->insert_id;
}

/**
 * Get recent admin activity
 */
function getRecentActivity($limit = 20) {
    $conn = getDatabaseConnection();
    if (!$conn) return [];
    
    $result = $conn->query("SELECT * FROM admin_activity_log 
                            ORDER BY created_at DESC 
                            LIMIT $limit");
    $activities = [];
    while($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    return $activities;
}

/**
 * Get activity for specific target (user, session, etc.)
 */
function getTargetActivity($target_type, $target_id, $limit = 50) {
    $conn = getDatabaseConnection();
    if (!$conn) return [];
    
    $result = $conn->query("SELECT * FROM admin_activity_log 
                            WHERE target_type = '$target_type' AND target_id = $target_id
                            ORDER BY created_at DESC 
                            LIMIT $limit");
    $activities = [];
    while($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    return $activities;
}

/**
 * Format activity for display
 */
function formatActivityTime($timestamp) {
    if (!$timestamp) return 'Unknown';
    
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $diff = $current_time - $time_ago;
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return date('M j, Y', $time_ago);
    }
}

// ============================================
// PENDING APPROVALS FUNCTIONS
// ============================================

function getPendingApprovals() {
    $conn = getDatabaseConnection();
    if (!$conn) return null;
    
    $query = "
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
            (SELECT COUNT(*) FROM session_attendees WHERE session_id = ts.session_id AND booking_status = 'approved') as approved_count
        FROM session_attendees sa
        JOIN users u ON sa.user_id = u.user_id
        JOIN training_sessions ts ON sa.session_id = ts.session_id
        JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
        WHERE sa.booking_status = 'pending_approval'
        ORDER BY sa.registered_at ASC
    ";
    
    return $conn->query($query);
}

function approveBooking($session_id, $user_id, $admin_id) {
    $conn = getDatabaseConnection();
    if (!$conn) return ['success' => false, 'message' => 'Database error'];
    
    // Update booking status
    $conn->query("
        UPDATE session_attendees 
        SET booking_status = 'approved',
            approved_by = $admin_id,
            approved_at = NOW()
        WHERE session_id = $session_id AND user_id = $user_id
    ");
    
    // Log activity
    logAdminActivity($admin_id, 'approve_booking', 'booking', $session_id, "Approved booking for user $user_id");
    
    return ['success' => true, 'message' => 'Booking approved successfully'];
}

function rejectBooking($session_id, $user_id, $admin_id, $reason = '') {
    $conn = getDatabaseConnection();
    if (!$conn) return ['success' => false, 'message' => 'Database error'];
    
    // Update booking status
    $conn->query("
        UPDATE session_attendees 
        SET booking_status = 'rejected',
            rejection_reason = '" . $conn->real_escape_string($reason) . "',
            approved_by = $admin_id,
            approved_at = NOW()
        WHERE session_id = $session_id AND user_id = $user_id
    ");
    
    // Log activity
    logAdminActivity($admin_id, 'reject_booking', 'booking', $session_id, "Rejected booking for user $user_id: $reason");
    
    return ['success' => true, 'message' => 'Booking rejected'];
}
?>