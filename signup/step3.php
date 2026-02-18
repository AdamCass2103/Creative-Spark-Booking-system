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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Title</title>
    <link rel="stylesheet" href="../css/signup.css">
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