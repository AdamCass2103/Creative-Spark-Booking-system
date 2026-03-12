<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if we're running on Vercel or locally
if (getenv('VERCEL_ENV')) {
    // ============================================
    // WE'RE ON VERCEL - Use Aiven cloud database
    // ============================================
    
    // Get connection from environment variables
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');
    $dbname = getenv('DB_NAME');
    
    // Debug: Check if env vars exist
    error_log("DB_HOST: " . ($host ? 'set' : 'NOT SET'));
    error_log("DB_PORT: " . ($port ? 'set' : 'NOT SET'));
    error_log("DB_USER: " . ($user ? 'set' : 'NOT SET'));
    error_log("DB_PASS: " . ($pass ? 'set' : 'NOT SET'));
    error_log("DB_NAME: " . ($dbname ? 'set' : 'NOT SET'));
    error_log("CA_CERT: " . (getenv('CA_CERT') ? 'set' : 'NOT SET'));
    
    // For Aiven, SSL is REQUIRED
    $conn = mysqli_init();
    
    // Handle SSL certificate
    if (getenv('CA_CERT')) {
        // Create a temporary file for the certificate
        $cert_path = '/tmp/ca.pem';
        $result = file_put_contents($cert_path, getenv('CA_CERT'));
        error_log("Cert file created: " . ($result ? 'yes' : 'no'));
        error_log("Cert path: " . $cert_path);
        
        if (!file_exists($cert_path)) {
            error_log("Cert file does not exist after writing!");
            die("SSL certificate error");
        }
        
        $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
    } else {
        error_log("No CA_CERT environment variable found!");
        die("SSL configuration error");
    }
    
    // Enable exceptions for better error handling
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        error_log("Attempting to connect to $host:$port as $user");
        $conn->real_connect($host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
        error_log("Connection successful!");
    } catch (mysqli_sql_exception $e) {
        error_log("Connection failed: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        die("Database connection error: " . $e->getMessage());
    }
    
} else {
    // ============================================
    // WE'RE LOCAL - Use XAMPP MySQL
    // ============================================
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'booking_system';
    $port = 3306;
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn = new mysqli($host, $user, $pass, $dbname, $port);
    } catch (mysqli_sql_exception $e) {
        error_log("Local connection failed: " . $e->getMessage());
        die("Database connection error. Please try again later.");
    }
}

// Set charset
$conn->set_charset("utf8mb4");
?>