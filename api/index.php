<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Log what we're trying to load
error_log("Requested path: " . $path);

// Remove base path if it exists (for local compatibility)
$path = preg_replace('#^/booking-system#', '', $path);

// If it's the root or empty, load public/index.php
if ($path == '/' || $path == '') {
    require __DIR__ . '/../public/index.php';
    exit;
}

// Handle CSS requests from root/css folder (outside public)
if (strpos($path, '/css/') === 0) {
    $css_file = __DIR__ . '/..' . $path;
    error_log("Looking for CSS at: " . $css_file);
    
    if (file_exists($css_file) && is_file($css_file)) {
        header('Content-Type: text/css');
        header('Cache-Control: public, max-age=86400');
        readfile($css_file);
        exit;
    } else {
        error_log("CSS not found at: " . $css_file);
    }
}

// Handle image requests from public/images folder
if (strpos($path, '/images/') === 0) {
    $image_file = __DIR__ . '/../public' . $path;
    error_log("Looking for image at: " . $image_file);
    
    if (file_exists($image_file) && is_file($image_file)) {
        $ext = pathinfo($image_file, PATHINFO_EXTENSION);
        $mime_types = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        header('Cache-Control: public, max-age=86400');
        readfile($image_file);
        exit;
    } else {
        error_log("Image not found at: " . $image_file);
    }
}

// Check if it's a PHP file in member folder
$member_file = __DIR__ . '/../member/' . ltrim($path, '/');
if (file_exists($member_file) && is_file($member_file) && pathinfo($member_file, PATHINFO_EXTENSION) == 'php') {
    require $member_file;
    exit;
}

// Check if it's a PHP file in admin folder
$admin_file = __DIR__ . '/../admin/' . ltrim($path, '/');
if (file_exists($admin_file) && is_file($admin_file) && pathinfo($admin_file, PATHINFO_EXTENSION) == 'php') {
    require $admin_file;
    exit;
}

// Check if it's a PHP file in signup folder
$signup_file = __DIR__ . '/../signup/' . ltrim($path, '/');
if (file_exists($signup_file) && is_file($signup_file) && pathinfo($signup_file, PATHINFO_EXTENSION) == 'php') {
    require $signup_file;
    exit;
}

// Check if it's a PHP file in public folder
$public_file = __DIR__ . '/../public/' . ltrim($path, '/');
if (file_exists($public_file) && is_file($public_file) && pathinfo($public_file, PATHINFO_EXTENSION) == 'php') {
    require $public_file;
    exit;
}

// If we get here, show 404 or load index.php
http_response_code(404);
echo "Page not found";
?>