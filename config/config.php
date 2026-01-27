<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pharmacy_db";

/* Try connecting directly to the database */
$conn = @new mysqli($host, $user, $pass, $db);

/* If DB doesn't exist yet → go to installer */
if ($conn->connect_error) {
    header("Location: ./database/install.php");
    exit();
}
?>
