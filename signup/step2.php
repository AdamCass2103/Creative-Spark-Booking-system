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
    <title>Choose Membership - Step 2 of 5</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; margin-bottom: 10px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; margin: 0 5px; border-radius: 5px; }
        .step.active { background: #9c27b0; color: white; }
        .step.completed { background: #4caf50; color: white; }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .pricing-card {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .pricing-card:hover {
            border-color: #9c27b0;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(156,39,176,0.2);
        }
        
        .pricing-card.selected {
            border-color: #9c27b0;
            background: #f9f0ff;
        }
        
        .pricing-card h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .price {
            font-size: 24px;
            font-weight: bold;
            color: #1a73e8;
            margin: 15px 0;
        }
        
        .price small {
            font-size: 14px;
            color: #666;
        }
        
        .features {
            text-align: left;
            margin: 20px 0;
            padding-left: 20px;
        }
        
        .features li {
            margin: 5px 0;
            color: #555;
        }
        
        .payment-options {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .btn { 
            width: 100%; 
            padding: 12px; 
            background: #1a73e8; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px; 
            cursor: pointer;
        }
        
        .btn:hover { background: #0d62d9; }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
        }
    </style>
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