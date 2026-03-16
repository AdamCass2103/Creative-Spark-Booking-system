<?php
// ============================================
// HELPER FUNCTIONS ONLY - NO AUTH FUNCTIONS!
// ============================================

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

// ============================================
// DATABASE CONNECTION HELPER
// ============================================

function getDatabaseConnection() {
    static $conn = null;
    
    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            if (getenv('VERCEL_ENV')) {
                $conn = mysqli_init();
                
                // Handle SSL certificate
                if (getenv('CA_CERT')) {
                    $cert_path = '/tmp/ca.pem';
                    file_put_contents($cert_path, getenv('CA_CERT'));
                    $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
                } else {
                    $cert_path = __DIR__ . '/certs/ca.pem';
                    if (file_exists($cert_path)) {
                        $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
                    }
                }
                
                $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, 25849, NULL, MYSQLI_CLIENT_SSL);
            } else {
                // Local XAMPP connection
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    return $conn;
}

// REMOVED: formatActivityTime() - now only in admin_functions.php
?>