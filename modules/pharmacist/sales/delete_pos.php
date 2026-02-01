<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');

$sid = $_GET['sid'] ?? $_GET['slid'] ?? null;
$mid = $_GET['mid'] ?? null;

if ($sid && $mid) {
    $sid = $conn->real_escape_string($sid);
    $mid = $conn->real_escape_string($mid);
    
    $sql = "DELETE FROM sales_items WHERE sale_id = '$sid' AND med_id = '$mid'";
    if ($conn->query($sql)) {
        set_flash_message("Item removed from order.", "info");
    } else {
        set_flash_message("Could not remove item.", "error");
    }
    header("Location: pos1.php?sid=" . $sid);
} else {
    header("Location: pos1.php");
}
exit();
