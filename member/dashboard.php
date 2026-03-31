<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (getenv('VERCEL_ENV')) {
        $conn = mysqli_init();

        if (getenv('CA_CERT')) {
            $cert_path = '/tmp/ca.pem';
            file_put_contents($cert_path, getenv('CA_CERT'));
            $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
        } else {
            $conn->ssl_set(NULL, NULL, __DIR__ . '/../certs/ca.pem', NULL, NULL);
        }

        $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, 25849, NULL, MYSQLI_CLIENT_SSL);
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
} catch (mysqli_sql_exception $e) {
    die("Database connection error.");
}

$user_id = getCurrentUserId();

// Fetch data
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Booking count
$pending_bookings = $conn->query("
    SELECT COUNT(*) as count 
    FROM session_attendees 
    WHERE user_id = $user_id AND booking_status = 'pending_approval'
")->fetch_assoc()['count'];

// Payment status
$payment_status = $user['payment_status'] ?? 'pending';

// Active check
$is_active = ($prefs['training_status'] == 'approved' && $payment_status == 'paid');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="../css/member-dashboard.css">
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <a href="logout.php" class="logout">Logout</a>
        <h1>👋 Welcome back, <?php echo htmlspecialchars($user['name']); ?></h1>
        <p>Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
    </div>

    <!-- WARNING BANNER -->
    <?php if (!$is_active): ?>
        <div style="background:#ff9800;color:white;padding:12px;text-align:center;border-radius:8px;margin-bottom:20px;">
            ⚠️ Your account is not fully active.
            <?php if ($prefs['training_status'] != 'approved'): ?>
                Waiting for admin approval.
            <?php else: ?>
                Please complete payment to unlock features.
                <a href="payment.php" style="color:#fff;text-decoration:underline;margin-left:10px;">Pay Now</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ACTION BUTTONS -->
    <div class="action-bar">

        <!-- BOOK TRAINING -->
        <?php if ($is_active): ?>
            <a href="book_training.php" class="btn-book">
        <?php else: ?>
            <a href="#" class="btn-book" onclick="showLocked()" style="opacity:0.6;">
        <?php endif; ?>
            📅 Book Training
            <?php if ($pending_bookings > 0): ?>
                <span class="badge"><?php echo $pending_bookings; ?></span>
            <?php endif; ?>
        </a>

        <!-- MY BOOKINGS -->
        <?php if ($is_active): ?>
            <a href="my_bookings.php" class="btn-book" style="background:#9c27b0;">
        <?php else: ?>
            <a href="#" class="btn-book" onclick="showLocked()" style="background:#9c27b0;opacity:0.6;">
        <?php endif; ?>
            📋 My Bookings
        </a>

        <!-- ACCOUNT (always allowed) -->
        <a href="my_account.php" class="btn-book" style="background:#2E7D32;">👤 My Account</a>

        <!-- FABMAN -->
        <?php if ($is_active): ?>
            <a href="/fabman" class="btn-book" target="_blank" style="background:#ff9800;">
        <?php else: ?>
            <a href="#" class="btn-book" onclick="showLocked()" style="background:#ff9800;opacity:0.6;">
        <?php endif; ?>
            🛠️ FabMan
        </a>

        <!-- FEEDBACK (always allowed) -->
        <a href="feedback.php" class="btn-book" style="background:#795548;">💬 Feedback</a>
    </div>

    <!-- DASHBOARD GRID -->
    <div class="dashboard-grid">

        <!-- PROFILE -->
        <div class="card">
            <h2>📋 Profile</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
            <p><strong>Company:</strong> <?php echo htmlspecialchars($user['company'] ?? 'N/A'); ?></p>
        </div>

        <!-- MEMBERSHIP -->
        <div class="card">
            <h2>🎫 Membership</h2>

            <p><strong>Status:</strong>
                <span class="status-<?php echo $prefs['training_status']; ?>">
                    <?php echo ucfirst($prefs['training_status']); ?>
                </span>
            </p>

            <p><strong>Payment:</strong>
                <?php if ($payment_status == 'paid'): ?>
                    <span style="color:#4caf50;">✅ Paid</span>
                <?php else: ?>
                    <span style="color:#ff9800;">⏳ Pending</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- RESOURCES -->
        <div class="card" style="grid-column: span 2;">
            <h2>📚 Learning Resources</h2>

            <h3>🎥 Videos</h3>
            <div class="video-item" onclick="window.open('https://www.youtube.com/watch?v=2vFdwz4U1VQ','_blank')">
                ▶️ 3D Printing Guide
            </div>

            <h3>📖 Wiki</h3>
            <a href="https://creative-spark-enterprise-fablab.gitbook.io/fablab-wiki/" target="_blank">
                Open Wiki →
            </a>
        </div>

    </div>
</div>

<!-- LOCKED MODAL -->
<div id="lockedModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;">
    <div style="background:white;padding:30px;border-radius:10px;text-align:center;">
        <h2>🔒 Access Restricted</h2>
        <p>You must complete payment to use this feature.</p>
        <a href="payment.php" class="btn-book">Go to Payment</a><br><br>
        <button onclick="closeModal()">Close</button>
    </div>
</div>

<script>
function showLocked() {
    document.getElementById('lockedModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('lockedModal').style.display = 'none';
}
</script>

</body>
</html>