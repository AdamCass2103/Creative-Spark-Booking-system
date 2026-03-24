<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';     
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
//222
// Get database connection
$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection error. Please try again later.");
}

$user_id = getCurrentUserId();
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Membership pricing
$membership_prices = [
    1 => 100,   // Fabber 1
    2 => 200,   // Fabber 2
    3 => 500,   // Fabber 3
    4 => 0      // Desk (free)
];

// Get membership details
$tier_id = $prefs['tier_id'];
$payment_type = $_POST['payment_type'] ?? $prefs['payment_type'] ?? 'monthly';
$amount = $membership_prices[$tier_id] ?? 0;

if ($payment_type == 'annual') {
    $amount = $amount * 12 * 0.9; // 10% discount for annual
}

// Calculate tax (23% VAT)
$tax_rate = 0.23;
$subtotal = $amount;
$tax = round($subtotal * $tax_rate, 2);
$total = $subtotal + $tax;

// Generate invoice number
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $invoice_number = generateInvoiceNumber();
    $status = 'pending';
    
    $stmt = $conn->prepare("INSERT INTO payments (user_id, invoice_number, amount, tax, total, payment_type, tier_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdddsis", $user_id, $invoice_number, $subtotal, $tax, $total, $payment_type, $tier_id, $status);
    
    if ($stmt->execute()) {
        $payment_id = $conn->insert_id;
        $payment_pending = true;
    } else {
        $error = "Failed to create payment record.";
    }
}

// Handle manual payment confirmation (admin only)
if (isset($_GET['confirm_payment']) && isset($_GET['payment_id']) && getCurrentAdminRole()) {
    $payment_id = (int)$_GET['payment_id'];
    $conn->query("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = $payment_id");
    $conn->query("UPDATE users SET payment_status = 'paid', payment_date = NOW() WHERE user_id = $user_id");
    $success = "Payment confirmed!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Creative Spark</title>
    <link rel="stylesheet" href="../css/payment.css">
    <style>
        .payment-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .amount {
            font-size: 3em;
            font-weight: bold;
            color: #2E7D32;
            margin: 20px 0;
        }
        .breakdown {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        .breakdown p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .btn-pay {
            width: 100%;
            padding: 15px;
            background: #2E7D32;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-pay:hover {
            background: #1B5E20;
            transform: translateY(-2px);
        }
        .pending-message {
            text-align: center;
        }
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .btn-confirm {
            display: inline-block;
            padding: 12px 24px;
            background: #ff9800;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px;
        }
        .cancel-link {
            color: #999;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h1>💳 Membership Payment</h1>
            
            <?php if (isset($success)): ?>
                <div class="success-message">
                    ✅ <?php echo $success; ?>
                    <br><br>
                    <a href="dashboard.php" style="color: #2E7D32;">Go to Dashboard →</a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($payment_pending)): ?>
                <div class="pending-message">
                    <div style="font-size: 3em;">📋</div>
                    <h2>Payment Instructions</h2>
                    <p>Your payment request has been created.</p>
                    <div class="payment-details">
                        <p><strong>Amount Due:</strong> €<?php echo number_format($total, 2); ?></p>
                        <p><strong>Invoice #:</strong> <?php echo $invoice_number; ?></p>
                    </div>
                    <div style="text-align: left;">
                        <h3>Please complete payment by:</h3>
                        <ul>
                            <li>💰 Cash at the FabLab front desk</li>
                            <li>💳 Card payment in person</li>
                            <li>🏦 Bank Transfer to: Creative Spark FabLab</li>
                        </ul>
                        <p style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px;">
                            <strong>Bank Details:</strong><br>
                            Bank: AIB<br>
                            Account Name: Creative Spark FabLab<br>
                            IBAN: [To be provided]<br>
                            BIC: [To be provided]
                        </p>
                    </div>
                    <a href="dashboard.php" class="cancel-link">← I'll Pay Later</a>
                </div>
            <?php else: ?>
                <div class="amount">
                    €<?php echo number_format($total, 2); ?>
                </div>
                
                <div class="breakdown">
                    <p>Subtotal: €<?php echo number_format($subtotal, 2); ?></p>
                    <p>VAT (23%): €<?php echo number_format($tax, 2); ?></p>
                    <p class="total"><strong>Total: €<?php echo number_format($total, 2); ?></strong></p>
                </div>
                
                <form method="POST">
                    <div class="form-group" style="margin: 20px 0; text-align: left;">
                        <label>Payment Type</label>
                        <select name="payment_type" onchange="updateAmount(this.value)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="monthly">Monthly - €<?php echo number_format($membership_prices[$tier_id] ?? 0, 2); ?></option>
                            <option value="annual">Annual - €<?php echo number_format(($membership_prices[$tier_id] ?? 0) * 12 * 0.9, 2); ?> (10% off)</option>
                        </select>
                    </div>
                    
                    <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: left;">
                        <p>ℹ️ <strong>Payment Options:</strong></p>
                        <ul style="margin-left: 20px;">
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
            let amount = <?php echo $membership_prices[$tier_id] ?? 0; ?>;
            if (value === 'annual') {
                amount = amount * 12 * 0.9;
            }
            let tax = amount * 0.23;
            let total = amount + tax;
            document.querySelector('.amount').innerHTML = '€' + total.toFixed(2);
            document.querySelector('.breakdown').innerHTML = `
                <p>Subtotal: €${amount.toFixed(2)}</p>
                <p>VAT (23%): €${tax.toFixed(2)}</p>
                <p class="total"><strong>Total: €${total.toFixed(2)}</strong></p>
            `;
        }
    </script>
</body>
</html>