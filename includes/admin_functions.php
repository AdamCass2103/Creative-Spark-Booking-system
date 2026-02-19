<?php
// ============================================
// ADMIN FUNCTIONS FOR ACTIVITY LOGGING
// ============================================

require_once 'db_connect.php';

/**
 * Log admin activity
 */
function logAdminActivity($admin_id, $action, $target_type, $target_id = null, $details = null) {
    global $conn;
    
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
    global $conn;
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
    global $conn;
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
?>