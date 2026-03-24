<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

// Get database connection
$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection error. Please try again later.");
}

// Get all payments
$payments = $conn->query("
    SELECT p.*, u.name, u.email 
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.created_at DESC
");

// Get summary stats
$total_paid = $conn->query("SELECT SUM(total) as total FROM payments WHERE status = 'paid'")->fetch_assoc();
$pending_count = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch_assoc();
$monthly_recurring = $conn->query("SELECT COUNT(*) as count FROM users WHERE auto_payment = 1")->fetch_assoc();

// Handle payment confirmation
if (isset($_GET['confirm']) && isset($_GET['payment_id'])) {
    $payment_id = (int)$_GET['payment_id'];
    $conn->query("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = $payment_id");
    // Also update user payment status
    $payment = $conn->query("SELECT user_id FROM payments WHERE id = $payment_id")->fetch_assoc();
    if ($payment) {
        $conn->query("UPDATE users SET payment_status = 'paid', payment_date = NOW() WHERE user_id = " . $payment['user_id']);
    }
    header("Location: manage_payments.php?success=1");
    exit;
}

// Handle payment deletion
if (isset($_GET['delete']) && isset($_GET['payment_id'])) {
    $payment_id = (int)$_GET['payment_id'];
    $conn->query("DELETE FROM payments WHERE id = $payment_id");
    header("Location: manage_payments.php?deleted=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .status-paid { color: #2E7D32; font-weight: bold; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-failed { color: #f44336; font-weight: bold; }
        .status-refunded { color: #9c27b0; font-weight: bold; }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            text-decoration: none;
            margin: 0 2px;
            display: inline-block;
        }
        .btn-success { background: #2E7D32; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-warning { background: #ff9800; color: white; }
        .btn-info { background: #2196f3; color: white; }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .total-amount {
            font-size: 2em;
            color: #2E7D32;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Payment Management</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
                <a href="pending_bookings.php" class="btn">⏳ Pending Bookings</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">✅ Payment confirmed successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-message">🗑️ Payment record deleted successfully!</div>
        <?php endif; ?>

        <div class="stats" style="margin-bottom: 30px;">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p class="total-amount">€<?php echo number_format($total_paid['total'] ?? 0, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Payments</h3>
                <p><?php echo $pending_count['count'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>Auto-Pay Users</h3>
                <p><?php echo $monthly_recurring['count'] ?? 0; ?></p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Paid At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments && $payments->num_rows > 0): ?>
                        <?php while ($payment = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['invoice_number']); ?></td>
                            <td><?php echo htmlspecialchars($payment['name']); ?><br><small><?php echo htmlspecialchars($payment['email']); ?></small></td>
                            <td>€<?php echo number_format($payment['amount'], 2); ?></td>
                            <td>€<?php echo number_format($payment['tax'], 2); ?></td>
                            <td><strong>€<?php echo number_format($payment['total'], 2); ?></strong></td>
                            <td><?php echo ucfirst($payment['payment_type']); ?></td>
                            <td>
                                <span class="status-<?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($payment['created_at'])); ?></td>
                            <td><?php echo $payment['paid_at'] ? date('d/m/Y', strtotime($payment['paid_at'])) : '-'; ?></td>
                            <td>
                                <?php if ($payment['status'] == 'pending'): ?>
                                    <a href="?confirm=1&payment_id=<?php echo $payment['id']; ?>" class="btn-small btn-success" onclick="return confirm('Mark this payment as paid?')">✅ Mark Paid</a>
                                <?php endif; ?>
                                <a href="receipt.php?id=<?php echo $payment['id']; ?>" class="btn-small btn-info" target="_blank">📄 Receipt</a>
                                <a href="?delete=1&payment_id=<?php echo $payment['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Delete this payment record?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px;">No payment records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>