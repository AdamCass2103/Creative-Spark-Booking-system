<?php
session_start();
require_once '../includes/db_connect.php';

// Check if previous steps completed
if (!isset($_SESSION['step3_complete'])) {
    header('Location: step3.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['safety_orientation']) || !isset($_POST['ppe_acknowledged']) || !isset($_POST['damages_responsibility'])) {
        $error = 'You must acknowledge all safety agreements';
    } else {
        $_SESSION['signup']['signature'] = $_POST['signature'];
        $_SESSION['signup']['signed_date'] = $_POST['signed_date'];
        $_SESSION['signup']['safety_agreements'] = [
            'orientation' => isset($_POST['safety_orientation']),
            'inductions' => isset($_POST['inductions']),
            'ppe' => isset($_POST['ppe_acknowledged']),
            'damages' => isset($_POST['damages_responsibility'])
        ];
        $_SESSION['step4_complete'] = true;
        
        header('Location: step5.php');
        exit();
    }
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
        <h1>⚠️ Health & Safety Agreement</h1>
        
        <div class="step-indicator">
            <div class="step completed">1. Account</div>
            <div class="step completed">2. Membership</div>
            <div class="step completed">3. Experience</div>
            <div class="step active">4. Safety</div>
            <div class="step">5. Review</div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="safety-box">
            <p><strong>All members must complete safety orientation before using any machinery.</strong></p>
        </div>
        
        <form method="POST">
            <div class="agreement-item">
                <label>
                    <input type="checkbox" name="safety_orientation" required>
                    I acknowledge that I must complete safety orientation before using any equipment
                </label>
            </div>
            
            <div class="agreement-item">
                <label>
                    <input type="checkbox" name="inductions" required>
                    I understand that machine-specific inductions are required for each equipment type
                </label>
            </div>
            
            <div class="agreement-item">
                <label>
                    <input type="checkbox" name="ppe_acknowledged" required>
                    I will follow all PPE requirements (safety glasses, ear protection, dust masks, etc.)
                </label>
            </div>
            
            <div class="agreement-item">
                <label>
                    <input type="checkbox" name="damages_responsibility" required>
                    I am responsible for damages caused by improper use
                </label>
            </div>
            
            <div class="form-group">
                <label>Full Name (Signature)</label>
                <input type="text" name="signature" required placeholder="Type your full name as signature">
            </div>
            
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="signed_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <p style="font-size: 12px; color: #666; margin: 20px 0;">
                Signing this form is a pre-requisite to commencing use of the Creative Spark Enterprise FabLab.
            </p>
            
            <button type="submit" class="btn">Continue to Review →</button>
        </form>
        
        <a href="step3.php" class="back-link">← Back to Experience</a>
    </div>
</body>
</html>