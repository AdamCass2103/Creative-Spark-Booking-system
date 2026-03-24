<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/payment_gateway.php';
requireLogin();

$user_id = getCurrentUserId();
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Get membership details
$tier_id = $prefs['tier_id'];
$payment_type = $_POST['payment_type'] ?? $prefs['payment_type'] ?? 'monthly';
$amount = $membership_prices[$tier_id] ?? 0;

if ($payment_type == 'annual') {
    $amount = $amount * 12 * 0.9; // 10% discount for annual
}

$total_calc = calculateTotal($amount);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // Create payment record
    $payment_id = createPaymentRecord($user_id, $amount, $payment_type, $tier_id);
    
    // Process payment through gateway
    $result = $payment_gateway->processPayment($payment_id, $user_id, $amount, 'manual');
    
    if ($result['success']) {
        if (isset($result['manual']) && $result['manual']) {
            // Manual payment - show instructions
            $payment_pending = true;
            $payment_id = $result['payment_id'];
        } elseif (isset($result['redirect_url'])) {
            // Zero payment - redirect
            header("Location: " . $result['redirect_url']);
            exit;
        }
    } else {
        $error = $result['message'];
    }
}

// Handle manual payment confirmation (admin only)
if (isset($_GET['confirm_payment']) && isset($_GET['payment_id']) && getCurrentAdminRole()) {
    $payment_id = (int)$_GET['payment_id'];
    updatePaymentStatus($payment_id, PAYMENT_STATUS_PAID);
    
    // Update user payment status
    $conn->query("UPDATE users SET payment_status = 'paid', payment_date = NOW() WHERE user_id = $user_id");
    
    // Generate receipt
    $payment_gateway->generateReceipt($payment_id);
    
    $success = "Payment confirmed! Receipt generated.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Creative Spark</title>
    <link rel="stylesheet" href="../css/payment.css">
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h1>💳 Membership Payment</h1>
            
            <?php if (isset($success)): ?>
                <div class="success-message">
                    ✅ <?php echo $success; ?>
                    <a href="dashboard.php" class="btn-small">Go to Dashboard</a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($payment_pending)): ?>
                <div class="pending-message">
                    <div class="icon">📋</div>
                    <h2>Payment Instructions</h2>
                    <p>Your payment request has been created.</p>
                    <div class="payment-details">
                        <p><strong>Amount Due:</strong> €<?php echo number_format($total_calc['total'], 2); ?></p>
                        <p><strong>Invoice #:</strong> <?php echo $conn->query("SELECT invoice_number FROM payments WHERE id = $payment_id")->fetch_assoc()['invoice_number']; ?></p>
                    </div>
                    <div class="instructions">
                        <h3>Please complete payment by:</h3>
                        <ul>
                            <li>💰 Cash at the FabLab front desk</li>
                            <li>💳 Card payment in person</li>
                            <li>🏦 Bank Transfer to: Creative Spark FabLab</li>
                        </ul>
                        <p class="bank-details">
                            <strong>Bank Details:</strong><br>
                            Bank: AIB<br>
                            Account Name: Creative Spark FabLab<br>
                            IBAN: [To be provided]<br>
                            BIC: [To be provided]
                        </p>
                    </div>
                    <a href="dashboard.php" class="btn-secondary">I'll Pay Later</a>
                    <?php if (getCurrentAdminRole()): ?>
                        <a href="?confirm_payment=1&payment_id=<?php echo $payment_id; ?>" class="btn-confirm">Mark as Paid (Admin)</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="amount">
                    €<?php echo number_format($total_calc['total'], 2); ?>
                </div>
                
                <div class="breakdown">
                    <p>Subtotal: €<?php echo number_format($total_calc['subtotal'], 2); ?></p>
                    <p>VAT (23%): €<?php echo number_format($total_calc['tax'], 2); ?></p>
                    <p class="total"><strong>Total: €<?php echo number_format($total_calc['total'], 2); ?></strong></p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type" onchange="updateAmount(this.value)">
                            <option value="monthly">Monthly - €<?php echo number_format($amount, 2); ?></option>
                            <option value="annual">Annual - €<?php echo number_format($amount * 12 * 0.9, 2); ?> (10% off)</option>
                        </select>
                    </div>
                    
                    <div class="info-box">
                        <p>ℹ️ <strong>Payment Options:</strong></p>
                        <ul>
                            <li>Pay at the FabLab front desk (cash/card)</li>
                            <li>Bank transfer (details provided after checkout)</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="process_payment" class="btn-pay">
                        Proceed to Payment →
                    </button>
                </form>
            <?php endif; ?>
            
            <a href="dashboard.php" class="cancel-link">← Cancel</a>
        </div>
    </div>
    
    <script>
        function updateAmount(value) {
            let amount = <?php echo $amount; ?>;
            if (value === 'annual') {
                amount = amount * 12 * 0.9;
            }
            document.querySelector('.amount').innerHTML = '€' + amount.toFixed(2);
            
            // Update breakdown
            let subtotal = amount;
            let tax = subtotal * 0.23;
            let total = subtotal + tax;
            
            document.querySelector('.breakdown').innerHTML = `
                <p>Subtotal: €${subtotal.toFixed(2)}</p>
                <p>VAT (23%): €${tax.toFixed(2)}</p>
                <p class="total"><strong>Total: €${total.toFixed(2)}</strong></p>
            `;
        }
    </script>
</body>
</html>