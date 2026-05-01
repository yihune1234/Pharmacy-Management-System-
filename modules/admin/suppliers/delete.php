<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM suppliers WHERE Sup_ID = '$id'";
    
    if ($conn->query($sql)) {
        set_flash_message("Supplier record removed successfully.", "success");
    } else {
        set_flash_message("Unable to delete supplier. They may have active purchase records.", "error");
    }
}

header("Location: view.php");
exit();
?>