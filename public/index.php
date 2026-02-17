<?php
// Fix the path - use __DIR__ to get absolute path
require_once __DIR__ . '/../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Spark FabLab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            max-width: 800px; 
            margin: 20px; 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.2em;
        }
        .fablab-logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 60px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3em;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .get-started-btn {
            display: inline-block;
            padding: 20px 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.5em;
            font-weight: bold;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: transform 0.3s;
            border: none;
            cursor: pointer;
        }
        .get-started-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }
        .options-panel {
            display: none;
            margin-top: 30px;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 15px;
            animation: slideDown 0.5s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .options-panel.show {
            display: block;
        }
        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .option-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border: 2px solid #eee;
        }
        .option-card:hover {
            transform: translateY(-5px);
            border-color: #764ba2;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .option-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        .option-title {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .option-desc {
            color: #666;
            font-size: 0.9em;
            line-height: 1.5;
        }
        .admin-link {
            margin-top: 20px;
            color: #999;
        }
        .admin-link a {
            color: #999;
            text-decoration: none;
            font-size: 0.9em;
        }
        .admin-link a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="fablab-logo">🔧</div>
        
        <h1>Creative Spark</h1>
        <h1 style="color: #764ba2;">Enterprise FabLab</h1>
        
        <div class="subtitle">
            Digital fabrication, innovation, and community
        </div>
        
        <!-- BIG GET STARTED BUTTON -->
        <button onclick="showOptions()" class="get-started-btn">
            GET STARTED →
        </button>
        
        <!-- OPTIONS PANEL (Hidden until button clicked) -->
        <div id="optionsPanel" class="options-panel">
            <h2 style="color: #333; margin-bottom: 10px;">How would you like to continue?</h2>
            <p style="color: #666; margin-bottom: 30px;">Choose your path</p>
            
            <div class="option-grid">
                <!-- Option 1: New Member -->
                <a href="../signup/step1.php" class="option-card">
                    <div class="option-icon">🆕</div>
                    <div class="option-title">New Member</div>
                    <div class="option-desc">
                        Create a new membership account<br>
                        <strong>5-minute application</strong>
                    </div>
                </a>
                
                <!-- Option 2: Returning Member -->
                <a href="login.php" class="option-card">
                    <div class="option-icon">👤</div>
                    <div class="option-title">Returning Member</div>
                    <div class="option-desc">
                        Login to your dashboard<br>
                        <strong>View training status & profile</strong>
                    </div>
                </a>
                
                <!-- Option 3: Staff/Admin -->
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
        
        <div style="margin-top: 30px; color: #999; font-size: 0.8em;">
            ⚡ Dundalk, Co. Louth
        </div>
    </div>
    
    <script>
        console.log("✅ Page loaded successfully");
        
        function showOptions() {
            console.log("👉 Show options clicked");
            document.getElementById('optionsPanel').classList.add('show');
            document.getElementById('optionsPanel').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideOptions() {
            console.log("👈 Hide options clicked");
            document.getElementById('optionsPanel').classList.remove('show');
        }
        
        // Check if element exists
        window.onload = function() {
            var panel = document.getElementById('optionsPanel');
            if(panel) {
                console.log("✅ Options panel found");
            } else {
                console.log("❌ Options panel NOT found");
            }
        }
    </script>
</body>
</html>