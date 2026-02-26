<?php
// Run this daily to check for inactive members
require_once '../../includes/db_connect.php';

// Mark as inactive if no login for 6 months OR payment expired
$six_months_ago = date('Y-m-d', strtotime('-6 months'));

$conn->query("
    UPDATE users 
    SET account_status = 'inactive', 
        inactive_since = CURDATE() 
    WHERE account_status = 'active' 
    AND (last_activity < '$six_months_ago' 
         OR membership_expiry < CURDATE())
");