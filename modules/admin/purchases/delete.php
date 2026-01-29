<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['pid']) && isset($_GET['sid']) && isset($_GET['mid'])) {
    $pid = $conn->real_escape_string($_GET['pid']);
    $sid = $conn->real_escape_string($_GET['sid']);
    $mid = $conn->real_escape_string($_GET['mid']);
    				
    $sql = "DELETE FROM purchase WHERE P_ID = '$pid' AND Sup_ID = '$sid' AND Med_ID = '$mid'";

    if ($conn->query($sql)) {
        set_flash_message("Purchase record deleted.", "success");
    } else {
        set_flash_message("Error deleting purchase record.", "error");
    }
}

header("Location: view.php");
exit();
?>