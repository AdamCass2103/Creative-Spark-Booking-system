<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/config.php';
// Debug output (visible in page source)
echo "<!-- Config loaded successfully -->\n";
echo "<!-- Environment: " . ENVIRONMENT . " -->\n";
echo "<!-- BASE_PATH: " . BASE_PATH . " -->\n";
echo "<!-- SITE_URL: " . SITE_URL . " -->\n";
<?php
// EMERGENCY DEBUG - REMOVE AFTER FIXING
header('Content-Type: text/html');
echo "<h1>🔍 VERCEL PATH DEBUGGER</h1>";
echo "<pre>";

echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "Current script: " . __FILE__ . "\n\n";

// Scan current directory
echo "📁 Files in " . __DIR__ . ":\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "  - " . $file . (is_dir(__DIR__ . '/' . $file) ? " (DIR)" : "") . "\n";
    }
}

// Look for images anywhere
echo "\n🔍 Searching for 'images' folder...\n";

// Check common locations
$locations = [
    __DIR__ . '/images',
    __DIR__ . '/../images',
    $_SERVER['DOCUMENT_ROOT'] . '/images',
    dirname(__DIR__) . '/images',
    __DIR__ . '/public/images',
    dirname(__DIR__) . '/public/images'
];

foreach ($locations as $location) {
    if (file_exists($location)) {
        echo "✅ FOUND: " . $location . "\n";
        if (is_dir($location)) {
            $img_files = scandir($location);
            echo "   Files: " . implode(', ', array_diff($img_files, ['.', '..'])) . "\n";
        }
    } else {
        echo "❌ Not found: " . $location . "\n";
    }
}

echo "</pre>";
exit; // Stop here so we can see the output

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Spark FabLab</title>
    <link rel="stylesheet" href="<?php echo base_path('css/landing.css'); ?>">
</head>
<body>
<div class="container">
    <div class="fablab-logo">
    <img src="/images/FabLab-logo-small.png" alt="Creative Spark FabLab Logo">
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
<?php
echo "<!-- ===== VERGEL FILE DEBUG ===== -->\n";

// Show current directory
echo "<!-- Current directory: " . __DIR__ . " -->\n";

// List files in current directory (public folder)
$public_files = scandir(__DIR__);
echo "<!-- Files in public folder: " . implode(', ', $public_files) . " -->\n";

// Check if images folder exists in public
if (is_dir(__DIR__ . '/images')) {
    echo "<!-- images folder EXISTS in public -->\n";
    $image_files = scandir(__DIR__ . '/images');
    echo "<!-- Files in public/images: " . implode(', ', $image_files) . " -->\n";
} else {
    echo "<!-- images folder DOES NOT exist in public -->\n";
}

// Check one level up
$parent_dir = dirname(__DIR__);
echo "<!-- Parent directory: " . $parent_dir . " -->\n";
if (is_dir($parent_dir . '/images')) {
    echo "<!-- images folder EXISTS in parent directory -->\n";
    $parent_images = scandir($parent_dir . '/images');
    echo "<!-- Files in parent/images: " . implode(', ', $parent_images) . " -->\n";
}

// Check root level
echo "<!-- Document root: " . $_SERVER['DOCUMENT_ROOT'] . " -->\n";
if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/images')) {
    echo "<!-- images folder EXISTS at document root -->\n";
}

echo "<!-- ===== END DEBUG ===== -->\n";
?>
</html>