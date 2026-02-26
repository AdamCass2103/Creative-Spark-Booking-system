<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
requireLogin();

if (!isset($_SESSION['reactivation']) || $_SESSION['reactivation']['step'] < 2) {
    header('Location: step1.php');
    exit();
}

$areas = [
    'FDM 3D Printing', 'SLA 3D Printing', 'SLS 3D Printing',
    'Laser Cutting', 'Vinyl Cutting', 'Waterjet Cutting',
    'Electronics Workbench', 'Precision CNC Milling', 'Large CNC Milling',
    'Vacuum Forming', 'Sublimation'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $areas_data = [];
    if (isset($_POST['areas']) && is_array($_POST['areas'])) {
        foreach ($_POST['areas'] as $area) {
            $skill_key = str_replace(' ', '_', $area) . '_skill';
            $skill_level = $_POST[$skill_key] ?? 'beginner';
            $areas_data[] = [
                'area' => $area,
                'skill' => $skill_level
            ];
        }
    }
    $_SESSION['reactivation']['new_machines'] = $areas_data;
    $_SESSION['reactivation']['step'] = 4;
    header('Location: step4.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivate - Update Machines</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .reactivate-container {
            max-width: 800px;
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
        .machine-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
        }
        .previous-machines {
            background: #f0f7f0;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #4caf50;
        }
        .machine-tag {
            display: inline-block;
            background: #e8f5e9;
            padding: 8px 15px;
            border-radius: 30px;
            margin: 5px;
            font-size: 0.9em;
        }
        .skill-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            margin-left: 5px;
            font-weight: bold;
        }
        .skill-badge.beginner { background: #ff9800; color: white; }
        .skill-badge.intermediate { background: #2196f3; color: white; }
        .skill-badge.expert { background: #4caf50; color: white; }
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
            <div class="step completed">2. Choose Tier</div>
            <div class="step active">3. Update Machines</div>
            <div class="step">4. Confirm</div>
        </div>

        <div class="machine-section">
            <h2 style="color: #ff9800; margin-bottom: 20px;">🔧 Update Machine Selections</h2>
            
            <div class="previous-machines">
                <h4 style="color: #2E7D32; margin-bottom: 10px;">Previously Selected:</h4>
                <?php foreach($_SESSION['reactivation']['original_machines'] as $machine): ?>
                    <span class="machine-tag">
                        <?php echo $machine['area_name']; ?>
                        <span class="skill-badge <?php echo $machine['skill_level']; ?>">
                            <?php echo ucfirst($machine['skill_level']); ?>
                        </span>
                    </span>
                <?php endforeach; ?>
            </div>
            
            <form method="POST">
                <p style="color: #666; margin-bottom: 20px;">Update your selections for your new project:</p>
                
                <?php foreach($areas as $area): 
                    $area_id = str_replace(' ', '_', $area);
                ?>
                <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input type="checkbox" name="areas[]" value="<?php echo $area; ?>" id="area_<?php echo $area_id; ?>">
                        <label for="area_<?php echo $area_id; ?>" style="font-weight: 600;"><?php echo $area; ?></label>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px; margin-left: 30px;" id="skills_<?php echo $area_id; ?>">
                        <label><input type="radio" name="<?php echo $area_id; ?>_skill" value="beginner" checked> Beginner</label>
                        <label><input type="radio" name="<?php echo $area_id; ?>_skill" value="intermediate"> Intermediate</label>
                        <label><input type="radio" name="<?php echo $area_id; ?>_skill" value="expert"> Expert</label>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn-continue">Continue →</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="step2.php" class="btn-back">← Back</a>
            </div>
        </div>
    </div>
</body>
</html>