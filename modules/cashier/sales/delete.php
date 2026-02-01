<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate role
validate_role_area('pharmacist');

if (isset($_GET['slid']) && isset($_GET['mid'])) {
    $slid = $conn->real_escape_string($_GET['slid']);
    $mid = $conn->real_escape_string($_GET['mid']);
    
    $sql = "DELETE FROM sales_items WHERE sale_id = '$slid' AND med_id = '$mid'";
    if ($conn->query($sql)) {
        set_flash_message("Item removed from sale.", "success");
    } else {
        set_flash_message("Error removing item.", "error");
    }
}

header("Location: pos2.php");
exit();
?>


