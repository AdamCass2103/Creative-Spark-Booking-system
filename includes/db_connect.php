<?php
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
    
    // For Aiven, SSL is REQUIRED
    $conn = mysqli_init();
    
    // Handle SSL certificate
    if (getenv('CA_CERT')) {
        // Create a temporary file for the certificate
        $cert_path = '/tmp/ca.pem';
        file_put_contents($cert_path, getenv('CA_CERT'));
        $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
    } else {
        // If no CA_CERT env var, try local path (shouldn't happen on Vercel)
        $ssl_ca = __DIR__ . '/../certs/ca.pem';
        if (file_exists($ssl_ca)) {
            $conn->ssl_set(NULL, NULL, $ssl_ca, NULL, NULL);
        } else {
            error_log("No SSL certificate found");
            die("Database configuration error");
        }
    }
    
    // Enable exceptions for better error handling
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn->real_connect($host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    } catch (mysqli_sql_exception $e) {
        error_log("Connection failed: " . $e->getMessage());
        die("Database connection error. Please try again later.");
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