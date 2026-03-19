<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// DATABASE CONFIGURATION
// ============================================

if (getenv('VERCEL_ENV')) {
    // We're on Vercel
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
    define('ENVIRONMENT', 'production');
} else {
    // We're on local XAMPP
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'booking_system');
    define('ENVIRONMENT', 'local');
}

// ============================================
// PATH CONFIGURATION
// ============================================

if (ENVIRONMENT === 'production') {
    // On Vercel
    define('BASE_PATH', '');
    define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} else {
    // On local
    define('BASE_PATH', '/booking-system');
    define('SITE_URL', 'http://localhost/booking-system');
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function asset_url($path) {
    return SITE_URL . '/' . ltrim($path, '/');
}

function base_path($path = '') {
    if (empty($path)) {
        return BASE_PATH;
    }
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Debug - remove after testing
error_log("Config loaded - Environment: " . ENVIRONMENT);
error_log("BASE_PATH: " . BASE_PATH);
error_log("SITE_URL: " . SITE_URL);
$conn = getDatabaseConnection();
?>