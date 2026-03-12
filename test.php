<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing...<br>";

$config_path = __DIR__ . '/includes/config.php';
echo "Looking for config at: " . $config_path . "<br>";

if (file_exists($config_path)) {
    echo "config.php EXISTS!<br>";
    require_once $config_path;
    echo "config.php loaded<br>";
    
    if (defined('BASE_PATH')) {
        echo "BASE_PATH: " . BASE_PATH . "<br>";
    } else {
        echo "BASE_PATH not defined<br>";
    }
    
    if (defined('SITE_URL')) {
        echo "SITE_URL: " . SITE_URL . "<br>";
    } else {
        echo "SITE_URL not defined<br>";
    }
    
    if (function_exists('base_path')) {
        echo "base_path() function exists<br>";
        echo "Test base_path('css/landing.css'): " . base_path('css/landing.css') . "<br>";
    } else {
        echo "base_path() function NOT found<br>";
    }
} else {
    echo "config.php NOT FOUND at: " . $config_path . "<br>";
    echo "Contents of includes directory:<br>";
    
    $includes_path = __DIR__ . '/includes';
    if (is_dir($includes_path)) {
        $files = scandir($includes_path);
        foreach ($files as $file) {
            echo "- $file<br>";
        }
    } else {
        echo "includes directory not found at: " . $includes_path . "<br>";
    }
}
?>