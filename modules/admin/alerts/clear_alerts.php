<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $log_file = __DIR__ . '/../../../logs/alerts.log';
    if (file_exists($log_file)) {
        // Log the clearance action
        $log_entry = json_encode([
            'type' => 'info',
            'message' => 'Audit logs cleared manually by ' . ($_SESSION['name'] ?? 'Unknown Admin'),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]) . "\n";
        
        file_put_contents($log_file, $log_entry); // Overwrite with clearance log
        echo json_encode(['success' => true]);
    }
}
?>
