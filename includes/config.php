<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Check if config loaded
echo "<!-- Config loaded successfully -->";
echo "<!-- BASE_PATH: " . BASE_PATH . " -->";
echo "<!-- SITE_URL: " . SITE_URL . " -->";

// Test if base_path function exists
if (function_exists('base_path')) {
    echo "<!-- base_path() function exists -->";
    echo "<!-- Test base_path: " . base_path('css/landing.css') . " -->";
} else {
    echo "<!-- ERROR: base_path() function NOT FOUND -->";
}
// Database configuration
if (getenv('VERCEL_ENV')) {
    // We're on Vercel
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
    define('BASE_PATH', '');
    define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} else {
    // We're on local XAMPP
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'booking_system');
    define('BASE_PATH', '/booking-system');
    define('SITE_URL', 'http://localhost/booking-system');
}

// Helper function for assets
function asset_url($path) {
    return SITE_URL . '/' . ltrim($path, '/');
}

// Helper function for paths
function base_path($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}
?>