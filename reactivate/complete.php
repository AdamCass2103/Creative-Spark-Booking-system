<?php
session_start();
require_once '../includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivation Complete</title>
    <link rel="stylesheet" href="../css/signup.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success-icon {
            font-size: 5em;
            margin-bottom: 20px;
            animation: bounce 1s ease;
        }
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        h1 {
            color: #2E7D32;
            margin-bottom: 20px;
        }
        .btn-dashboard {
            background: linear-gradient(135deg, #2E7D32, #1B5E20);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 30px;
            display: inline-block;
            text-decoration: none;
        }
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(46, 125, 50, 0.4);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">🎉</div>
            <h1>Welcome Back!</h1>
            <p style="color: #666; font-size: 1.2em; margin-bottom: 30px;">
                Your membership has been successfully reactivated.
            </p>
            <p style="color: #999; margin-bottom: 30px;">
                You now have full access to all member features.
            </p>
            <a href="../member/dashboard.php" class="btn-dashboard">
                Go to Your Dashboard →
            </a>
        </div>
    </div>
</body>
</html>