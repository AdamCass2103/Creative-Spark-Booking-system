<?php
require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
requireLogin();

$user_id = getCurrentUserId();

if (isset($_POST['cancel_booking']) && isset($_POST['attendee_id'])) {
    $attendee_id = $_POST['attendee_id'];
    $result = cancelBooking($attendee_id, $user_id);
    
    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'error';
    }
}

header('Location: book_training.php');
exit();
?>