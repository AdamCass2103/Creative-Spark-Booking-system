<?php
session_start();
require_once '../includes/db_connect.php';

// Check if step1 completed
if (!isset($_SESSION['step1_complete'])) {
    header('Location: step1.php');
    exit();
}

$tiers = $conn->query("SELECT * FROM membership_tiers ORDER BY tier_level");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['signup']['tier_id'] = $_POST['tier_id'];
    $_SESSION['signup']['payment_type'] = $_POST['payment_type'];
    $_SESSION['signup']['enterprise_seats'] = $_POST['enterprise_seats'] ?? 1;
    $_SESSION['step2_complete'] = true;
    
    header('Location: step3.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Title</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
    <div class="container">
        <h1>🎫 Choose Your Membership</h1>
        
        <div class="step-indicator">
            <div class="step completed">1. Account</div>
            <div class="step active">2. Membership</div>
            <div class="step">3. Experience</div>
            <div class="step">4. Safety</div>
            <div class="step">5. Review</div>
        </div>
        
        <form method="POST" id="membershipForm">
            <div class="pricing-grid">
                <?php while($tier = $tiers->fetch_assoc()): ?>
                <div class="pricing-card" onclick="selectTier(<?php echo $tier['tier_id']; ?>)">
                    <h3><?php echo $tier['tier_name']; ?></h3>
                    <div class="price">
                        €<?php echo $tier['tier_id'] == 1 ? '100' : ($tier['tier_id'] == 2 ? '200' : ($tier['tier_id'] == 3 ? '500' : 'Custom')); ?>
                        <small>/month</small>
                    </div>
                    <div class="features">
                        <?php echo nl2br($tier['description']); ?>
                    </div>
                    <input type="radio" name="tier_id" value="<?php echo $tier['tier_id']; ?>" style="display: none;" class="tier-radio-<?php echo $tier['tier_id']; ?>">
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="payment-options">
                <h3>Payment Option</h3>
                <label style="display: block; margin: 10px 0;">
                    <input type="radio" name="payment_type" value="monthly" checked> Monthly
                </label>
                <label style="display: block; margin: 10px 0;">
                    <input type="radio" name="payment_type" value="annual"> Annual (10% discount)
                </label>
            </div>
            
            <div class="payment-options" id="enterpriseSection" style="display: none;">
                <h3>Enterprise Seats</h3>
                <label>Number of seats:</label>
                <input type="number" name="enterprise_seats" min="1" value="1" style="width: 80px; padding: 5px;">
            </div>
            
            <button type="submit" class="btn">Continue →</button>
        </form>
        
        <a href="step1.php" class="back-link">← Back to Account Details</a>
    </div>
    
    <script>
    function selectTier(tierId) {
        // Deselect all
        document.querySelectorAll('.pricing-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Select clicked card
        event.currentTarget.classList.add('selected');
        
        // Check radio
        document.querySelector('.tier-radio-' + tierId).checked = true;
        
        // Show enterprise section if tier 4 selected
        document.getElementById('enterpriseSection').style.display = 
            tierId == 4 ? 'block' : 'none';
    }
    </script>
</body>
</html>