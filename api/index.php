<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Log what we're trying to load
error_log("Requested path: " . $path);

// Serve static files directly from public folder
$public_file = __DIR__ . '/../public' . $path;

// Check if it's a static file request (css, js, images)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $path)) {
    if (file_exists($public_file) && is_file($public_file)) {
        // Set proper content type
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
        
        // Add cache headers
        header('Cache-Control: public, max-age=86400');
        readfile($public_file);
        exit;
    } else {
        error_log("Static file not found: " . $public_file);
        // Try root directory as fallback
        $root_file = __DIR__ . '/../' . ltrim($path, '/');
        if (file_exists($root_file) && is_file($root_file)) {
            $ext = pathinfo($root_file, PATHINFO_EXTENSION);
            if (isset($mime_types[$ext])) {
                header('Content-Type: ' . $mime_types[$ext]);
            }
            readfile($root_file);
            exit;
        }
    }
}

// For everything else, load the main index.php
require __DIR__ . '/../public/index.php';
?>