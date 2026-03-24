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

// Handle image requests specifically
if (strpos($path, '/images/') === 0) {
    $image_file = __DIR__ . '/..' . $path;
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
        readfile($image_file);
        exit;
    } else {
        error_log("Image not found at: " . $image_file);
    }
}

// Handle CSS requests
if (strpos($path, '/css/') === 0) {
    $css_file = __DIR__ . '/..' . $path;
    if (file_exists($css_file) && is_file($css_file)) {
        header('Content-Type: text/css');
        readfile($css_file);
        exit;
    }
}

// ========== ADD THIS FABMAN HANDLER ==========
// Handle FabMan requests (just for redirect - FabMan is external)
if ($path == '/fabman' || $path == '/fabman/') {
    header('Location: https://www.fabman.io/login');
    exit;
}
// ========== END FABMAN HANDLER ==========

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