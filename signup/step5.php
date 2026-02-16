<?php
session_start();
require_once '../includes/db_connect.php';

// Check if all steps completed
if (!isset($_SESSION['step4_complete'])) {
    header('Location: step4.php');
    exit();
}

$data = $_SESSION['signup'];
$error = '';

// Get tier name
$tier_result = $conn->query("SELECT tier_name FROM membership_tiers WHERE tier_id = " . ($data['tier_id'] ?? 1));
$tier_name = $tier_result->fetch_assoc()['tier_name'] ?? 'Fabber 1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CHECK IF EMAIL ALREADY EXISTS
    $email_check = $conn->query("SELECT user_id FROM users WHERE email = '" . $conn->real_escape_string($data['email']) . "'");
    
    if ($email_check->num_rows > 0) {
        $error = 'This email is already registered. Please <a href="step1.php">try again</a> with a different email or <a href="../dashboard.php">login</a>.';
    } else {
        // Insert into database
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $company = $conn->real_escape_string($data['company'] ?? '');
        $phone = $conn->real_escape_string($data['phone'] ?? '');
        $address = $conn->real_escape_string($data['address'] ?? '');
        $signature = $conn->real_escape_string($data['signature'] ?? '');
        $signed_date = $data['signed_date'] ?? date('Y-m-d');
        
        // Insert users table
        $conn->query("INSERT INTO users (name, email, password, company, phone, address, signature, signed_date) 
                      VALUES ('$name', '$email', '$password', '$company', '$phone', '$address', '$signature', '$signed_date')");
        $user_id = $conn->insert_id;
        
        // ============================================
        // USE CHECKBOX VALUES FROM SESSION
        // ============================================
        $tier_id = $data['tier_id'] ?? 1;
        $payment_type = $data['payment_type'] ?? 'monthly';
        
        // IMPORTANT: Get checkbox values from session
        $is_returning = isset($data['is_returning']) ? $data['is_returning'] : 0;
        $needs_training = isset($data['needs_training']) ? $data['needs_training'] : 1;
        $terms_accepted = isset($data['terms_accepted']) ? $data['terms_accepted'] : 1;
        
        $experience_text = $conn->real_escape_string($data['experience_text'] ?? '');
        $work_description = $conn->real_escape_string($data['work_description'] ?? '');
        
        // Insert user_preferences with checkbox values
        $conn->query("INSERT INTO user_preferences 
                      (user_id, is_returning_member, needs_training, terms_accepted, training_status, tier_id, payment_type, experience_text, work_description) 
                      VALUES ($user_id, $is_returning, $needs_training, $terms_accepted, 'pending', $tier_id, '$payment_type', '$experience_text', '$work_description')");
        
        // Insert selected areas
        if (!empty($data['areas'])) {
            foreach ($data['areas'] as $area) {
                $area = $conn->real_escape_string($area);
                $conn->query("INSERT INTO user_areas (user_id, area_name) VALUES ($user_id, '$area')");
            }
        }
        
        // Clear session
        session_destroy();
        
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Review & Submit - Step 5 of 5</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; margin-bottom: 10px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; margin: 0 5px; border-radius: 5px; }
        .step.completed { background: #4caf50; color: white; }
        
        .review-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .review-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .review-item {
            margin: 10px 0;
        }
        
        .review-item strong {
            display: block;
            color: #666;
            font-size: 14px;
        }
        
        .review-item span {
            font-size: 16px;
            color: #333;
        }
        
        .btn { 
            width: 100%; 
            padding: 12px; 
            background: #4caf50; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px; 
            cursor: pointer;
        }
        
        .btn:hover { background: #388e3c; }
        
        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        
        .error-box {
            background: #fee;
            color: #c00;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #c00;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
        }
        
        .badge-yes {
            background: #4caf50;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .badge-no {
            background: #f44336;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success) && $success): ?>
            <div class="success-box">
                <h2>✅ Application Submitted Successfully!</h2>
                <p>Thank you for joining Creative Spark FabLab. Your application is pending approval.</p>
                <p>You will receive an email once your account is activated.</p>
                <a href="../dashboard.php" class="btn" style="width: auto; padding: 12px 30px; margin-top: 20px;">Go to Dashboard</a>
            </div>
        <?php elseif (isset($error) && $error): ?>
            <div class="error-box">
                <h2>❌ Error</h2>
                <p><?php echo $error; ?></p>
                <a href="step1.php" class="btn" style="background: #1a73e8; width: auto; padding: 12px 30px; margin-top: 20px;">Start Over</a>
            </div>
        <?php else: ?>
        
        <h1>📋 Review Your Application</h1>
        
        <div class="step-indicator">
            <div class="step completed">1. Account</div>
            <div class="step completed">2. Membership</div>
            <div class="step completed">3. Experience</div>
            <div class="step completed">4. Safety</div>
            <div class="step completed">5. Review</div>
        </div>
        
        <div class="review-section">
            <h3>📋 Personal Details</h3>
            <div class="review-grid">
                <div class="review-item">
                    <strong>Name</strong>
                    <span><?php echo htmlspecialchars($data['name']); ?></span>
                </div>
                <div class="review-item">
                    <strong>Email</strong>
                    <span><?php echo htmlspecialchars($data['email']); ?></span>
                </div>
                <div class="review-item">
                    <strong>Company</strong>
                    <span><?php echo htmlspecialchars($data['company'] ?? 'Not provided'); ?></span>
                </div>
                <div class="review-item">
                    <strong>Phone</strong>
                    <span><?php echo htmlspecialchars($data['phone'] ?? 'Not provided'); ?></span>
                </div>
                <div class="review-item" style="grid-column: span 2;">
                    <strong>Address</strong>
                    <span><?php echo nl2br(htmlspecialchars($data['address'] ?? 'Not provided')); ?></span>
                </div>
            </div>
        </div>
        
        <!-- NEW SECTION: Membership Options with Checkboxes -->
        <div class="review-section">
            <h3>✅ Membership Options</h3>
            <div class="review-grid">
                <div class="review-item">
                    <strong>Returning Member</strong>
                    <span>
                        <?php echo (isset($data['is_returning']) && $data['is_returning']) ? 'Yes' : 'No'; ?>
                        <?php if (isset($data['is_returning']) && $data['is_returning']): ?>
                            <span class="badge-yes">Returning</span>
                        <?php else: ?>
                            <span class="badge-no">New</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="review-item">
                    <strong>Needs Training</strong>
                    <span>
                        <?php echo (isset($data['needs_training']) && $data['needs_training']) ? 'Yes' : 'No'; ?>
                        <?php if (isset($data['needs_training']) && $data['needs_training']): ?>
                            <span class="badge-yes">Training Required</span>
                        <?php else: ?>
                            <span class="badge-no">No Training</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="review-item">
                    <strong>Terms Accepted</strong>
                    <span>
                        <?php echo (isset($data['terms_accepted']) && $data['terms_accepted']) ? '✅ Yes' : '❌ No'; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="review-section">
            <h3>🎫 Membership</h3>
            <div class="review-grid">
                <div class="review-item">
                    <strong>Plan</strong>
                    <span><?php echo $tier_name; ?></span>
                </div>
                <div class="review-item">
                    <strong>Payment</strong>
                    <span><?php echo ucfirst($data['payment_type'] ?? 'monthly'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="review-section">
            <h3>🔧 Experience & Areas</h3>
            <?php if (!empty($data['areas'])): ?>
            <div class="review-item">
                <strong>Areas selected</strong>
                <span><?php echo implode(', ', $data['areas']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($data['experience_text'])): ?>
            <div class="review-item">
                <strong>Experience</strong>
                <span><?php echo nl2br(htmlspecialchars($data['experience_text'])); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($data['work_description'])): ?>
            <div class="review-item">
                <strong>Work description</strong>
                <span><?php echo nl2br(htmlspecialchars($data['work_description'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="review-section">
            <h3>⚠️ Safety Agreements</h3>
            <p>✓ Safety orientation acknowledged</p>
            <p>✓ Machine inductions understood</p>
            <p>✓ PPE requirements accepted</p>
            <p>✓ Damages responsibility accepted</p>
            
            <div class="review-grid" style="margin-top: 15px;">
                <div class="review-item">
                    <strong>Signature</strong>
                    <span><?php echo htmlspecialchars($data['signature']); ?></span>
                </div>
                <div class="review-item">
                    <strong>Date</strong>
                    <span><?php echo $data['signed_date']; ?></span>
                </div>
            </div>
        </div>
        
        <form method="POST">
            <button type="submit" class="btn">✓ SUBMIT APPLICATION</button>
        </form>
        
        <a href="step4.php" class="back-link">← Back to Safety</a>
        
        <?php endif; ?>
    </div>
</body>
</html>