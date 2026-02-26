<?php
require_once '../includes/auth.php';
requireLogin();

$user_id = getCurrentUserId();
$conn = new mysqli('localhost', 'root', '', 'booking_system');

// Update status to reactivating
$conn->query("UPDATE users SET account_status = 'reactivating', reactivation_step = 1 WHERE user_id = $user_id");

// Redirect to step2 of signup (modified for reactivation)
header('Location: ../signup/step2.php?reactivate=1');
exit();