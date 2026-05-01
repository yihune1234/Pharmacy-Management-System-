<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$prescription_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($prescription_id <= 0) {
    set_flash_message("Invalid prescription ID.", "error");
    header("Location: prescriptions.php");
    exit();
}

// Get prescription to delete file if exists
$stmt = $conn->prepare("SELECT file_path FROM prescriptions WHERE prescription_id = ?");
$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$result = $stmt->get_result();
$prescription = $result->fetch_assoc();
$stmt->close();

if (!$prescription) {
    set_flash_message("Prescription not found.", "error");
    header("Location: prescriptions.php");
    exit();
}

// Delete file if exists
if ($prescription['file_path']) {
    $file_path = __DIR__ . '/../../../' . $prescription['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete prescription from database
$stmt = $conn->prepare("DELETE FROM prescriptions WHERE prescription_id = ?");
$stmt->bind_param("i", $prescription_id);

if ($stmt->execute()) {
    set_flash_message("Prescription deleted successfully!", "success");
} else {
    set_flash_message("Error deleting prescription: " . $stmt->error, "error");
}
$stmt->close();

header("Location: prescriptions.php");
exit();
?>
