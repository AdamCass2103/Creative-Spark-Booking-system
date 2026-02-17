<?php
session_start();
require_once '../includes/db_connect.php';

$error = '';

// Simple hardcoded admin password (Oscar can change it)
define('ADMIN_PASSWORD', 'fablab2026'); // Change this!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit();
    } else {
        $error = 'Invalid admin code';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Access - FabLab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            max-width: 400px; 
            width: 90%;
            margin: 20px; 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px;
            text-align: center;
        }
        .sub {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 0.9em;
        }
        .form-group { margin-bottom: 20px; }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
            color: #555;
        }
        input[type="password"] { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #eee; 
            border-radius: 6px; 
            font-size: 16px;
        }
        input[type="password"]:focus {
            border-color: #764ba2;
            outline: none;
        }
        .btn { 
            width: 100%; 
            padding: 12px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .error { 
            background: #fee; 
            color: #c00; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 0.9em;
        }
        .back-link a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👨‍💼 Staff Access</h1>
        <div class="sub">For Oscar and FabLab team only</div>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Admin Code</label>
                <input type="password" name="password" required placeholder="Enter admin code">
            </div>
            
            <button type="submit" class="btn">Access Admin Panel</button>
        </form>
        
        <div class="back-link">
            <a href="../public/index.php">← Back to main page</a>
        </div>
    </div>
</body>
</html>