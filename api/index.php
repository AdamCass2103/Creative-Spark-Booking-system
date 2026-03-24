<?php
// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if it exists
$path = preg_replace('#^/booking-system#', '', $path);

// Serve static files from public folder
$public_file = __DIR__ . '/../public' . $path;
if (file_exists($public_file) && is_file($public_file)) {
    $ext = pathinfo($public_file, PATHINFO_EXTENSION);
    $mime_types = [
        'css' => 'text/css',
        'js' => 'application/javascript',
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
    readfile($public_file);
    exit;
}

// Check if it's a PHP file in the root
$root_file = __DIR__ . '/../' . ltrim($path, '/');
if (file_exists($root_file) && is_file($root_file) && pathinfo($root_file, PATHINFO_EXTENSION) == 'php') {
    require $root_file;
    exit;
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

// If it's the root, load index.php
if ($path == '/' || $path == '') {
    require __DIR__ . '/../public/index.php';
    exit;
}

// Default fallback
require __DIR__ . '/../public/index.php';
?>