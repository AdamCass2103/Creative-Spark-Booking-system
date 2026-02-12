<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Create new training session
    if (isset($_POST['create_session'])) {
        $machine_id = $_POST['machine_id'];
        $session_date = $_POST['session_date'];
        $session_time = $_POST['session_time'];
        $max_attendees = $_POST['max_attendees'] ?? 4;
        $notes = $conn->real_escape_string($_POST['notes']);
        $trainer_id = $_SESSION['user_id']; // Oscar's ID
        
        $stmt = $conn->prepare("INSERT INTO training_sessions 
            (machine_id, trainer_id, session_date, session_time, max_attendees, notes) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissis", $machine_id, $trainer_id, $session_date, $session_time, $max_attendees, $notes);
        $stmt->execute();
        $success = "Training session created!";
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
        
        // If attended, update their machine training status
        if ($status == 'attended') {
            $session = $conn->query("SELECT machine_id FROM training_sessions WHERE session_id = $session_id")->fetch_assoc();
            $machine_id = $session['machine_id'];
            
            // Check if user already has a training record
            $check = $conn->query("SELECT training_id FROM user_machine_training 
                                   WHERE user_id = $user_id AND machine_id = $machine_id");
            
            if ($check->num_rows > 0) {
                $conn->query("UPDATE user_machine_training 
                              SET training_status = 'completed', 
                                  training_date = CURDATE(),
                                  expiry_date = DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
                              WHERE user_id = $user_id AND machine_id = $machine_id");
            } else {
                $conn->query("INSERT INTO user_machine_training 
                              (user_id, machine_id, training_status, training_date, expiry_date) 
                              VALUES ($user_id, $machine_id, 'completed', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
            }
        }
    }
    
    // Register user for session (Oscar can manually add)
    if (isset($_POST['register_user'])) {
        $session_id = $_POST['session_id'];
        $user_id = $_POST['user_id'];
        
        $conn->query("INSERT IGNORE INTO session_attendees (session_id, user_id) 
                      VALUES ($session_id, $user_id)");
    }
}

// Get all machines for dropdown
$machines = $conn->query("SELECT * FROM machines ORDER BY machine_name");

// Get upcoming sessions
$upcoming_sessions = $conn->query("
    SELECT ts.*, m.machine_name, 
           (SELECT COUNT(*) FROM session_attendees WHERE session_id = ts.session_id) as registered_count
    FROM training_sessions ts
    JOIN machines m ON ts.machine_id = m.machine_id
    WHERE ts.session_date >= CURDATE()
    ORDER BY ts.session_date ASC, ts.session_time ASC
");

// Get pending training requests
$pending_training = $conn->query("
    SELECT u.user_id, u.name, u.email, up.created_at
    FROM users u
    JOIN user_preferences up ON u.user_id = up.user_id
    WHERE up.needs_training = 1 
    AND up.training_status = 'approved'
    AND NOT EXISTS (
        SELECT 1 FROM user_machine_training umt 
        WHERE umt.user_id = u.user_id 
        AND umt.training_status = 'completed'
    )
    ORDER BY up.created_at ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Training Sessions - FabLab</title>
    <style>
        .session-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .session-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .session-card h3 { margin: 0 0 10px 0; color: #1a73e8; }
        .machine-tag { background: #e3f2fd; color: #0d47a1; padding: 4px 10px; border-radius: 20px; font-size: 0.9em; }
        .spots-left { background: #e8f5e8; color: #2e7d32; padding: 4px 10px; border-radius: 20px; }
        .register-btn { background: #1a73e8; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }
        .register-btn:hover { background: #0d62d9; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #f0f0f0; cursor: pointer; border-radius: 6px; }
        .tab.active { background: #1a73e8; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Training Session Manager</h1>
            <div class="nav">
                <a href="admin.php" class="btn back-btn">← Back to Admin</a>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="openTab(event, 'upcoming')">📋 Upcoming Sessions</div>
            <div class="tab" onclick="openTab(event, 'create')">➕ Create Session</div>
            <div class="tab" onclick="openTab(event, 'pending')">⏳ Pending Training</div>
        </div>
        
        <!-- Tab 1: Upcoming Sessions -->
        <div id="upcoming" class="tab-content active">
            <h2>Upcoming Training Sessions</h2>
            <div class="session-grid">
                <?php while($session = $upcoming_sessions->fetch_assoc()): ?>
                <div class="session-card">
                    <h3><?php echo $session['machine_name']; ?> Training</h3>
                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <span class="machine-tag">📅 <?php echo date('d/m/Y', strtotime($session['session_date'])); ?></span>
                        <span class="machine-tag">⏰ <?php echo date('H:i', strtotime($session['session_time'])); ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span>👥 Registered: <?php echo $session['registered_count']; ?>/<?php echo $session['max_attendees']; ?></span>
                        <span class="spots-left">🎯 <?php echo $session['max_attendees'] - $session['registered_count']; ?> spots left</span>
                    </div>
                    
                    <?php if($session['notes']): ?>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">📝 <?php echo $session['notes']; ?></p>
                    <?php endif; ?>
                    
                    <button onclick="showRegisterModal(<?php echo $session['session_id']; ?>)" 
                            class="register-btn"
                            <?php echo $session['registered_count'] >= $session['max_attendees'] ? 'disabled' : ''; ?>>
                        <?php echo $session['registered_count'] >= $session['max_attendees'] ? 'Full' : 'Register Users →'; ?>
                    </button>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Tab 2: Create Session -->
        <div id="create" class="tab-content">
            <div class="card" style="max-width: 600px;">
                <h2>Create New Training Session</h2>
                
                <?php if(isset($success)): ?>
                <div class="alert success">✅ <?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Machine:</label>
                        <select name="machine_id" required>
                            <option value="">Select machine...</option>
                            <?php 
                            $machines->data_seek(0);
                            while($machine = $machines->fetch_assoc()): ?>
                            <option value="<?php echo $machine['machine_id']; ?>">
                                <?php echo $machine['machine_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="date" name="session_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Time:</label>
                            <input type="time" name="session_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Max Attendees:</label>
                        <input type="number" name="max_attendees" min="1" max="10" value="4">
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (optional):</label>
                        <textarea name="notes" rows="3" placeholder="What to bring, prerequisites, etc..."></textarea>
                    </div>
                    
                    <button type="submit" name="create_session" class="btn" style="background: #4caf50;">
                        ➕ Create Training Session
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Tab 3: Pending Training -->
        <div id="pending" class="tab-content">
            <h2>Users Needing Training</h2>
            
            <?php if($pending_training->num_rows == 0): ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <p style="font-size: 1.2em; color: #4caf50;">✅ All approved users are trained!</p>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Requested</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $pending_training->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button onclick="registerForSession(<?php echo $user['user_id']; ?>)" class="btn-small">
                                📅 Assign to Session
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Register User Modal -->
    <div id="registerModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); z-index: 1000;">
        <h3>Register User for Session</h3>
        <form method="POST" id="registerForm">
            <input type="hidden" name="session_id" id="modalSessionId">
            <div class="form-group">
                <label>Select User:</label>
                <select name="user_id" id="userSelect" required>
                    <option value="">Choose user...</option>
                    <?php
                    $users = $conn->query("SELECT user_id, name FROM users ORDER BY name");
                    while($user = $users->fetch_assoc()):
                    ?>
                    <option value="<?php echo $user['user_id']; ?>"><?php echo $user['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="register_user" class="btn">Register</button>
                <button type="button" onclick="closeModal()" class="btn back-btn">Cancel</button>
            </div>
        </form>
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
        document.getElementById('registerModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('registerModal').style.display = 'none';
    }
    
    function registerForSession(userId) {
        // Pre-select user and open modal
        var select = document.getElementById('userSelect');
        for(var i = 0; i < select.options.length; i++) {
            if(select.options[i].value == userId) {
                select.options[i].selected = true;
                break;
            }
        }
        showRegisterModal(<?php echo isset($session['session_id']) ? $session['session_id'] : '1'; ?>);
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('registerModal');
        if(event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>