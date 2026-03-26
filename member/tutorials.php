<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user_id = getCurrentUserId();
$user_name = getCurrentUserName();

// Get user data for personalization
// Use your existing database connection from config
require_once __DIR__ . '/../includes/config.php';
$conn = getDatabaseConnection();
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machine Tutorials - Creative Spark</title>
    <link rel="stylesheet" href="../css/tutorials.css">
    <style>
        /* Additional styles to ensure back button works and looks good */
        .header {
            position: relative;
            padding-top: 80px !important;
        }
        
        .header-top {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }
        
        .back-arrow {
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .back-text {
            font-weight: 500;
        }
        
        .logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Bottom navigation */
        .bottom-nav {
            text-align: center;
            margin-top: 40px;
            padding: 30px 0;
            border-top: 1px solid #ddd;
        }
        
        .bottom-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .bottom-back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .bottom-back-btn .back-arrow {
            font-size: 1.2em;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header {
                padding-top: 100px !important;
            }
            
            .header-top {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .back-button, .logout {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }

        /* Ensure filter buttons work */
        .machine-card {
            display: block;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header with prominent back button -->
    <div class="header">
    <div class="header-top">
        <a href="dashboard.php" class="back-button">
            <span class="back-arrow">←</span>
            <span class="back-text">Back to Dashboard</span>
        </a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
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
        
    <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
        <div class="machine-header">
            <h2>Prusa i3 MK3S+</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">3D Printer - FDM</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=GE-lrRbU124" target="_blank" class="tutorial-link video">Complete Beginner's Guide (15:22)</a>
            <div class="tutorial-placeholder">
                <p>⏳ PrusaSlicer Tutorial (12:45) – coming soon</p>
                <small>Check back for an updated walkthrough</small>
            </div>
            <div class="tutorial-placeholder">
                <p>⏳ Maintenance & Calibration (10:30) – coming soon</p>
                <small>We're preparing this guide</small>
            </div>
            <h3>📖 Written Guides</h3>
            <a href="https://help.prusa3d.com/en/category/prusa-i3-mk3s-mk3s_220" target="_blank" class="tutorial-link guide">Official Prusa Knowledge Base</a>
            <a href="https://help.prusa3d.com/article/first-print-with-prusaslicer_1753" target="_blank" class="tutorial-link guide">PrusaSlicer Settings Guide</a>
            <a href="https://help.prusa3d.com/product/mk3s-2/calibration_199" target="_blank" class="tutorial-link guide">3D Printing Tuning Guide</a>
        </div>
    </div>

    <!-- ULTIMAKER S3 EXTENDED -->
    <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
        <div class="machine-header">
            <h2>Ultimaker S3 Extended</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">3D Printer - FDM</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=m-MoI1FgjAY" target="_blank" class="tutorial-link video">Cura Tutorial for Beginners (8:21)</a>
            <div class="tutorial-placeholder">
                <p>⏳ First Print Setup (6:30) – coming soon</p>
                <small>We're working on this tutorial</small>
            </div>
            <h3>📖 Written Guides</h3>
            <a href="https://support.ultimaker.com/s/article/1667411295942" target="_blank" class="tutorial-link guide">Ultimaker S3 User Manual</a>
            <a href="https://ultimaker.com/software/ultimaker-cura" target="_blank" class="tutorial-link guide">Cura Software Documentation</a>
            <div class="tutorial-placeholder">
                <p>📝 Fab Academy 3D Printing Guide – coming soon</p>
                <small>Will be added once finalized</small>
            </div>
        </div>
    </div>

    <!-- MAKERBOT REPLICATOR+ -->
    <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
        <div class="machine-header">
            <h2>MakerBot Replicator+</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">3D Printer - FDM</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <div class="tutorial-placeholder">
                <p>⏳ Getting Started Guide (7:15) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <div class="tutorial-placeholder">
                <p>⏳ MakerBot Print Software (8:30) – coming soon</p>
                <small>Check back later</small>
            </div>
            <h3>📖 Written Guides</h3>
            <a href="https://support.makerbot.com/s/" target="_blank" class="tutorial-link guide">MakerBot Support Center</a>
            <div class="tutorial-placeholder">
                <p>📝 General FDM Printing Guide – coming soon</p>
                <small>We're working on this resource</small>
            </div>
        </div>
    </div>

    <!-- FORMLABS FORM 3B+ -->
    <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
        <div class="machine-header">
            <h2>Formlabs Form 3B+</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">3D Printer - SLA</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <div class="tutorial-placeholder">
                <p>⏳ Setup & First Print (8:30) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <div class="tutorial-placeholder">
                <p>⏳ PreForm Software Tutorial (10:15) – coming soon</p>
                <small>Check back later</small>
            </div>
            <div class="tutorial-placeholder">
                <p>⏳ Post-Processing Guide (7:45) – coming soon</p>
                <small>We're working on this</small>
            </div>
            <h3>📖 Written Guides</h3>
            <a href="https://formlabs.com/blog/ultimate-guide-to-stereolithography-sla-3d-printing/" target="_blank" class="tutorial-link guide">Complete SLA Printing Guide</a>
            <div class="tutorial-placeholder">
                <p>📝 PreForm Software Manual – coming soon</p>
                <small>Will be added once available</small>
            </div>
            <a href="https://formlabs.com/services/training/form-3/" target="_blank" class="tutorial-link guide">Formlabs Training Hub</a>
        </div>
    </div>

    <!-- EPILOG ZING 16-40W -->
    <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Laser Cutter">
        <div class="machine-header">
            <h2>Epilog Zing 16-40W</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Laser Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <div class="tutorial-placeholder">
                <p>⏳ Beginner's Tutorial (14:30) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <a href="https://www.youtube.com/watch?v=z7kE8W4eC8E" target="_blank" class="tutorial-link video">Laser Cutting Basics (21:36)</a>
            <h3>📖 Written Guides</h3>
            <div class="tutorial-placeholder">
                <p>📝 Epilog Laser Tutorials – coming soon</p>
                <small>Will be added once available</small>
            </div>
            <div class="tutorial-placeholder">
                <p>📝 Laser Projects Guide – coming soon</p>
                <small>We're working on this</small>
            </div>
            <div class="tutorial-placeholder">
                <p>📝 Material Settings Database – coming soon</p>
                <small>Check back later</small>
            </div>
        </div>
    </div>

    <!-- FLUX BEAMO / BEAMBOX -->
    <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Laser Cutter">
        <div class="machine-header">
            <h2>Flux Beamo / Beambox</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Laser Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=uP7-k1ReEGQ" target="_blank" class="tutorial-link video">Beginner Tutorial (12:00)</a>
            <h3>📖 Written Guides</h3>
            <a href="https://support.flux3dp.com/hc/en-us" target="_blank" class="tutorial-link guide">Flux Support Center</a>
            <div class="tutorial-placeholder">
                <p>📝 General Laser Cutting Guide – coming soon</p>
                <small>We're working on this resource</small>
            </div>
        </div>
    </div>

    <!-- WAZER DESKTOP WATERJET -->
    <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Waterjet">
        <div class="machine-header">
            <h2>Wazer Desktop Waterjet</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Waterjet Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <div class="tutorial-placeholder">
                <p>⏳ Desktop Waterjet Tutorial (10:30) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <h3>📖 Written Guides</h3>
            <a href="https://www.wazer.com/support" target="_blank" class="tutorial-link guide">Wazer Support Center</a>
        </div>
    </div>

    <!-- ROLAND MONOFAB SRM-20 -->
    <div class="machine-card fabber1 cnc" data-tier="Fabber 1" data-category="CNC">
        <div class="machine-header">
            <h2>Roland Monofab SRM-20</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">CNC Milling - High Definition</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <div class="tutorial-placeholder">
                <p>⏳ Milling Tutorial (13:45) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <div class="tutorial-placeholder">
                <p>⏳ SRP Player Software Guide (8:20) – coming soon</p>
                <small>Check back later</small>
            </div>
            <h3>📖 Written Guides</h3>
            <a href="https://support.rolanddga.com/hc/en-us/categories/204207187-SRM-20" target="_blank" class="tutorial-link guide">Roland SRM-20 Manuals</a>
            <div class="tutorial-placeholder">
                <p>📝 Feeds & Speeds Explained – coming soon</p>
                <small>We're working on this guide</small>
            </div>
        </div>
    </div>

    <!-- ROLAND CAMM1 GS2-24 -->
    <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Vinyl">
        <div class="machine-header">
            <h2>Roland CAMM1 GS2-24</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Vinyl Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=vU1IA0sr_v0" target="_blank" class="tutorial-link video">Vinyl Cutter Tutorial (9:30)</a>
            <div class="tutorial-placeholder">
                <p>⏳ Vinyl Cutting for Beginners (4:08) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <h3>📖 Written Guides</h3>
            <div class="tutorial-placeholder">
                <p>📝 Complete Vinyl Cutting Guide – coming soon</p>
                <small>Will be added once available</small>
            </div>
        </div>
    </div>

    <!-- ROLAND VERSA STUDIO BN-20 -->
    <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Vinyl">
        <div class="machine-header">
            <h2>Roland Versa Studio BN-20</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Vinyl Cutting & Printing</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=QD6Fwgq-7BU" target="_blank" class="tutorial-link video">BN-20 Getting Started (11:20)</a>
            <h3>📖 Written Guides</h3>
            <a href="https://www.rolanddga.com/support" target="_blank" class="tutorial-link guide">Roland Support Center</a>
        </div>
    </div>

    <!-- BROTHER XJ1 / VR EMBROIDERY -->
    <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Embroidery">
        <div class="machine-header">
            <h2>Brother XJ1 / VR Embroidery</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Sewing & Embroidery</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=0ZdHzlwVkdA" target="_blank" class="tutorial-link video">Embroidery Machine Tutorial (15:00)</a>
            <h3>📖 Written Guides</h3>
            <a href="https://support.brother.com/g/b/productseries.aspx?c=us&lang=en&pcatid=43" target="_blank" class="tutorial-link guide">Brother Support</a>
        </div>
    </div>

    <!-- MAYKU FORMBOX -->
    <div class="machine-card fabber1" data-tier="Fabber 1" data-category="Thermoforming">
        <div class="machine-header">
            <h2>Mayku Formbox</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Thermoforming</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <div class="tutorial-placeholder">
                <p>⏳ Thermoforming Guide (6:45) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <h3>📖 Written Guides</h3>
            <div class="tutorial-placeholder">
                <p>📝 Mayku Learning Center – coming soon</p>
                <small>Will be added once available</small>
            </div>
        </div>
    </div>

    <!-- SOLDERING & POWERTOOLS -->
    <div class="machine-card fabber1" data-tier="Fabber 1" data-category="Workbench">
        <div class="machine-header">
            <h2>Soldering & Powertools</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Workbench Equipment</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=uTloBPjC-uA" target="_blank" class="tutorial-link video">Soldering Basics (12:00)</a>
            <div class="tutorial-placeholder">
                <p>⏳ Power Tool Safety (8:30) – coming soon</p>
                <small>We're preparing this video</small>
            </div>
            <h3>📖 Written Guides</h3>
            <div class="tutorial-placeholder">
                <p>📝 Soldering & Electronics Guide – coming soon</p>
                <small>Will be added once available</small>
            </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>Check back later for Form 3L setup guides</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Visit <a href="https://formlabs.com/training/" target="_blank" rel="noopener noreferrer">Formlabs Training Hub</a> for official resources</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>SLS printing guides in production</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Learn more about <a href="https://formlabs.com/uk/blog/sls-vs-sla-vs-fdm/" target="_blank" rel="noopener noreferrer">SLS vs SLA vs FDM</a> on Formlabs</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>Setup and maintenance guides on the way</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Check <a href="https://support.ultimaker.com/s/article/1667416763353" target="_blank" rel="noopener noreferrer">Ultimaker S5 Manual</a> for official documentation</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>Advanced laser cutting guides in production</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Visit <a href="https://www.epiloglaser.com/tech-support/laser-tutorials.htm" target="_blank" rel="noopener noreferrer">Epilog Laser Tutorials</a> for official resources</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>Beginner to advanced CNC guides on the way</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Visit <a href="https://www.shopbottools.com/support" target="_blank" rel="noopener noreferrer">Shopbot Support Center</a> for official documentation</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>High-definition milling guides in production</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Visit <a href="https://www.rolanddga.com/support" target="_blank" rel="noopener noreferrer">Roland Support</a> for official resources</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>Artec Leo training guides on the way</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Visit <a href="https://www.artec3d.com/academy" target="_blank" rel="noopener noreferrer">Artec 3D Academy</a> for official courses</small>
        </div>
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
        <div class="tutorial-placeholder">
            <p>⏳ Video tutorials coming soon</p>
            <small>Clay 3D printing guides in production</small>
        </div>
        
        <h3>📖 Written Guides</h3>
        <div class="tutorial-placeholder">
            <p>📝 Written guides in progress</p>
            <small>Visit <a href="https://www.3dwasp.com/support/" target="_blank" rel="noopener noreferrer">WASP Support Center</a> for official documentation</small>
        </div>
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
    
    <!-- Bottom navigation -->
    <div class="bottom-nav">
        <a href="member-dashboard.php" class="bottom-back-btn">
            <span class="back-arrow">←</span> Return to Dashboard
        </a>
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