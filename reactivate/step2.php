<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
requireLogin();

if (!isset($_SESSION['reactivation']) || $_SESSION['reactivation']['step'] < 1) {
    header('Location: step1.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'booking_system');
$tiers = $conn->query("SELECT * FROM membership_tiers ORDER BY tier_level");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reactivation']['new_tier'] = $_POST['tier_id'];
    $_SESSION['reactivation']['step'] = 3;
    header('Location: step3.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivate - Choose Tier</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .reactivate-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            color: #999;
            position: relative;
        }
        .step.completed {
            color: #4caf50;
        }
        .step.completed::before {
            content: '✓';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: #4caf50;
            font-size: 20px;
        }
        .step.active {
            color: #ff9800;
            font-weight: bold;
        }
        .step.active::before {
            content: '●';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: #ff9800;
            font-size: 20px;
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .pricing-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .pricing-card.selected {
            border-color: #ff9800;
            background: #fff3e0;
        }
        .previous-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .btn-continue {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
            transition: all 0.3s;
        }
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 152, 0, 0.4);
        }
        .btn-back {
            background: #757575;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="reactivate-container">
        <div class="step-indicator">
            <div class="step completed">1. Confirm Details</div>
            <div class="step active">2. Choose Tier</div>
            <div class="step">3. Update Machines</div>
            <div class="step">4. Confirm</div>
        </div>

        <div style="background: white; border-radius: 20px; padding: 40px;">
            <h2 style="color: #ff9800; margin-bottom: 10px;">🎫 Choose Your Membership Tier</h2>
            <p style="color: #666; margin-bottom: 30px;">
                Previous tier: <strong>Fabber <?php echo $_SESSION['reactivation']['original_tier']; ?></strong>
            </p>
            
            <form method="POST" id="tierForm">
                <div class="pricing-grid">
                    <?php while($tier = $tiers->fetch_assoc()): 
                        $is_previous = ($tier['tier_id'] == $_SESSION['reactivation']['original_tier']);
                    ?>
                    <div class="pricing-card <?php echo $is_previous ? 'selected' : ''; ?>" 
                         onclick="selectTier(<?php echo $tier['tier_id']; ?>)">
                        <h3><?php echo $tier['tier_name']; ?></h3>
                        <?php if($is_previous): ?>
                            <span class="previous-badge">Previous</span>
                        <?php endif; ?>
                        <div class="price">
                            €<?php echo $tier['tier_id'] == 1 ? '100' : ($tier['tier_id'] == 2 ? '200' : '500'); ?>
                            <small>/month</small>
                        </div>
                        <p style="color: #666; font-size: 0.9em; margin-top: 15px;">
                            <?php echo $tier['description']; ?>
                        </p>
                        <input type="radio" name="tier_id" value="<?php echo $tier['tier_id']; ?>" 
                               <?php echo $is_previous ? 'checked' : ''; ?> 
                               style="display: none;" class="tier-radio">
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <button type="submit" class="btn-continue">Continue →</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="step1.php" class="btn-back">← Back</a>
            </div>
        </div>
    </div>

    <script>
    function selectTier(tierId) {
        document.querySelectorAll('.pricing-card').forEach(c => c.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        document.querySelector('.tier-radio[value="' + tierId + '"]').checked = true;
    }
    </script>
</body>
</html>