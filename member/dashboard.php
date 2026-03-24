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

// ... rest of your code continues
// Learning Resources Data with corrected wiki URLs (using /home/ structure)
$wiki_base_url = "https://creative-spark-enterprise-fablab.gitbook.io/fablab-wiki/";

$videos = [
        [
            'title' => 'Laser Cutting for Beginners',
            'duration' => '21:36',
            'url' => 'https://learn.microsoft.com/en-us/shows/themakershow/mini-laser-cutting'
        ],
        [
            'title' => '3D Printing Beginner Guide',
            'duration' => '30:19',
            'url' => 'https://www.youtube.com/watch?v=2vFdwz4U1VQ'
        ],
        [
            'title' => 'CNC Milling Basics for Beginners',
            'duration' => '18:45',
            'url' => 'https://www.youtube.com/watch?v=cj0-wSGGe6g'
        ],
        [
            'title' => 'Vinyl Cutter Tutorial for Beginners',
            'duration' => '9:43',
            'url' => 'https://www.youtube.com/watch?v=G9V-F7kWs8g'
        ]
];

// Wiki articles with CORRECT URLs based on your links
$wiki_articles = [
    [
        'title' => 'Glossary of Terms', 
        'new' => true,
        'url' => $wiki_base_url . 'home/glossary-of-terms',
        'description' => 'Common terms in digital fabrication'
    ],
    [
        'title' => 'Fabrication Processes', 
        'new' => false,
        'url' => $wiki_base_url . 'home/fabrication-processes',
        'description' => 'Guides for using equipment safely'
    ],
    
    [
        'title' => 'Extra Resources', 
        'new' => false,
        'url' => $wiki_base_url . 'home/extra-resources',
        'description' => 'Tutorials and community forums'
    ],
    [
        'title' => 'Fab Academy Diploma', 
        'new' => false,
        'url' => $wiki_base_url . 'home/fab-academy-diploma',
        'description' => 'Intensive 5-month program in digital fabrication'
    ],
];

$quick_help = [
    [
        'question' => 'How do I book a machine?',
        'answer' => 'Go to the bookings page, select your machine, choose a time, and confirm. You need to have completed training for that machine first.',
        'icon' => '📅'
    ],
    [
        'question' => 'What safety gear do I need?',
        'answer' => 'Safety glasses are required for all machines. Ear protection for loud equipment. Dust masks for sanding/woodwork.',
        'icon' => '🛡️'
    ],
    [
        'question' => 'Can I bring my own materials?',
        'answer' => 'Yes, but they must be approved by staff first. Some materials can damage machines or are fire hazards.',
        'icon' => '📦'
    ]
];
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
            <a href="book_training.php" class="btn-book">
                📅 Book Training
                <?php if (isset($pending_bookings) && $pending_bookings > 0): ?>
                    <span class="badge"><?php echo $pending_bookings; ?> pending</span>
                <?php endif; ?>
            </a>
            <a href="my_bookings.php" class="btn-book" style="background: #9c27b0;">
                📋 My Bookings
            </a>
            <a href="my_account.php" class="btn-book" style="background: #2E7D32;">👤 My Account</a>
            <a href="/fabman" class="btn-book" style="background: #ff9800;" target="_blank">
                🛠️ FabMan Portal
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
    
    <!-- PAYMENT SECTION - Only shows when approved -->
    <?php if ($prefs['training_status'] == 'approved'): ?>
    <div class="info-row" style="border-top: 2px dashed #2E7D32; padding-top: 15px; margin-top: 10px;">
        <strong>Payment Status:</strong>
        <span>
            <?php 
            // Check if already paid
            $payment_check = $conn->query("SELECT payment_status FROM users WHERE user_id = $user_id")->fetch_assoc();
            $payment_status = $payment_check['payment_status'] ?? 'pending';
            
            if ($payment_status == 'paid'): ?>
                <span style="color: #4caf50; font-weight: bold;">✅ Paid</span>
            <?php else: ?>
                <span style="color: #ff9800; font-weight: bold;">⏳ Payment Required</span>
            <?php endif; ?>
        </span>
    </div>
    
    <?php if ($payment_status != 'paid'): ?>
    <div style="margin-top: 20px; text-align: center;">
        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
            <div style="font-size: 1.8em; font-weight: bold; color: #2E7D32;">
                €<?php 
                // Get amount based on tier
                $amount = $prefs['tier_id'] == 1 ? '100' : ($prefs['tier_id'] == 2 ? '200' : ($prefs['tier_id'] == 3 ? '500' : 'Custom'));
                echo $amount;
                ?>
            </div>
            <div style="color: #666;"><?php echo ucfirst($prefs['payment_type'] ?? 'monthly'); ?> payment</div>
        </div>
        
        <a href="payment.php" class="btn-book" style="background: #ff9800; width: 100%; text-align: center; justify-content: center;">
            💳 Complete Payment Now
        </a>
        <p style="font-size: 0.8em; color: #999; margin-top: 8px;">
            Your membership is approved! Complete payment to unlock full access.
        </p>
    </div>
    <?php endif; ?>
    
    <?php elseif ($prefs['training_status'] == 'pending'): ?>
    <div style="margin-top: 15px; padding: 10px; background: #fff3e0; border-radius: 8px; text-align: center;">
        <span style="color: #ff9800;">⏳ Waiting for approval</span>
        <p style="font-size: 0.85em; color: #666; margin-top: 5px;">
            You'll be able to pay once your membership is approved.
        </p>
    </div>
    <?php endif; ?>
</div>

            <!-- In your member-dashboard.php - Replace the Learning Resources section with this -->

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
                <?php foreach($videos as $video): ?>
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
            
            <!-- Machine Tutorials Library Button - Styled like Visit Wiki -->
            <div style="margin-top: 20px;">
                <a href="tutorials.php" class="btn-book" style="display: inline-block; padding: 10px 20px; font-size: 1em; background: #ff6b6b; width: 100%; text-align: center; box-sizing: border-box;">
                    📚 Machine Tutorials Library
                </a>
                <p style="font-size: 0.8em; color: #666; margin-top: 5px; text-align: center;">Complete guides for all machines</p>
            </div>
        </div>
        
        <!-- Wiki Articles -->
        <div>
            <div class="resource-header">
                <span style="font-size: 1.5em;">📖</span>
                <h3>Wiki Articles</h3>
            </div>
            <div class="article-list">
                <?php foreach($wiki_articles as $article): ?>
                <div class="article-item" onclick="window.open('<?php echo $article['url']; ?>', '_blank')">
                    <div class="video-thumb">📄</div>
                    <div class="article-info">
                        <div class="article-title">
                            <?php echo $article['title']; ?>
                            <?php if($article['new']): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                        </div>
                        <div class="article-meta" style="font-size: 0.8em; color: #666; margin-top: 3px;">
                            <?php echo $article['description']; ?>
                        </div>
                    </div>
                    <a href="<?php echo $article['url']; ?>" target="_blank" class="read-btn" onclick="event.stopPropagation();">Read</a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Wiki Navigation Link -->
            <div style="margin-top: 15px; text-align: right;">
                <a href="<?php echo $wiki_base_url; ?>" target="_blank" class="btn-book" style="display: inline-block; padding: 8px 15px; font-size: 0.9em;">
                    Visit Full Wiki 📚
                </a>
            </div>
        </div>
    </div>
</div>
            <!-- Quick Help -->
            <div class="card" style="grid-column: span 2;">
                <h2>❓ Quick Help</h2>
                <div class="help-grid">
                    <?php foreach($quick_help as $help): ?>
                    <div class="help-card" onclick="showHelp('<?php echo $help['question']; ?>', '<?php echo $help['answer']; ?>')">
                        <div class="help-icon"><?php echo $help['icon']; ?></div>
                        <div class="help-question"><?php echo $help['question']; ?></div>
                        <div class="help-answer-preview">
                            Click for answer...
                        </div>
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

    <style>
        /* Additional styles for wiki articles */
        .article-meta {
            font-size: 0.8em;
            color: #666;
            margin-top: 3px;
        }
    </style>

    <script>
        function showHelp(question, answer) {
            document.getElementById('modalQuestion').textContent = question;
            document.getElementById('modalAnswer').textContent = answer;
            document.getElementById('helpModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('helpModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('helpModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>