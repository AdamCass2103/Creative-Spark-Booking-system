<?php
require_once '../includes/auth.php';
requireLogin();

// Get user info
$user_id = getCurrentUserId();
$conn = new mysqli('localhost', 'root', '', 'booking_system');
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

// Mock video data (replace with real links later)
$videos = [
    ['title' => 'Laser Cutter Basics', 'duration' => '12:34', 'url' => '#'],
    ['title' => '3D Printer Setup', 'duration' => '8:21', 'url' => '#'],
    ['title' => 'CNC Router Safety', 'duration' => '15:45', 'url' => '#'],
    ['title' => 'Vinyl Cutting Guide', 'duration' => '6:18', 'url' => '#'],
];

$wiki_articles = [
    ['title' => 'Machine Safety Guidelines', 'new' => true],
    ['title' => 'Material Selection Guide', 'new' => false],
    ['title' => 'Troubleshooting Common Issues', 'new' => false],
    ['title' => 'Project Ideas & Inspiration', 'new' => false],
];

// Get upcoming training
$upcoming = $conn->query("
    SELECT ts.*, mt.tier_name 
    FROM training_sessions ts
    JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
    WHERE ts.session_date >= CURDATE()
    ORDER BY ts.session_date ASC
    LIMIT 3
");
?>