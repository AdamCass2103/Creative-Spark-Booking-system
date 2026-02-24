<?php
require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
requireLogin();

$user_id = getCurrentUserId();

if (isset($_POST['cancel_booking']) && isset($_POST['session_id']) && isset($_POST['user_id'])) {
    $session_id = $_POST['session_id'];
    $target_user_id = $_POST['user_id'];
    
    // Make sure the user can only cancel their own bookings
    if ($target_user_id == $user_id) {
        $result = cancelBooking($session_id, $user_id);
        
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $result['message'];
            $_SESSION['message_type'] = 'error';
        }
    }
}

header('Location: book_training.php');
exit();
?>