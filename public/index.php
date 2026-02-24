<?php
require_once __DIR__ . '/../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Spark FabLab</title>
    <link rel="stylesheet" href="/booking-system/css/landing.css"> 
    </style>
</head>
<body>
<div class="container">
    <div class="fablab-logo">
        <img src="/booking-system/images/FabLab logo.png" alt="Creative Spark FabLab Logo">
    </div>

        <h1>Creative Spark</h1>
        <h1 style="color: #764ba2;">Enterprise FabLab</h1>
        
        <div class="subtitle">
            Digital fabrication, innovation, and community
        </div>
        
        <button onclick="showOptions()" class="get-started-btn">
            GET STARTED →
        </button>
        
        <div id="optionsPanel" class="options-panel">
            <h2>How would you like to continue?</h2>
            <p>Choose your path</p>
            
            <div class="option-grid">
                <a href="../signup/step1.php" class="option-card">
                    <div class="option-icon">🆕</div>
                    <div class="option-title">New Member</div>
                    <div class="option-desc">
                        Create a new membership account<br>
                        <strong>5-minute application</strong>
                    </div>
                </a>
                
                <a href="login.php" class="option-card">
                    <div class="option-icon">👤</div>
                    <div class="option-title">Returning Member</div>
                    <div class="option-desc">
                        Login to your dashboard<br>
                        <strong>View training status & profile</strong>
                    </div>
                </a>
                
                <a href="../admin/login.php" class="option-card">
                    <div class="option-icon">👨‍💼</div>
                    <div class="option-title">Staff Access</div>
                    <div class="option-desc">
                        Oscar and team only<br>
                        <strong>Admin panel</strong>
                    </div>
                </a>
            </div>
            
            <div class="admin-link">
                <a href="#" onclick="hideOptions()">← Go back</a>
            </div>
        </div>
        
        <div class="footer-note">
            ⚡ Dundalk, Co. Louth
        </div>
    </div>
    
    <script>
        function showOptions() {
            document.getElementById('optionsPanel').classList.add('show');
            document.getElementById('optionsPanel').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideOptions() {
            document.getElementById('optionsPanel').classList.remove('show');
        }
    </script>
</body>
</html>