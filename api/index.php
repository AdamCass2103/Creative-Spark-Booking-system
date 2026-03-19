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

// If it's the root or empty, load index.php
if ($path == '/' || $path == '') {
    require __DIR__ . '/../index.php';
    exit;
}

// First check if the file exists in the public/images folder
$public_file = __DIR__ . '/../public' . $path;
if (file_exists($public_file) && is_file($public_file)) {
    // Serve file from public folder
    $ext = pathinfo($public_file, PATHINFO_EXTENSION);
    $mime_types = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
    ];
    
    if (isset($mime_types[$ext])) {
        header('Content-Type: ' . $mime_types[$ext]);
    }
    readfile($public_file);
    exit;
}

// Check if the file exists in the root directory
$root_file = __DIR__ . '/../' . ltrim($path, '/');
if (file_exists($root_file) && is_file($root_file)) {
    // If it's a PHP file, require it
    if (pathinfo($root_file, PATHINFO_EXTENSION) == 'php') {
        require $root_file;
    } else {
        // For non-PHP files, serve them with proper content type
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
        ];
        
        $ext = pathinfo($root_file, PATHINFO_EXTENSION);
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        readfile($root_file);
    }
    exit;
}

// If we get here, try to load index.php as a fallback
require __DIR__ . '/../index.php';
?>