<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user_id = getCurrentUserId();

// Use the shared database connection
$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection error. Please try again later.");
}

// Get current user data
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Get user's existing skills from user_areas table
$user_areas = $conn->query("SELECT * FROM user_areas WHERE user_id = $user_id");
$existing_skills = [];
while ($area = $user_areas->fetch_assoc()) {
    $existing_skills[$area['area_name']] = $area['skill_level'];
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update personal details
    if (isset($_POST['update_details'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $company = $conn->real_escape_string($_POST['company']);
        $address = $conn->real_escape_string($_POST['address']);
        
        $conn->query("UPDATE users SET 
                      name = '$name',
                      email = '$email',
                      phone = '$phone',
                      company = '$company',
                      address = '$address'
                      WHERE user_id = $user_id");
        
        $message = "Profile updated successfully!";
        $message_type = "success";
        
        // Refresh user data
        $user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current, $user['password'])) {
            if ($new === $confirm) {
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password = '$new_hash' WHERE user_id = $user_id");
                $message = "Password changed successfully!";
                $message_type = "success";
            } else {
                $message = "New passwords do not match!";
                $message_type = "error";
            }
        } else {
            $message = "Current password is incorrect!";
            $message_type = "error";
        }
    }
    
    // Update membership tier
    if (isset($_POST['update_tier'])) {
        $new_tier = $_POST['tier_id'];
        $payment_type = $_POST['payment_type'];
        
        $conn->query("UPDATE user_preferences SET 
                      tier_id = '$new_tier',
                      payment_type = '$payment_type'
                      WHERE user_id = $user_id");
        
        $message = "Membership updated successfully!";
        $message_type = "success";
        
        // Refresh prefs
        $prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();
    }
    
    // Update machine skills (using user_areas table)
    if (isset($_POST['update_skills'])) {
        // Define all possible machine areas (same as in signup)
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
        
        // Delete existing areas for this user
        $conn->query("DELETE FROM user_areas WHERE user_id = $user_id");
        
        // Insert new areas with skill levels
        if (isset($_POST['areas']) && is_array($_POST['areas'])) {
            foreach ($_POST['areas'] as $area) {
                $area_clean = $conn->real_escape_string($area);
                $skill_key = str_replace(' ', '_', $area) . '_skill';
                $skill_level = $conn->real_escape_string($_POST[$skill_key] ?? 'beginner');
                
                $conn->query("INSERT INTO user_areas (user_id, area_name, skill_level) 
                             VALUES ($user_id, '$area_clean', '$skill_level')");
            }
        }
        
        $message = "Machine skills updated successfully!";
        $message_type = "success";
        
        // Refresh skills
        $user_areas = $conn->query("SELECT * FROM user_areas WHERE user_id = $user_id");
        $existing_skills = [];
        while ($area = $user_areas->fetch_assoc()) {
            $existing_skills[$area['area_name']] = $area['skill_level'];
        }
    }
}

// Get tiers for dropdown
$tiers = $conn->query("SELECT * FROM membership_tiers ORDER BY tier_level");

// Define areas for skills section
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>My Account - Creative Spark</title>
    <link rel="stylesheet" href="../css/my_account.css">
    <style>
        /* Additional mobile-specific styles */
        @media (max-width: 480px) {
            .account-container {
                padding: 15px;
            }
            
            .account-card {
                padding: 18px;
            }
            
            .skill-badge {
                padding: 5px 10px;
                font-size: 0.75em;
            }
            
            .selected-skills-summary {
                font-size: 0.85em;
            }
            
            .status-approved, .status-pending, .status-completed {
                font-size: 0.85em;
                padding: 3px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="account-container">
        <div class="account-header">
            <h1>👤 My Account</h1>
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="account-grid">
            <!-- Personal Details Card -->
            <div class="account-card">
                <h2>📋 Personal Details</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Company/Organisation</label>
                        <input type="text" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_details" class="btn-save">
                        💾 Save Changes
                    </button>
                </form>
            </div>
            
            <!-- Password Card -->
            <div class="account-card">
                <h2>🔐 Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <div class="password-requirements">
                        <strong>🔒 Password requirements:</strong>
                        <ul>
                            <li>At least 8 characters long</li>
                            <li>At least one number</li>
                            <li>At least one capital letter</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-save">
                        🔄 Update Password
                    </button>
                </form>
            </div>
            
            <!-- Membership Card -->
            <div class="account-card">
                <h2>🎫 Membership Details</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Membership Tier</label>
                        <select name="tier_id">
                            <?php 
                            $tiers->data_seek(0);
                            while($tier = $tiers->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $tier['tier_id']; ?>" 
                                <?php echo ($prefs['tier_id'] == $tier['tier_id']) ? 'selected' : ''; ?>>
                                <?php echo $tier['tier_name']; ?> - 
                                €<?php echo $tier['tier_id'] == 1 ? '100' : ($tier['tier_id'] == 2 ? '200' : ($tier['tier_id'] == 3 ? '500' : 'Custom')); ?>/month
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type">
                            <option value="monthly" <?php echo ($prefs['payment_type'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                            <option value="annual" <?php echo ($prefs['payment_type'] == 'annual') ? 'selected' : ''; ?>>Annual (10% discount)</option>
                        </select>
                    </div>
                    
                    <div class="info-text">
                        ℹ️ Changing your membership tier will take effect from your next billing cycle.
                    </div>
                    
                    <button type="submit" name="update_tier" class="btn-save">
                        💳 Update Membership
                    </button>
                </form>
            </div>
            
            <!-- Machine Skills Card -->
            <div class="account-card">
                <h2>🔧 Machine Skills</h2>
                <p class="info-text" style="margin-bottom: 15px;">
                    Select the machines you use and rate your skill level:
                </p>
                
                <form method="POST" id="skillsForm">
                    <div class="skills-container">
                        <?php foreach($areas as $area): 
                            $area_id = str_replace(' ', '_', $area);
                            $area_id = str_replace('/', '_', $area_id);
                            $is_checked = isset($existing_skills[$area]);
                            $current_skill = $existing_skills[$area] ?? 'beginner';
                        ?>
                        <div class="machine-skill-row">
                            <div class="machine-checkbox">
                                <input type="checkbox" name="areas[]" value="<?php echo $area; ?>" 
                                       id="area_<?php echo $area_id; ?>" 
                                       <?php echo $is_checked ? 'checked' : ''; ?>
                                       onchange="toggleSkill('<?php echo $area_id; ?>')">
                                <label for="area_<?php echo $area_id; ?>" class="machine-name"><?php echo $area; ?></label>
                            </div>
                            <div class="skill-selector" id="skills_<?php echo $area_id; ?>" 
                                 style="display: <?php echo $is_checked ? 'flex' : 'none'; ?>;">
                                <input type="hidden" name="<?php echo $area_id; ?>_skill" 
                                       id="skill_<?php echo $area_id; ?>" value="<?php echo $current_skill; ?>">
                                <button type="button" class="skill-badge beginner <?php echo $current_skill == 'beginner' ? 'selected' : ''; ?>" 
                                        onclick="setSkill('<?php echo $area_id; ?>', 'beginner', this)">Beginner</button>
                                <button type="button" class="skill-badge intermediate <?php echo $current_skill == 'intermediate' ? 'selected' : ''; ?>" 
                                        onclick="setSkill('<?php echo $area_id; ?>', 'intermediate', this)">Intermediate</button>
                                <button type="button" class="skill-badge expert <?php echo $current_skill == 'expert' ? 'selected' : ''; ?>" 
                                        onclick="setSkill('<?php echo $area_id; ?>', 'expert', this)">Expert</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Summary of selected skills -->
                    <div class="selected-skills-summary" id="skillsSummary">
                        <?php 
                        $count = count($existing_skills);
                        if ($count > 0) {
                            echo "📌 <span>$count</span> machine" . ($count > 1 ? 's' : '') . " selected";
                        } else {
                            echo "📌 No machines selected yet";
                        }
                        ?>
                    </div>
                    
                    <!-- Skill Level Breakdown -->
                    <?php if (count($existing_skills) > 0): 
                        $beginners = 0; $intermediates = 0; $experts = 0;
                        foreach ($existing_skills as $skill) {
                            if ($skill == 'beginner') $beginners++;
                            if ($skill == 'intermediate') $intermediates++;
                            if ($skill == 'expert') $experts++;
                        }
                    ?>
                    <div class="skill-breakdown">
                        <span class="skill-breakdown-badge beginner-badge">🔰 Beginner: <?php echo $beginners; ?></span>
                        <span class="skill-breakdown-badge intermediate-badge">📘 Intermediate: <?php echo $intermediates; ?></span>
                        <span class="skill-breakdown-badge expert-badge">⭐ Expert: <?php echo $experts; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="update_skills" class="btn-update-skills">
                        💾 Update Machine Skills
                    </button>
                </form>
            </div>
            
            <!-- Account Info Card -->
            <div class="account-card">
                <h2>📊 Account Information</h2>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Member Since:</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Account Status:</span>
                        <span class="info-value status-active">✓ Active</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Training Status:</span>
                        <span class="info-value status-<?php echo $prefs['training_status']; ?>">
                            <?php echo ucfirst($prefs['training_status']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Machines Known:</span>
                        <span class="info-value"><?php echo count($existing_skills); ?></span>
                    </div>
                </div>
                
                <div class="logout-link">
                    <a href="logout.php">🚪 Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSkill(areaId) {
            var checkbox = document.getElementById('area_' + areaId);
            var skillDiv = document.getElementById('skills_' + areaId);
            skillDiv.style.display = checkbox.checked ? 'flex' : 'none';
            
            // Update summary
            updateSkillsSummary();
        }
        
        function setSkill(areaId, level, btn) {
            // Update hidden input
            document.getElementById('skill_' + areaId).value = level;
            
            // Update button styles
            var buttons = document.querySelectorAll('#skills_' + areaId + ' .skill-badge');
            buttons.forEach(function(b) {
                b.classList.remove('selected');
            });
            btn.classList.add('selected');
        }
        
        function updateSkillsSummary() {
            var checkboxes = document.querySelectorAll('input[name="areas[]"]:checked');
            var count = checkboxes.length;
            var summary = document.getElementById('skillsSummary');
            
            if (count > 0) {
                summary.innerHTML = '📌 <span>' + count + '</span> machine' + (count > 1 ? 's' : '') + ' selected';
            } else {
                summary.innerHTML = '📌 No machines selected yet';
            }
        }
        
        // Add change listeners to all checkboxes
        document.querySelectorAll('input[name="areas[]"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', updateSkillsSummary);
        });
    </script>
</body>
</html>