<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_functions.php';
requireAdmin();

$conn = getDatabaseConnection();

// Get all payments
$payments = $conn->query("
    SELECT p.*, u.name, u.email 
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.created_at DESC
");

// Get summary stats
$total_paid = $conn->query("SELECT SUM(total) as total FROM payments WHERE status = 'paid'")->fetch_assoc()['total'];
$pending_count = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch_assoc()['count'];
$monthly_recurring = $conn->query("SELECT COUNT(*) as count FROM users WHERE auto_payment = 1")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Payment Management</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>€<?php echo number_format($total_paid ?? 0, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Payments</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Auto-Pay Users</h3>
                <p><?php echo $monthly_recurring; ?></p>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $payment['invoice_number']; ?></td>
                        <td><?php echo $payment['name']; ?></td>
                        <td>€<?php echo number_format($payment['total'], 2); ?></td>
                        <td><?php echo ucfirst($payment['payment_type']); ?></td>
                        <td>
                            <span class="status-<?php echo $payment['status']; ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($payment['created_at'])); ?></td>
                        <td>
                            <?php if ($payment['status'] == 'pending'): ?>
                                <a href="?confirm=<?php echo $payment['id']; ?>" class="btn-small">Mark Paid</a>
                            <?php endif; ?>
                            <a href="receipt.php?id=<?php echo $payment['id']; ?>" class="btn-small" target="_blank">Receipt</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>