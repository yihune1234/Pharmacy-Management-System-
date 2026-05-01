<?php
/*
 * Database Configuration
 * Supports both local development and environment-based production
 */

// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'pharmacy_db');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Initialize connection
$conn = mysqli_init();

// Support SSL if CA certificate is present (for cloud DBs like Aiven)
$ca_cert = __DIR__ . "/ca.pem";
if (file_exists($ca_cert) && getenv('DB_SSL') === 'true') {
    mysqli_ssl_set($conn, NULL, NULL, $ca_cert, NULL, NULL);
    $connect_flags = MYSQLI_CLIENT_SSL;
} else {
    $connect_flags = 0;
}

// Connect to database
if (!mysqli_real_connect($conn, DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, NULL, $connect_flags)) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set charset to ensure proper encoding
mysqli_set_charset($conn, "utf8mb4");

// Optional: Echo connection status for debugging (Disable in production)
// echo "Database Connected successfully!";
?>
