<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();

// Get user data from database
$conn = new mysqli('localhost', 'root', '', 'booking_system');
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$prefs = $conn->query("SELECT * FROM user_preferences WHERE user_id = $user_id")->fetch_assoc();

// Learning Resources Data
$videos = [
    ['title' => 'Laser Cutter Basics', 'duration' => '12:34', 'url' => '#'],
    ['title' => '3D Printer Setup', 'duration' => '8:21', 'url' => '#'],
    ['title' => 'CNC Router Safety', 'duration' => '15:45', 'url' => '#'],
    ['title' => 'Vinyl Cutting Guide', 'duration' => '6:18', 'url' => '#'],
];

$wiki_articles = [
    ['title' => 'Machine Safety Guidelines', 'new' => true],
    ['title' => 'Material Selection Guide', 'new' => false],
    ['title' => 'Troubleshooting Common Issues', 'new' => false],
    ['title' => 'Project Ideas & Inspiration', 'new' => true],
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
                <?php if($prefs['tier_id']): ?>
                <div class="info-row">
                    <strong>Membership Tier:</strong>
                    <span>
                        <?php 
                        $tier = $conn->query("SELECT tier_name FROM membership_tiers WHERE tier_id = " . $prefs['tier_id'])->fetch_assoc();
                        echo $tier['tier_name'] ?? 'Standard';
                        ?>
                    </span>
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
                    </div>
                    
                    <!-- Wiki Articles -->
                    <div>
                        <div class="resource-header">
                            <span style="font-size: 1.5em;">📖</span>
                            <h3>Wiki Articles</h3>
                        </div>
                        <div class="article-list">
                            <?php foreach($wiki_articles as $article): ?>
                            <div class="article-item" onclick="readArticle('<?php echo $article['title']; ?>')">
                                <div class="video-thumb">📄</div>
                                <div class="article-info">
                                    <div class="article-title">
                                        <?php echo $article['title']; ?>
                                        <?php if($article['new']): ?>
                                            <span class="new-badge">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button class="read-btn">Read</button>
                            </div>
                            <?php endforeach; ?>
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

    <script>
        function showHelp(question, answer) {
            document.getElementById('modalQuestion').textContent = question;
            document.getElementById('modalAnswer').textContent = answer;
            document.getElementById('helpModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('helpModal').style.display = 'none';
        }

        function readArticle(title) {
            alert('Opening article: ' + title + '\n(This would open the wiki in a real implementation)');
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