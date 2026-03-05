<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();

// Get user data for personalization
$conn = new mysqli('localhost', 'root', '', 'booking_system');
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machine Tutorials - Creative Spark</title>
    <link rel="stylesheet" href="../css/tutorials.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="logout.php" class="logout">Logout</a>
            <a href="member-dashboard.php" class="back-btn">← Back to Dashboard</a>
            <h1>📚 Machine Tutorials Library</h1>
            <p class="welcome">Comprehensive guides for all FabLab machines</p>
        </div>

        <!-- Quick Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-btn active" onclick="filterTutorials('all')">All Machines</button>
            <button class="filter-btn" onclick="filterTutorials('fabber1')">Fabber 1</button>
            <button class="filter-btn" onclick="filterTutorials('fabber2')">Fabber 2</button>
            <button class="filter-btn" onclick="filterTutorials('3d')">3D Printers</button>
            <button class="filter-btn" onclick="filterTutorials('laser')">Laser & Water Cutters</button>
            <button class="filter-btn" onclick="filterTutorials('cnc')">CNC Milling</button>
            <button class="filter-btn" onclick="filterTutorials('vinyl')">Fabric & Vinyl</button>
        </div>

        <!-- Tutorials Grid -->
        <div class="tutorials-grid">
            
            <!-- FABBER 1 MACHINES -->
            
            <!-- 3D Printers - FDM (Fabber 1) -->
            <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Prusa i3 MK3S+</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">3D Printer - FDM</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=jZp7hNcE0Zk" target="_blank" class="tutorial-link video">Complete Beginner's Guide (15:22)</a>
                    <a href="https://www.youtube.com/watch?v=w_PrusaSlicer" target="_blank" class="tutorial-link video">PrusaSlicer Tutorial (12:45)</a>
                    <a href="https://www.youtube.com/watch?v=PrusaMaintenance" target="_blank" class="tutorial-link video">Maintenance & Calibration (10:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://help.prusa3d.com/en/category/prusa-i3-mk3s-mk3s_220" target="_blank" class="tutorial-link guide">Official Prusa Knowledge Base</a>
                    <a href="https://help.prusa3d.com/en/guide/3-print-settings-prusaslicer" target="_blank" class="tutorial-link guide">PrusaSlicer Settings Guide</a>
                    <a href="https://pub.fabcloud.io/tutorials/04_3d_printing_scanning/3d_printing_tuning" target="_blank" class="tutorial-link guide">3D Printing Tuning Guide</a>
                </div>
            </div>

            <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Ultimaker S3 Extended</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">3D Printer - FDM</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=_E8Gz2_7ZNM" target="_blank" class="tutorial-link video">Cura Tutorial for Beginners (8:21)</a>
                    <a href="https://www.youtube.com/watch?v=UltimakerS3" target="_blank" class="tutorial-link video">First Print Setup (6:30)</a>
                    <a href="https://www.youtube.com/watch?v=UltimakerMaintenance" target="_blank" class="tutorial-link video">Maintenance Guide (9:15)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://support.ultimaker.com/s/article/1667416763353" target="_blank" class="tutorial-link guide">Ultimaker S3 User Manual</a>
                    <a href="https://support.ultimaker.com/s/topic" target="_blank" class="tutorial-link guide">Cura Software Documentation</a>
                    <a href="https://pub.fabcloud.io/tutorials/04_3d_printing_scanning" target="_blank" class="tutorial-link guide">Fab Academy 3D Printing Guide</a>
                </div>
            </div>

            <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
                <div class="machine-header">
                    <h2>MakerBot Replicator+</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">3D Printer - FDM</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=MakerBotSetup" target="_blank" class="tutorial-link video">Getting Started Guide (7:15)</a>
                    <a href="https://www.youtube.com/watch?v=MakerBotPrint" target="_blank" class="tutorial-link video">MakerBot Print Software (8:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://support.makerbot.com/" target="_blank" class="tutorial-link guide">MakerBot Support Center</a>
                    <a href="https://pub.fabcloud.io/tutorials/04_3d_printing_scanning" target="_blank" class="tutorial-link guide">General FDM Printing Guide</a>
                </div>
            </div>

            <!-- SLA 3D Printers (Fabber 1) -->
            <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Formlabs Form 3B+</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">3D Printer - SLA</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=QehUfBqKsoQ" target="_blank" class="tutorial-link video">Setup & First Print (8:30)</a>
                    <a href="https://www.youtube.com/watch?v=PreForm" target="_blank" class="tutorial-link video">PreForm Software Tutorial (10:15)</a>
                    <a href="https://www.youtube.com/watch?v=PostProcess" target="_blank" class="tutorial-link video">Post-Processing Guide (7:45)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://formlabs.com/uk/blog/ultimate-guide-to-stereolithography-sla-3d-printing/" target="_blank" class="tutorial-link guide">Complete SLA Printing Guide</a>
                    <a href="https://support.formlabs.com/s/article/Using-PreForm" target="_blank" class="tutorial-link guide">PreForm Software Manual</a>
                    <a href="https://formlabs.com/training/" target="_blank" class="tutorial-link guide">Formlabs Training Hub</a>
                </div>
            </div>

            <!-- Laser Cutters (Fabber 1) -->
            <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Laser Cutter">
                <div class="machine-header">
                    <h2>Epilog Zing 16-40W</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Laser Cutter</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=EpilogBeginner" target="_blank" class="tutorial-link video">Beginner's Tutorial (14:30)</a>
                    <a href="https://learn.microsoft.com/en-us/shows/themakershow/mini-laser-cutting" target="_blank" class="tutorial-link video">Laser Cutting Basics (21:36)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.epiloglaser.com/tech-support/laser-tutorials.htm" target="_blank" class="tutorial-link guide">Epilog Laser Tutorials</a>
                    <a href="https://pub.fabcloud.io/tutorials/01_laser_cutter/laser_caster" target="_blank" class="tutorial-link guide">Laser Caster Project Guide</a>
                    <a href="https://www.epiloglaser.com/tech-support/laser-material-settings/" target="_blank" class="tutorial-link guide">Material Settings Database</a>
                </div>
            </div>

            <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Laser Cutter">
                <div class="machine-header">
                    <h2>Flux Beamo / Beambox</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Laser Cutter</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=FluxBeambox" target="_blank" class="tutorial-link video">Beginner Tutorial (12:00)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://support.flux3dp.com/" target="_blank" class="tutorial-link guide">Flux Support Center</a>
                    <a href="https://pub.fabcloud.io/tutorials/01_laser_cutter" target="_blank" class="tutorial-link guide">General Laser Cutting Guide</a>
                </div>
            </div>

            <!-- Waterjet (Fabber 1) -->
            <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Waterjet">
                <div class="machine-header">
                    <h2>Wazer Desktop Waterjet</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Waterjet Cutter</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=WazerTutorial" target="_blank" class="tutorial-link video">Desktop Waterjet Tutorial (10:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.wazer.com/support" target="_blank" class="tutorial-link guide">Wazer Support Center</a>
                </div>
            </div>

            <!-- CNC Milling (Fabber 1) -->
            <div class="machine-card fabber1 cnc" data-tier="Fabber 1" data-category="CNC">
                <div class="machine-header">
                    <h2>Roland Monofab SRM-20</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">CNC Milling - High Definition</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=RolandSRM20" target="_blank" class="tutorial-link video">Milling Tutorial (13:45)</a>
                    <a href="https://www.youtube.com/watch?v=SRPPlayer" target="_blank" class="tutorial-link video">SRP Player Software Guide (8:20)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://pub.fabcloud.io/tutorials/05_cnc_milling" target="_blank" class="tutorial-link guide">Fab Academy CNC Guide</a>
                    <a href="https://www.youtube.com/watch?v=CNCFeedsSpeeds" target="_blank" class="tutorial-link guide">Feeds & Speeds Explained</a>
                </div>
            </div>

            <!-- Vinyl Cutters (Fabber 1) -->
            <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Vinyl">
                <div class="machine-header">
                    <h2>Roland CAMM1 GS2-24</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Vinyl Cutter</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=RolandVinyl" target="_blank" class="tutorial-link video">Vinyl Cutter Tutorial (9:30)</a>
                    <a href="https://www.youtube.com/watch?v=VinylBeginner" target="_blank" class="tutorial-link video">Vinyl Cutting for Beginners (4:08)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://pub.fabcloud.io/tutorials/02_vinyl_cutting/vinyl_cutting" target="_blank" class="tutorial-link guide">Complete Vinyl Cutting Guide</a>
                </div>
            </div>

            <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Vinyl">
                <div class="machine-header">
                    <h2>Roland Versa Studio BN-20</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Vinyl Cutting & Printing</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=RolandBN20" target="_blank" class="tutorial-link video">BN-20 Getting Started (11:20)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.rolanddga.com/support" target="_blank" class="tutorial-link guide">Roland Support Center</a>
                </div>
            </div>

            <!-- Embroidery Machines (Fabber 1) -->
            <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Embroidery">
                <div class="machine-header">
                    <h2>Brother XJ1 / VR Embroidery</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Sewing & Embroidery</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=BrotherEmbroidery" target="_blank" class="tutorial-link video">Embroidery Machine Tutorial (15:00)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://support.brother.com/" target="_blank" class="tutorial-link guide">Brother Support</a>
                </div>
            </div>

            <!-- Thermoforming (Fabber 1) -->
            <div class="machine-card fabber1" data-tier="Fabber 1" data-category="Thermoforming">
                <div class="machine-header">
                    <h2>Mayku Formbox</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Thermoforming</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=MaykuFormbox" target="_blank" class="tutorial-link video">Thermoforming Guide (6:45)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.mayku.com/pages/learn" target="_blank" class="tutorial-link guide">Mayku Learning Center</a>
                </div>
            </div>

            <!-- Workbench (Fabber 1) -->
            <div class="machine-card fabber1" data-tier="Fabber 1" data-category="Workbench">
                <div class="machine-header">
                    <h2>Soldering & Powertools</h2>
                    <span class="tier-badge fabber1">Fabber 1</span>
                </div>
                <p class="machine-type">Workbench Equipment</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=SolderingBasics" target="_blank" class="tutorial-link video">Soldering Basics (12:00)</a>
                    <a href="https://www.youtube.com/watch?v=PowertoolsSafety" target="_blank" class="tutorial-link video">Power Tool Safety (8:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://pub.fabcloud.io/tutorials/08_electronics_production" target="_blank" class="tutorial-link guide">Electronics Production Guide</a>
                </div>
            </div>

            <!-- FABBER 2 MACHINES -->
            
            <!-- Raise 3D Printers (Fabber 2) -->
            <div class="machine-card fabber2 3d" data-tier="Fabber 2" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Raise Pro3 Plus (x2)</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">3D Printer - FDM (Industrial)</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=Raise3DPro3" target="_blank" class="tutorial-link video">Pro3 Plus Setup Guide (14:20)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://support.raise3d.com/" target="_blank" class="tutorial-link guide">Raise3D Support Center</a>
                    <a href="https://pub.fabcloud.io/tutorials/04_3d_printing_scanning" target="_blank" class="tutorial-link guide">Advanced FDM Guide</a>
                </div>
            </div>

            <!-- Formlabs Fabber 2 -->
            <div class="machine-card fabber2 3d" data-tier="Fabber 2" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Formlabs Form 3L</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">3D Printer - SLA (Large Format)</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=Form3L" target="_blank" class="tutorial-link video">Form 3L Setup (9:45)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://formlabs.com/training/" target="_blank" class="tutorial-link guide">Formlabs Training Hub</a>
                </div>
            </div>

            <div class="machine-card fabber2 3d" data-tier="Fabber 2" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Formlabs Fuse 1</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">3D Printer - SLS</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=Fuse1SLS" target="_blank" class="tutorial-link video">SLS Printing Introduction (11:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://formlabs.com/uk/blog/sls-vs-sla-vs-fdm/" target="_blank" class="tutorial-link guide">SLS vs SLA vs FDM Guide</a>
                </div>
            </div>

            <!-- Ultimaker S5 -->
            <div class="machine-card fabber2 3d" data-tier="Fabber 2" data-category="3D Printer">
                <div class="machine-header">
                    <h2>Ultimaker S5</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">3D Printer - FDM</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=UltimakerS5" target="_blank" class="tutorial-link video">S5 Complete Guide (16:40)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://support.ultimaker.com/s/article/1667416763353" target="_blank" class="tutorial-link guide">Ultimaker S5 Manual</a>
                </div>
            </div>

            <!-- Epilog Fusion Pro -->
            <div class="machine-card fabber2 laser" data-tier="Fabber 2" data-category="Laser Cutter">
                <div class="machine-header">
                    <h2>Epilog Fusion Pro 120W</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">Laser Cutter - Industrial</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=EpilogFusion" target="_blank" class="tutorial-link video">Fusion Pro Advanced Guide (18:20)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.epiloglaser.com/tech-support/laser-tutorials.htm" target="_blank" class="tutorial-link guide">Epilog Laser Tutorials</a>
                </div>
            </div>

            <!-- Shopbot CNC -->
            <div class="machine-card fabber2 cnc" data-tier="Fabber 2" data-category="CNC">
                <div class="machine-header">
                    <h2>Shopbot PRSalpha</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">CNC Milling - Large Format</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=ShopbotBeginner" target="_blank" class="tutorial-link video">Shopbot Beginner Tutorial (18:00)</a>
                    <a href="https://www.youtube.com/watch?v=VCarve" target="_blank" class="tutorial-link video">VCarve Toolpaths (14:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.shopbottools.com/support" target="_blank" class="tutorial-link guide">Shopbot Support Center</a>
                    <a href="https://pub.fabcloud.io/tutorials/05_cnc_milling" target="_blank" class="tutorial-link guide">Advanced CNC Guide</a>
                </div>
            </div>

            <!-- Roland MDX-50 -->
            <div class="machine-card fabber2 cnc" data-tier="Fabber 2" data-category="CNC">
                <div class="machine-header">
                    <h2>Roland Monofab MDX-50</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">CNC Milling - High Definition</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=RolandMDX50" target="_blank" class="tutorial-link video">MDX-50 Advanced Milling (15:10)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.rolanddga.com/support" target="_blank" class="tutorial-link guide">Roland Support</a>
                </div>
            </div>

            <!-- 3D Scanning -->
            <div class="machine-card fabber2" data-tier="Fabber 2" data-category="Scanning">
                <div class="machine-header">
                    <h2>Artec Leo 3D Scanner</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">3D Scanning</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=ArtecLeo" target="_blank" class="tutorial-link video">Artec Leo Basic Training (12:30)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.artec3d.com/academy" target="_blank" class="tutorial-link guide">Artec 3D Academy</a>
                    <a href="https://www.artec3d.com/academy/group/75?type=catalog" target="_blank" class="tutorial-link guide">Complete Leo Course</a>
                </div>
            </div>

            <!-- Clay Printer -->
            <div class="machine-card fabber2 3d" data-tier="Fabber 2" data-category="3D Printer">
                <div class="machine-header">
                    <h2>WASP 40100 Clay Printer</h2>
                    <span class="tier-badge fabber2">Fabber 2</span>
                </div>
                <p class="machine-type">Clay 3D Printer</p>
                <div class="tutorial-links">
                    <h3>📹 Video Tutorials</h3>
                    <a href="https://www.youtube.com/watch?v=WASPClay" target="_blank" class="tutorial-link video">Clay 3D Printing Tutorial (14:00)</a>
                    
                    <h3>📖 Written Guides</h3>
                    <a href="https://www.3dwasp.com/support/" target="_blank" class="tutorial-link guide">WASP Support Center</a>
                </div>
            </div>

        </div>

        <!-- Quick Resource Links -->
        <div class="quick-resources">
            <h2>🔗 Quick Resource Links</h2>
            <div class="resources-grid">
                <a href="https://pub.fabcloud.io/tutorials/" target="_blank" class="resource-link">📚 Fab Academy Tutorials</a>
                <a href="https://formlabs.com/training/" target="_blank" class="resource-link">🎓 Formlabs Training Hub</a>
                <a href="https://help.prusa3d.com/" target="_blank" class="resource-link">📖 Prusa Knowledge Base</a>
                <a href="https://www.epiloglaser.com/tech-support/laser-tutorials.htm" target="_blank" class="resource-link">🔧 Epilog Laser Tutorials</a>
                <a href="https://www.youtube.com/@NYCCNC" target="_blank" class="resource-link">🎥 NYCCNC YouTube Channel</a>
                <a href="https://www.artec3d.com/academy" target="_blank" class="resource-link">📐 Artec 3D Academy</a>
            </div>
        </div>
    </div>

    <script>
        function filterTutorials(filter) {
            const cards = document.querySelectorAll('.machine-card');
            
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else if (filter === 'fabber1') {
                    card.style.display = card.classList.contains('fabber1') ? 'block' : 'none';
                } else if (filter === 'fabber2') {
                    card.style.display = card.classList.contains('fabber2') ? 'block' : 'none';
                } else if (filter === '3d') {
                    card.style.display = card.classList.contains('3d') ? 'block' : 'none';
                } else if (filter === 'laser') {
                    card.style.display = card.classList.contains('laser') ? 'block' : 'none';
                } else if (filter === 'cnc') {
                    card.style.display = card.classList.contains('cnc') ? 'block' : 'none';
                } else if (filter === 'vinyl') {
                    card.style.display = card.classList.contains('vinyl') ? 'block' : 'none';
                }
            });

            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }
    </script>
</body>
</html>