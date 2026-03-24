<?php
// ============================================
// PAYMENT CONFIGURATION
// ============================================

// Payment processor type: 'manual', 'stripe', 'paypal', or 'zero'
define('PAYMENT_PROCESSOR', 'manual'); // Change this when Zero is ready

// Payment settings
define('CURRENCY', 'EUR');
define('TAX_RATE', 0.23); // 23% Irish VAT

// Membership pricing (per month)
$membership_prices = [
    1 => 100,   // Fabber 1
    2 => 200,   // Fabber 2
    3 => 500,   // Fabber 3
    4 => 0      // Desk (free)
];

// Payment status constants
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_PAID', 'paid');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Generate invoice number
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
}

// Calculate total with tax
function calculateTotal($amount) {
    $tax = $amount * TAX_RATE;
    return [
        'subtotal' => $amount,
        'tax' => round($tax, 2),
        'total' => round($amount + $tax, 2)
    ];
}

// Create payment record
function createPaymentRecord($user_id, $amount, $payment_type, $tier_id) {
    global $conn;
    
    $invoice_number = generateInvoiceNumber();
    $total_calc = calculateTotal($amount);
    
    $stmt = $conn->prepare("INSERT INTO payments (user_id, invoice_number, amount, tax, total, payment_type, tier_id, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $status = PAYMENT_STATUS_PENDING;
    $stmt->bind_param("isdddsis", $user_id, $invoice_number, $total_calc['subtotal'], $total_calc['tax'], $total_calc['total'], $payment_type, $tier_id, $status);
    $stmt->execute();
    
    return $conn->insert_id;
}

// Update payment status
function updatePaymentStatus($payment_id, $status, $transaction_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE payments SET status = ?, transaction_id = ?, paid_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $status, $transaction_id, $payment_id);
    return $stmt->execute();
}

// Get user payment history
function getUserPayments($user_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM payments WHERE user_id = $user_id ORDER BY created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Check if user has active membership
function hasActiveMembership($user_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM payments WHERE user_id = $user_id AND status = 'paid' AND created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY created_at DESC LIMIT 1");
    return $result->num_rows > 0;
}

// Get next billing date
function getNextBillingDate($user_id) {
    global $conn;
    $result = $conn->query("SELECT created_at FROM payments WHERE user_id = $user_id AND status = 'paid' AND payment_type = 'monthly' ORDER BY created_at DESC LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        return date('Y-m-d', strtotime($row['created_at'] . ' +1 month'));
    }
    return null;
}
?>