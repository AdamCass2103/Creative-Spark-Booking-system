<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// ============================================
// HANDLE FORM SUBMISSIONS
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Create new training session
    if (isset($_POST['create_session'])) {
        $tier_id = $_POST['tier_id'];
        $session_date = $_POST['session_date'];
        $session_time = $_POST['session_time'];
        $max_attendees = $_POST['max_attendees'] ?? 4;
        $notes = $conn->real_escape_string($_POST['notes']);
        $trainer_id = $_SESSION['user_id'] ?? 1; // Oscar's ID
        
        $stmt = $conn->prepare("INSERT INTO training_sessions 
            (tier_id, trainer_id, session_date, session_time, max_attendees, notes) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissis", $tier_id, $trainer_id, $session_date, $session_time, $max_attendees, $notes);
        $stmt->execute();
        $success = "Training session created for " . ($_POST['tier_name'] ?? 'Fabber tier');
    }
    
    // Mark attendance
    if (isset($_POST['mark_attendance'])) {
        $session_id = $_POST['session_id'];
        $user_id = $_POST['user_id'];
        $status = $_POST['attendance_status'];
        
        $conn->query("UPDATE session_attendees 
                      SET attendance_status = '$status',
                          completed_training = " . ($status == 'attended' ? '1' : '0') . "
                      WHERE session_id = $session_id AND user_id = $user_id");
        
        // If attended, update their tier training status
        if ($status == 'attended') {
            $session = $conn->query("SELECT tier_id FROM training_sessions WHERE session_id = $session_id")->fetch_assoc();
            $tier_id = $session['tier_id'];
            
            // Check if user already has training for this tier
            $check = $conn->query("SELECT training_id FROM user_training_completed 
                                   WHERE user_id = $user_id AND tier_id = $tier_id");
            
            if ($check->num_rows > 0) {
                $conn->query("UPDATE user_training_completed 
                              SET training_date = CURDATE(),
                                  expiry_date = DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
                              WHERE user_id = $user_id AND tier_id = $tier_id");
            } else {
                $conn->query("INSERT INTO user_training_completed 
                              (user_id, tier_id, training_date, expiry_date) 
                              VALUES ($user_id, $tier_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
            }
            
            // Update user_preferences status to 'completed'
            $conn->query("UPDATE user_preferences 
                          SET training_status = 'completed' 
                          WHERE user_id = $user_id");
            
            $success = "✅ Training marked as COMPLETED for user! Status updated.";
        }
    }
    
    // Register user for session
    if (isset($_POST['register_user'])) {
        $session_id = $_POST['session_id'];
        $user_id = $_POST['user_id'];
        
        $conn->query("INSERT IGNORE INTO session_attendees (session_id, user_id) 
                      VALUES ($session_id, $user_id)");
        $success = "User registered for session!";
    }
}

// Get all tiers for dropdown
$tiers = $conn->query("SELECT * FROM membership_tiers ORDER BY tier_level");

// Get upcoming sessions
$upcoming_sessions = $conn->query("
    SELECT ts.*, mt.tier_name, mt.tier_level,
           (SELECT COUNT(*) FROM session_attendees WHERE session_id = ts.session_id) as registered_count
    FROM training_sessions ts
    JOIN membership_tiers mt ON ts.tier_id = mt.tier_id
    WHERE ts.session_date >= CURDATE()
    ORDER BY ts.session_date ASC, ts.session_time ASC
");

// Get pending training requests
$pending_training = $conn->query("
    SELECT u.user_id, u.name, u.email, u.created_at, up.training_status
    FROM users u
    JOIN user_preferences up ON u.user_id = up.user_id
    WHERE up.needs_training = 1 
    AND up.training_status != 'completed'
    ORDER BY u.created_at ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Session Manager - Creative Spark FabLab</title>
    <link rel="stylesheet" href="../css/training.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Training Session Manager</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
            </div>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="alert">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="openTab(event, 'upcoming')">📋 Upcoming Sessions</div>
            <div class="tab" onclick="openTab(event, 'create')">➕ Create Session</div>
            <div class="tab" onclick="openTab(event, 'pending')">⏳ Pending Training</div>
        </div>
        
        <!-- Tab 1: Upcoming Sessions -->
        <div id="upcoming" class="tab-content active">
            <h2>Upcoming Training Sessions</h2>
            
            <?php if($upcoming_sessions && $upcoming_sessions->num_rows == 0): ?>
            <div class="empty-state">
                <p style="color: white;">No upcoming sessions scheduled.</p>
                <p style="color: rgba(255,255,255,0.8);">Click the "Create Session" tab to add one!</p>
            </div>
            <?php elseif($upcoming_sessions): ?>
            <div class="session-grid">
                <?php while($session = $upcoming_sessions->fetch_assoc()): 
                    $tier_class = 'tier-badge-' . strtolower(str_replace(' ', '', $session['tier_name']));
                ?>
                <div class="session-card">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <h3><?php echo $session['tier_name']; ?></h3>
                        <span class="machine-tag <?php echo $tier_class; ?>">
                            Tier <?php echo $session['tier_level']; ?>
                        </span>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                        <span class="machine-tag">📅 <?php echo date('d/m/Y', strtotime($session['session_date'])); ?></span>
                        <span class="machine-tag">⏰ <?php echo date('H:i', strtotime($session['session_time'])); ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 10px;">
                        <span style="font-weight: 600;">👥 Registered: <?php echo $session['registered_count']; ?>/<?php echo $session['max_attendees']; ?></span>
                        <span class="spots-left">🎯 <?php echo $session['max_attendees'] - $session['registered_count']; ?> spots</span>
                    </div>
                    
                    <?php if($session['notes']): ?>
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <p style="color: #666; font-size: 0.95em;">📝 <?php echo $session['notes']; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Registered Users List -->
                    <div style="margin-top: 20px;">
                        <p style="font-weight: 600; margin-bottom: 15px; color: #333;">📋 Registered Users</p>
                        <?php
                        $attendees = $conn->query("
                            SELECT u.name, sa.attendance_status 
                            FROM session_attendees sa
                            JOIN users u ON sa.user_id = u.user_id
                            WHERE sa.session_id = " . $session['session_id']
                        );
                        if($attendees && $attendees->num_rows > 0):
                            while($attendee = $attendees->fetch_assoc()):
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 10px; background: #f5f5f5; border-radius: 8px;">
                            <span style="font-weight: 500;"><?php echo $attendee['name']; ?></span>
                            <span class="attendance-status" style="color: <?php echo $attendee['attendance_status'] == 'attended' ? '#2E7D32' : '#ff9800'; ?>; font-weight: 600;">
                                <?php echo ucfirst($attendee['attendance_status'] ?? 'Pending'); ?>
                            </span>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <p style="color: #999; font-style: italic; text-align: center; padding: 20px; background: #f5f5f5; border-radius: 8px;">
                            No users registered yet
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <button onclick="showRegisterModal(<?php echo $session['session_id']; ?>)" 
                            class="register-btn"
                            <?php echo $session['registered_count'] >= $session['max_attendees'] ? 'disabled' : ''; ?>>
                        <?php echo $session['registered_count'] >= $session['max_attendees'] ? 'Session Full' : '➕ Register Users'; ?>
                    </button>
                    
                    <button onclick="markAllComplete(<?php echo $session['session_id']; ?>)" 
                            class="btn-small" 
                            style="width: 100%;">
                        🎓 Demo: Complete All Users
                    </button>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Tab 2: Create Session -->
        <div id="create" class="tab-content">
            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <h2 style="color: #2E7D32;">➕ Create New Training Session</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Membership Tier</label>
                        <select name="tier_id" required onchange="this.form.tier_name.value=this.options[this.selectedIndex].text">
                            <option value="">-- Select a tier --</option>
                            <?php 
                            if($tiers) {
                                $tiers->data_seek(0);
                                while($tier = $tiers->fetch_assoc()): ?>
                                <option value="<?php echo $tier['tier_id']; ?>">
                                    <?php echo $tier['tier_name']; ?>
                                </option>
                            <?php endwhile; } ?>
                        </select>
                        <input type="hidden" name="tier_name">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="session_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time" name="session_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Max Attendees</label>
                        <input type="number" name="max_attendees" min="1" max="10" value="4">
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (optional)</label>
                        <textarea name="notes" rows="4" placeholder="What to bring, prerequisites, meeting point, etc..."></textarea>
                    </div>
                    
                    <button type="submit" name="create_session" class="btn" style="width: 100%; font-size: 16px; padding: 15px;">
                        ➕ Create Training Session
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Tab 3: Pending Training -->
        <div id="pending" class="tab-content">
            <h2>Users Needing Training</h2>
            
            <?php if($pending_training && $pending_training->num_rows == 0): ?>
            <div class="empty-state">
                <p style="color: white;">✅ All users are trained!</p>
                <p style="color: rgba(255,255,255,0.8);">Great job, Oscar!</p>
            </div>
            <?php elseif($pending_training): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $pending_training->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $user['name']; ?></strong></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <span class="status-<?php echo $user['training_status']; ?>">
                                    <?php echo ucfirst($user['training_status']); ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="registerForSession(<?php echo $user['user_id']; ?>)" class="btn" style="padding: 8px 15px; font-size: 13px;">
                                    📅 Assign
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Register User Modal -->
    <div id="registerModal">
        <div>
            <h3>📝 Register User for Session</h3>
            <form method="POST" id="registerForm">
                <input type="hidden" name="session_id" id="modalSessionId">
                <div class="form-group">
                    <label>Select User</label>
                    <select name="user_id" id="userSelect" required>
                        <option value="">-- Choose a user --</option>
                        <?php
                        $users = $conn->query("SELECT user_id, name FROM users ORDER BY name");
                        if($users) {
                            while($user = $users->fetch_assoc()):
                            ?>
                            <option value="<?php echo $user['user_id']; ?>"><?php echo $user['name']; ?></option>
                            <?php endwhile; 
                        } ?>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" name="register_user" class="btn" style="flex: 2;">✅ Register</button>
                    <button type="button" onclick="closeModal()" class="btn back-btn" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openTab(event, tabName) {
        var tabs = document.getElementsByClassName('tab-content');
        for(var i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }
        var tabButtons = document.getElementsByClassName('tab');
        for(var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove('active');
        }
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }
    
    function showRegisterModal(sessionId) {
        document.getElementById('modalSessionId').value = sessionId;
        document.getElementById('registerModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('registerModal').style.display = 'none';
    }
    
    function registerForSession(userId) {
        var select = document.getElementById('userSelect');
        for(var i = 0; i < select.options.length; i++) {
            if(select.options[i].value == userId) {
                select.options[i].selected = true;
                break;
            }
        }
        alert('Please select a session from the Upcoming tab first!');
    }
    
    function markAllComplete(sessionId) {
        if(confirm('🎓 Mark ALL users in this session as COMPLETED?')) {
            var button = event.target;
            var sessionCard = button.closest('.session-card');
            
            if(sessionCard) {
                var statusSpans = sessionCard.querySelectorAll('.attendance-status');
                statusSpans.forEach(function(span) {
                    span.innerHTML = 'Completed 🎓';
                    span.style.color = '#2E7D32';
                });
                
                var spotsSpan = sessionCard.querySelector('.spots-left');
                if(spotsSpan) {
                    spotsSpan.innerHTML = '🎯 0 spots';
                }
                
                alert('✅ Demo: All users marked as completed!');
            }
        }
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById('registerModal');
        if(event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>