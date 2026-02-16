<?php
session_start();
require_once '../includes/db_connect.php';

// Check if previous steps completed
if (!isset($_SESSION['step2_complete'])) {
    header('Location: step2.php');
    exit();
}

$areas = [
    'FDM 3D Printing',
    'SLA 3D Printing',
    'SLS 3D Printing',
    'Laser Cutting',
    'Vinyl Cutting',
    'Waterjet Cutting',
    'Electronics Workbench',
    'Precision CNC Milling',
    'Large CNC Milling',
    'Vacuum Forming',
    'Sublimation'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['signup']['experience_text'] = $_POST['experience_text'];
    $_SESSION['signup']['work_description'] = $_POST['work_description'];
    $_SESSION['signup']['areas'] = $_POST['areas'] ?? [];
    $_SESSION['step3_complete'] = true;
    
    header('Location: step4.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Experience - Step 3 of 5</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; margin-bottom: 10px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; margin: 0 5px; border-radius: 5px; }
        .step.active { background: #9c27b0; color: white; }
        .step.completed { background: #4caf50; color: white; }
        
        .form-group { margin-bottom: 25px; }
        label { font-weight: bold; display: block; margin-bottom: 10px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        
        .areas-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        
        .area-checkbox {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .area-checkbox:hover {
            background: #f0f0f0;
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
        <h1>🔧 Your Experience & Areas</h1>
        
        <div class="step-indicator">
            <div class="step completed">1. Account</div>
            <div class="step completed">2. Membership</div>
            <div class="step active">3. Experience</div>
            <div class="step">4. Safety</div>
            <div class="step">5. Review</div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Which areas will you use? (Select all that apply)</label>
                <div class="areas-grid">
                    <?php foreach($areas as $area): ?>
                    <label class="area-checkbox">
                        <input type="checkbox" name="areas[]" value="<?php echo $area; ?>">
                        <?php echo $area; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>Describe your experience with this equipment</label>
                <textarea name="experience_text" rows="4" placeholder="e.g., I've used 3D printers at university, comfortable with CAD software..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Brief description of your work/projects</label>
                <textarea name="work_description" rows="4" placeholder="What type of work will you be doing in the FabLab?"></textarea>
            </div>
            
            <button type="submit" class="btn">Continue →</button>
        </form>
        
        <a href="step2.php" class="back-link">← Back to Membership</a>
    </div>
</body>
</html>