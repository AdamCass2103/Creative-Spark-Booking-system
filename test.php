<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing...<br>";

if (file_exists('includes/config.php')) {
    echo "config.php exists<br>";
    require_once 'includes/config.php';
    echo "config.php loaded<br>";
    echo "BASE_PATH: " . BASE_PATH . "<br>";
    echo "SITE_URL: " . SITE_URL . "<br>";
} else {
    echo "config.php NOT FOUND in includes/<br>";
    echo "Current directory: " . __DIR__ . "<br>";
    echo "Files in current directory:<br>";
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        echo "- $file<br>";
    }
}
?>