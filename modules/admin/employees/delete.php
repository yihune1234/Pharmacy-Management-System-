<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    // Prevent self-deletion
    if ($id == $_SESSION['user']) {
        set_flash_message("You cannot delete your own account.", "warning");
        header("Location: view.php");
        exit();
    }

    $sql = "DELETE FROM employee WHERE E_ID = '$id'";
    
    if ($conn->query($sql)) {
        set_flash_message("Employee record removed successfully.", "success");
    } else {
        set_flash_message("Unable to delete employee. They may have active system logs.", "error");
    }
}

header("Location: view.php");
exit();
?>