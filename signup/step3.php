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
    $_SESSION['signup']['work_description'] = $_POST['work_description'];
    
    // Process areas with skill levels
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
    $_SESSION['signup']['areas_with_skills'] = $areas_data;
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
    <title>Your Experience - Creative Spark</title>
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
        
        <form method="POST" id="experienceForm">
            <div class="form-group">
                <label>Select machines you'll use and rate your skill level:</label>
                <div style="margin: 20px 0;">
                    <?php foreach($areas as $area): 
                        $area_id = str_replace(' ', '_', $area);
                    ?>
                    <div class="machine-skill-row">
                         <div>
                         <input type="checkbox" name="areas[]" value="<?php echo $area; ?>" 
                            id="area_<?php echo $area_id; ?>" 
                                onchange="toggleSkill('<?php echo $area_id; ?>')">  <!-- FIXED: Added quotes -->
                                <label for="area_<?php echo $area_id; ?>" class="machine-name"><?php echo $area; ?></label>
                                     </div>
                                    <div class="skill-buttons" id="skills_<?php echo $area_id; ?>" style="display: none;">
                                <input type="hidden" name="<?php echo $area_id; ?>_skill" id="skill_<?php echo $area_id; ?>" value="beginner">
                        <button type="button" class="skill-btn beginner" onclick="setSkill('<?php echo $area_id; ?>', 'beginner', this)">Beginner</button>
                        <button type="button" class="skill-btn intermediate" onclick="setSkill('<?php echo $area_id; ?>', 'intermediate', this)">Intermediate</button>
                        <button type="button" class="skill-btn expert" onclick="setSkill('<?php echo $area_id; ?>', 'expert', this)">Expert</button>
                        </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="work-description">
                <div class="form-group">
                    <label>Brief description of your work/projects</label>
                    <textarea name="work_description" rows="4" placeholder="What type of work will you be doing in the FabLab?"></textarea>
                </div>
            </div>
            
            <button type="submit" class="btn">Continue →</button>
        </form>
        
        <a href="step2.php" class="back-link">← Back to Membership</a>
    </div>
    
    <script>
        function toggleSkill(areaId) {
            var checkbox = document.getElementById('area_' + areaId);
            var skillDiv = document.getElementById('skills_' + areaId);
            skillDiv.style.display = checkbox.checked ? 'flex' : 'none';
            
            // Set default skill if checked
            if (checkbox.checked) {
                setSkill(areaId, 'beginner', document.querySelector('#skills_' + areaId + ' .beginner'));
            }
        }
        
        function setSkill(areaId, level, btn) {
            // Update hidden input
            document.getElementById('skill_' + areaId).value = level;
            
            // Update button styles
            var buttons = document.querySelectorAll('#skills_' + areaId + ' .skill-btn');
            buttons.forEach(function(b) {
                b.classList.remove('selected');
            });
            btn.classList.add('selected');
        }
    </script>
</body>
</html>