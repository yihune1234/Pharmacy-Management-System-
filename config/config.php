<?php
$host = "mysql-25712907-yihunebelay859-f8f5.h.aivencloud.com";
$user = "avnadmin";
$pass = getenv('DB_PASS');
$db   = "pharmacy_db";
$port = 25713;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, __DIR__ . "/ca.pem", NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Aiven Connection Failed: " . mysqli_connect_error());
}

echo "Connected to Aiven successfully!";
?>
