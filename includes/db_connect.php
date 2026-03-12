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
        // Write certificate to temporary file
        $cert_path = '/tmp/ca.pem';
        file_put_contents($cert_path, getenv('CA_CERT'));
        $conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
    } else {
        // Local development - use local cert file
        $ssl_ca = __DIR__ . '/../certs/ca.pem';
        $conn->ssl_set(NULL, NULL, $ssl_ca, NULL, NULL);
    }
    
    $conn->real_connect($host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    
} else {
    // ============================================
    // WE'RE LOCAL - Use XAMPP MySQL
    // ============================================
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'booking_system';
    $port = 3306;
    
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
}

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Database connection error. Please try again later.");
}

$conn->set_charset("utf8mb4");
?>