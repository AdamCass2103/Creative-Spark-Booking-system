<?php
// EMERGENCY DEBUG - REMOVE AFTER FIXING
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Vercel Path Debugger</title></head><body>";
echo "<h1>🔍 VERCEL PATH DEBUGGER</h1>";
echo "<pre>";

echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "Current script: " . __FILE__ . "\n\n";

// Scan current directory
echo "📁 Files in " . __DIR__ . ":\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "  - " . $file . (is_dir(__DIR__ . '/' . $file) ? " (DIR)" : "") . "\n";
    }
}

// Look for images anywhere
echo "\n🔍 Searching for 'images' folder...\n";

// Check common locations
$locations = [
    __DIR__ . '/images',
    __DIR__ . '/../images',
    $_SERVER['DOCUMENT_ROOT'] . '/images',
    dirname(__DIR__) . '/images',
    __DIR__ . '/public/images',
    dirname(__DIR__) . '/public/images',
    '/var/task/user/images',
    '/var/task/user/public/images'
];

foreach ($locations as $location) {
    if (file_exists($location)) {
        echo "✅ FOUND: " . $location . "\n";
        if (is_dir($location)) {
            $img_files = scandir($location);
            echo "   Files: " . implode(', ', array_diff($img_files, ['.', '..'])) . "\n";
        }
    } else {
        echo "❌ Not found: " . $location . "\n";
    }
}

echo "</pre>";
echo "</body></html>";
exit; // Stop here
?>