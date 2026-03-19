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
            <a href="https://www.youtube.com/watch?v=3S_0H0fI-Rk" target="_blank" class="tutorial-link video">PrusaSlicer Tutorial (12:45)</a>
            <a href="https://www.youtube.com/watch?v=z0z8D-E5r8M" target="_blank" class="tutorial-link video">Maintenance & Calibration (10:30)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://help.prusa3d.com/en/category/prusa-i3-mk3s-mk3s_220" target="_blank" class="tutorial-link guide">Official Prusa Knowledge Base</a>
            <a href="https://help.prusa3d.com/article/first-print-with-prusaslicer_1753" target="_blank" class="tutorial-link guide">PrusaSlicer Settings Guide</a>
            <a href="https://help.prusa3d.com/product/mk3s-2/calibration_199" target="_blank" class="tutorial-link guide">3D Printing Tuning Guide</a>
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
            <a href="https://www.youtube.com/watch?v=m-MoI1FgjAY" target="_blank" class="tutorial-link video">Cura Tutorial for Beginners (8:21)</a>
            <a href="https://www.youtube.com/watch?v=4UaH_vFm0_Y" target="_blank" class="tutorial-link video">First Print Setup (6:30)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://support.ultimaker.com/s/article/1667411295942" target="_blank" class="tutorial-link guide">Ultimaker S3 User Manual</a>
            <a href="https://ultimaker.com/software/ultimaker-cura" target="_blank" class="tutorial-link guide">Cura Software Documentation</a>
            <a href="https://support.ultimaker.com/s/topic/0TO5b000000U6fMGAS/3d-printing-basics" target="_blank" class="tutorial-link guide">Ultimate 3D Printing Guide</a>
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
            <a href="https://www.youtube.com/watch?v=pSAnpAnC_xQ" target="_blank" class="tutorial-link video">Getting Started Guide (7:15)</a>
            <a href="https://www.youtube.com/watch?v=R4w7Y-y_x2c" target="_blank" class="tutorial-link video">MakerBot Print Software (8:30)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://support.makerbot.com/s/" target="_blank" class="tutorial-link guide">MakerBot Support Center</a>
            <a href="https://support.makerbot.com/s/article/MakerBot-Replicator-Plus-User-Manual" target="_blank" class="tutorial-link guide">General FDM Printing Guide</a>
        </div>
    </div>

    <div class="machine-card fabber1 3d" data-tier="Fabber 1" data-category="3D Printer">
        <div class="machine-header">
            <h2>Formlabs Form 3B+</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">3D Printer - SLA</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=QehUfBqKsoQ" target="_blank" class="tutorial-link video">Setup & First Print (8:30)</a>
            <a href="https://www.youtube.com/watch?v=vV77m6_pL7Q" target="_blank" class="tutorial-link video">PreForm Software Tutorial (10:15)</a>
            <a href="https://www.youtube.com/watch?v=5G8u6Gatb9s" target="_blank" class="tutorial-link video">Post-Processing Guide (7:45)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://formlabs.com/blog/ultimate-guide-to-stereolithography-sla-3d-printing/" target="_blank" class="tutorial-link guide">Complete SLA Printing Guide</a>
            <a href="https://support.formlabs.com/s/article/Using-PreForm" target="_blank" class="tutorial-link guide">PreForm Software Manual</a>
            <a href="https://formlabs.com/services/training/form-3/" target="_blank" class="tutorial-link guide">Formlabs Training Hub</a>
        </div>
    </div>

    <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Laser Cutter">
        <div class="machine-header">
            <h2>Epilog Zing 16-40W</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Laser Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=q6eY0Vp34is" target="_blank" class="tutorial-link video">Beginner's Tutorial (14:30)</a>
            <a href="https://www.youtube.com/watch?v=z7kE8W4eC8E" target="_blank" class="tutorial-link video">Laser Cutting Basics (21:36)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://www.epiloglaser.com/tech-support/zing-manual.htm" target="_blank" class="tutorial-link guide">Epilog Laser Tutorials</a>
            <a href="https://www.epiloglaser.com/resources/sample-club.htm" target="_blank" class="tutorial-link guide">Laser Projects Guide</a>
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
            <a href="https://www.youtube.com/watch?v=uP7-k1ReEGQ" target="_blank" class="tutorial-link video">Beginner Tutorial (12:00)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://support.flux3dp.com/hc/en-us" target="_blank" class="tutorial-link guide">Flux Support Center</a>
            <a href="https://support.flux3dp.com/hc/en-us/articles/360001550136-Beam-Studio-Manual" target="_blank" class="tutorial-link guide">General Laser Cutting Guide</a>
        </div>
    </div>

    <div class="machine-card fabber1 laser" data-tier="Fabber 1" data-category="Waterjet">
        <div class="machine-header">
            <h2>Wazer Desktop Waterjet</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Waterjet Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=o7uLgTIdD0Q" target="_blank" class="tutorial-link video">Desktop Waterjet Tutorial (10:30)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://www.wazer.com/support" target="_blank" class="tutorial-link guide">Wazer Support Center</a>
        </div>
    </div>

    <div class="machine-card fabber1 cnc" data-tier="Fabber 1" data-category="CNC">
        <div class="machine-header">
            <h2>Roland Monofab SRM-20</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">CNC Milling - High Definition</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=j_Kz58-H_00" target="_blank" class="tutorial-link video">Milling Tutorial (13:45)</a>
            <a href="https://www.youtube.com/watch?v=o0L6m_u-HMo" target="_blank" class="tutorial-link video">SRP Player Software Guide (8:20)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://support.rolanddga.com/hc/en-us/categories/204207187-SRM-20" target="_blank" class="tutorial-link guide">Roland SRM-20 Manuals</a>
            <a href="https://www.youtube.com/watch?v=CNCFeedsSpeeds" target="_blank" class="tutorial-link guide">Feeds & Speeds Explained</a>
        </div>
    </div>

    <div class="machine-card fabber1 vinyl" data-tier="Fabber 1" data-category="Vinyl">
        <div class="machine-header">
            <h2>Roland CAMM1 GS2-24</h2>
            <span class="tier-badge fabber1">Fabber 1</span>
        </div>
        <p class="machine-type">Vinyl Cutter</p>
        <div class="tutorial-links">
            <h3>📹 Video Tutorials</h3>
            <a href="https://www.youtube.com/watch?v=vU1IA0sr_v0" target="_blank" class="tutorial-link video">Vinyl Cutter Tutorial (9:30)</a>
            <a href="https://www.youtube.com/watch?v=T1D-5Vv2Uio" target="_blank" class="tutorial-link video">Vinyl Cutting for Beginners (4:08)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://support.rolanddga.com/hc/en-us/categories/204207167-GS-24" target="_blank" class="tutorial-link guide">Complete Vinyl Cutting Guide</a>
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
            <a href="https://www.youtube.com/watch?v=QD6Fwgq-7BU" target="_blank" class="tutorial-link video">BN-20 Getting Started (11:20)</a>
            
            <h3>📖 Written Guides</h3>
            <a href="https://www.rolanddga.com/support" target="_blank" class="tutorial-link guide">Roland Support Center</a>
        </div>
    </div>
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

<div class="machine-card fabber1" data-tier="Fabber 1" data-category="Thermoforming">
    <div class="machine-header">
        <h2>Mayku Formbox</h2>
        <span class="tier-badge fabber1">Fabber 1</span>
    </div>
    <p class="machine-type">Thermoforming</p>
    <div class="tutorial-links">
        <h3>📹 Video Tutorials</h3>
        <a href="https://www.youtube.com/watch?v=vV9W8V3h_20" target="_blank" class="tutorial-link video">Thermoforming Guide (6:45)</a>
        
        <h3>📖 Written Guides</h3>
        <a href="https://www.mayku.com/pages/learn" target="_blank" class="tutorial-link guide">Mayku Learning Center</a>
    </div>
</div> 

<div class="machine-card fabber1" data-tier="Fabber 1" data-category="Workbench">
    <div class="machine-header">
        <h2>Soldering & Powertools</h2>
        <span class="tier-badge fabber1">Fabber 1</span>
    </div>
    <p class="machine-type">Workbench Equipment</p>
    <div class="tutorial-links">
        <h3>📹 Video Tutorials</h3>
        <a href="https://www.youtube.com/watch?v=uTloBPjC-uA" target="_blank" class="tutorial-link video">Soldering Basics (12:00)</a>
        <a href="https://www.youtube.com/watch?v=7p_G7UqU3rY" target="_blank" class="tutorial-link video">Power Tool Safety (8:30)</a>
        
        <h3>📖 Written Guides</h3>
        <a href="https://www.sparkfun.com/tutorials/106" target="_blank" class="tutorial-link guide">Soldering & Electronics Guide</a>
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