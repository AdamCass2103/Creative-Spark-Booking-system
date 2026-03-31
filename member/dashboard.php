<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Use the same database connection pattern as your login.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // For Vercel/Aiven connection
    if (getenv('VERCEL_ENV')) {
        $conn = mysqli_init();
        
        // Handle SSL certificate
        if (getenv('CA_CERT')) {
            $cert_path = '/tmp/ca.pem';
            file_put_contents($cert_path, getenv('CA_CERT'));
            $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
        } else {
            $conn->ssl_set(NULL, NULL, __DIR__ . '/../certs/ca.pem', NULL, NULL);
        }
        
        $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, 25849, NULL, MYSQLI_CLIENT_SSL);
    } else {
        // Local XAMPP connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
} catch (mysqli_sql_exception $e) {
    error_log("Dashboard connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();

// Get user data from database
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Get pending booking count for badge
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM session_attendees 
                                  WHERE user_id = $user_id AND booking_status = 'pending_approval'")->fetch_assoc()['count'];

// Get payment status
$payment_status = $user['payment_status'] ?? 'pending';

// Check if membership is active - ONLY show active dashboard if status is approved AND payment is paid
$is_active = ($prefs['training_status'] == 'approved' && $payment_status == 'paid');

// If not active, show the inactive message directly (not redirect to another file)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - Creative Spark</title>
    <link rel="stylesheet" href="../css/member-dashboard.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="logout.php" class="logout">Logout</a>
            <h1>👋 Welcome back, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="welcome">Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>

        <!-- Action Bar with Booking Buttons -->
        <div class="action-bar">

        <?php if ($is_active): ?>
            <a href="book_training.php" class="btn-book">
             📅 Book Training
        <?php if (isset($pending_bookings) && $pending_bookings > 0): ?>
            <span class="badge"><?php echo $pending_bookings; ?> pending</span>
        <?php endif; ?>
        </a>
            <?php else: ?>
         <a href="#" class="btn-book" onclick="showLocked()" style="opacity:0.6;">
                📅 Book Training
            </a>
            <?php endif; ?>


            <?php if ($is_active): ?>
                <a href="my_bookings.php" class="btn-book" style="background: #9c27b0;">
                    📋 My Bookings
                </a>
            <?php else: ?>
                <a href="#" class="btn-book" onclick="showLocked()" style="background:#9c27b0;opacity:0.6;">
                    📋 My Bookings
                </a>
            <?php endif; ?>


            <a href="my_account.php" class="btn-book" style="background: #2E7D32;">
                👤 My Account
            </a>


            <?php if ($is_active): ?>
                <a href="/fabman" class="btn-book" style="background: #ff9800;" target="_blank">
                    🛠️ FabMan Portal
                </a>
            <?php else: ?>
                <a href="#" class="btn-book" onclick="showLocked()" style="background:#ff9800;opacity:0.6;">
                    🛠️ FabMan Portal
                </a>
            <?php endif; ?>


            <a href="feedback.php" class="btn-book" style="background: #795548;">
                💬 Feedback
            </a>

            </div>
        
        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Profile Card -->
            <div class="card">
                <h2>📋 Your Profile</h2>
                <div class="info-row">
                    <strong>Email:</strong>
                    <span><?php echo $user['email']; ?></span>
                </div>
                <div class="info-row">
                    <strong>Phone:</strong>
                    <span><?php echo $user['phone'] ?? 'Not provided'; ?></span>
                </div>
                <div class="info-row">
                    <strong>Company:</strong>
                    <span><?php echo $user['company'] ?? 'Not provided'; ?></span>
                </div>
                <div class="info-row">
                    <strong>Address:</strong>
                    <span><?php echo $user['address'] ?? 'Not provided'; ?></span>
                </div>
            </div>
            
            <!-- Membership Card -->
            <div class="card">
                <h2>🎫 Membership Status</h2>
                <div class="info-row">
                    <strong>Returning Member:</strong>
                    <span><?php echo $prefs['is_returning_member'] ? 'Yes' : 'No'; ?></span>
                </div>
                <div class="info-row">
                    <strong>Training Required:</strong>
                    <span><?php echo $prefs['needs_training'] ? 'Yes' : 'No'; ?></span>
                </div>
                <div class="info-row">
                    <strong>Account Status:</strong>
                    <span class="status-<?php echo $prefs['training_status']; ?>">
                        <?php echo ucfirst($prefs['training_status']); ?>
                    </span>
                </div>
                <?php if ($prefs['training_status'] != 'approved'): ?>
                <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin: 20px 0;">
                <p style="margin: 0;">📧 You'll receive an email when your application is reviewed.</p>
                <p style="margin: 5px 0 0; font-size: 0.85em; color: #666;">Contact Oscar if you have questions.</p>
                </div>
                <?php else: ?>
                <a href="payment.php" class="reactivate-btn">
                    💳 Pay Now →
                </a>
                <div style="color: #666; font-size: 0.9em; margin: 20px 0;">
                    Your training is approved. Please complete payment to activate your membership.
                     </div>
                    <?php endif; ?>
            </div>

            <!-- Learning Resources -->
            <div class="card" style="grid-column: span 2;">
                <h2>📚 Learning Resources</h2>
                
                <div class="resources-grid">
                    <!-- Videos -->
                    <div>
                        <div class="resource-header">
                            <span style="font-size: 1.5em;">🎥</span>
                            <h3>Video Tutorials</h3>
                        </div>
                        <div class="video-list">
                            <?php 
                            $videos = [
                                ['title' => 'Laser Cutting for Beginners', 'duration' => '21:36', 'url' => 'https://learn.microsoft.com/en-us/shows/themakershow/mini-laser-cutting'],
                                ['title' => '3D Printing Beginner Guide', 'duration' => '30:19', 'url' => 'https://www.youtube.com/watch?v=2vFdwz4U1VQ'],
                                ['title' => 'CNC Milling Basics for Beginners', 'duration' => '18:45', 'url' => 'https://www.youtube.com/watch?v=cj0-wSGGe6g'],
                                ['title' => 'Vinyl Cutter Tutorial for Beginners', 'duration' => '9:43', 'url' => 'https://www.youtube.com/watch?v=G9V-F7kWs8g']
                            ];
                            foreach($videos as $video): ?>
                            <div class="video-item" onclick="window.open('<?php echo $video['url']; ?>', '_blank')">
                                <div class="video-thumb">▶️</div>
                                <div class="video-info">
                                    <div class="video-title"><?php echo $video['title']; ?></div>
                                    <div class="video-meta">
                                        <span>⏱️ <?php echo $video['duration']; ?></span>
                                    </div>
                                </div>
                                <button class="watch-btn">Watch</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <a href="tutorials.php" class="btn-book" style="display: inline-block; padding: 10px 20px; font-size: 1em; background: #ff6b6b; width: 100%; text-align: center;">
                                📚 Machine Tutorials Library
                            </a>
                        </div>
                    </div>
                    
                    <!-- Wiki Articles -->
                    <div>
                        <div class="resource-header">
                            <span style="font-size: 1.5em;">📖</span>
                            <h3>Wiki Articles</h3>
                        </div>
                        <div class="article-list">
                            <?php 
                            $wiki_base_url = "https://creative-spark-enterprise-fablab.gitbook.io/fablab-wiki/";
                            $wiki_articles = [
                                ['title' => 'Glossary of Terms', 'new' => true, 'url' => $wiki_base_url . 'home/glossary-of-terms', 'description' => 'Common terms in digital fabrication'],
                                ['title' => 'Fabrication Processes', 'new' => false, 'url' => $wiki_base_url . 'home/fabrication-processes', 'description' => 'Guides for using equipment safely'],
                                ['title' => 'Extra Resources', 'new' => false, 'url' => $wiki_base_url . 'home/extra-resources', 'description' => 'Tutorials and community forums'],
                                ['title' => 'Fab Academy Diploma', 'new' => false, 'url' => $wiki_base_url . 'home/fab-academy-diploma', 'description' => 'Intensive 5-month program in digital fabrication']
                            ];
                            foreach($wiki_articles as $article): ?>
                            <div class="article-item" onclick="window.open('<?php echo $article['url']; ?>', '_blank')">
                                <div class="video-thumb">📄</div>
                                <div class="article-info">
                                    <div class="article-title">
                                        <?php echo $article['title']; ?>
                                        <?php if($article['new']): ?>
                                            <span class="new-badge">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="article-meta"><?php echo $article['description']; ?></div>
                                </div>
                                <a href="<?php echo $article['url']; ?>" target="_blank" class="read-btn" onclick="event.stopPropagation();">Read</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <a href="<?php echo $wiki_base_url; ?>" target="_blank" class="btn-book" style="display: inline-block; padding: 10px 20px; font-size: 1em; background: #2E7D32; width: 100%; text-align: center; box-sizing: border-box;">
                                📚 Visit Full Wiki
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Help -->
            <div class="card" style="grid-column: span 2;">
                <h2>❓ Quick Help</h2>
                <div class="help-grid">
                    <?php 
                    $quick_help = [
                        ['question' => 'How do I book a machine?', 'answer' => 'Go to the bookings page, select your machine, choose a time, and confirm. You need to have completed training for that machine first.', 'icon' => '📅'],
                        ['question' => 'What safety gear do I need?', 'answer' => 'Safety glasses are required for all machines. Ear protection for loud equipment. Dust masks for sanding/woodwork.', 'icon' => '🛡️'],
                        ['question' => 'Can I bring my own materials?', 'answer' => 'Yes, but they must be approved by staff first. Some materials can damage machines or are fire hazards.', 'icon' => '📦']
                    ];
                    foreach($quick_help as $help): ?>
                    <div class="help-card" onclick="showHelp('<?php echo $help['question']; ?>', '<?php echo $help['answer']; ?>')">
                        <div class="help-icon"><?php echo $help['icon']; ?></div>
                        <div class="help-question"><?php echo $help['question']; ?></div>
                        <div class="help-answer-preview">Click for answer...</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div id="helpModal" class="modal">
        <div class="modal-content">
            <h3 id="modalQuestion"></h3>
            <p id="modalAnswer"></p>
            <button class="modal-close" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script>
        function showLocked() {
            document.getElementById('lockedModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('lockedModal').style.display = 'none';
        }
        function showHelp(question, answer) {
            document.getElementById('modalQuestion').textContent = question;
            document.getElementById('modalAnswer').textContent = answer;
            document.getElementById('helpModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('helpModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('helpModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>