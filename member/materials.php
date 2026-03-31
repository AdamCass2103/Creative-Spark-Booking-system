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
    <link rel="stylesheet" href="../css/materials.css">
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