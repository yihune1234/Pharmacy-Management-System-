<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM customer WHERE C_ID = '$id'";
    
    if ($conn->query($sql)) {
        set_flash_message("Customer profile deleted successfully.", "success");
    } else {
        set_flash_message("Unable to delete customer. They may have transaction history.", "error");
    }
}

header("Location: view.php");
exit();
?>