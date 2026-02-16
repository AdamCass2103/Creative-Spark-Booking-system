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
    <title>Safety Agreement - Step 4 of 5</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; margin-bottom: 10px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; margin: 0 5px; border-radius: 5px; }
        .step.active { background: #9c27b0; color: white; }
        .step.completed { background: #4caf50; color: white; }
        
        .safety-box {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .agreement-item {
            padding: 15px;
            margin: 10px 0;
            background: white;
            border-left: 4px solid #9c27b0;
            border-radius: 5px;
        }
        
        .form-group { margin: 20px 0; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input[type="text"], input[type="date"] {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;
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
        
        .error {
            background: #fee;
            color: #c00;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
        }
    </style>
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