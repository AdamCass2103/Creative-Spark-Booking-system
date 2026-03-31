<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials Hub</title>
    <link rel="stylesheet" href="../css/member-dashboard.css">

    <style>
        .materials-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .materials-header {
            background: #2E7D32;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .category {
            margin-bottom: 25px;
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .category h2 {
            margin: 0 0 10px 0;
            color: #2E7D32;
        }

        .material-item {
            border-top: 1px solid #eee;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .material-name {
            font-weight: bold;
        }

        .badge-approved {
            background: #4caf50;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .badge-fablab {
            background: #ff9800;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 5px;
        }

        .material-info {
            font-size: 13px;
            color: #666;
            width: 100%;
            margin-top: 5px;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #607d8b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<div class="materials-container">

    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <div class="materials-header">
        <h1>📦 Materials Hub</h1>
        <p>Approved materials and trusted suppliers for FabLab use only.</p>
    </div>

    <!-- WOOD -->
    <div class="category">
        <h2>🪵 Wood</h2>

        <div class="material-item">
            <span class="material-name">Birch Plywood</span>
            <div>
                <span class="badge-approved">Approved</span>
                <span class="badge-fablab">FabLab Stock</span>
            </div>
            <div class="material-info">
                Buy: McMahons / Chadwicks / FabLab Store
            </div>
        </div>
    </div>

    <!-- METAL -->
    <div class="category">
        <h2>🔩 Metal</h2>

        <div class="material-item">
            <span class="material-name">Aluminium Sheet</span>
            <div>
                <span class="badge-approved">Approved</span>
            </div>
            <div class="material-info">
                Buy: Metal Suppliers (approved list only)
            </div>
        </div>
    </div>

    <!-- PLASTIC -->
    <div class="category">
        <h2>🧪 Plastic</h2>

        <div class="material-item">
            <span class="material-name">PLA Filament</span>
            <div>
                <span class="badge-approved">Approved</span>
                <span class="badge-fablab">FabLab Stock</span>
            </div>
            <div class="material-info">
                Use FabLab 3D printers only
            </div>
        </div>
    </div>

    <!-- ELECTRONICS -->
    <div class="category">
        <h2>⚡ Electronics</h2>

        <div class="material-item">
            <span class="material-name">Arduino Kit Components</span>
            <div>
                <span class="badge-approved">Approved</span>
                <span class="badge-fablab">FabLab Stock</span>
            </div>
            <div class="material-info">
                Available in FabLab only
            </div>
        </div>
    </div>

</div>

</body>
</html>