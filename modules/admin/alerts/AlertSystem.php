<?php
require_once __DIR__ . '/../../../config/config.php';

class AlertSystem {
    private static $persistent_log_file = __DIR__ . '/../../../logs/alerts.log';
    
    public static function addAlert($type, $message, $data = []) {
        $alert = [
            'id' => uniqid(),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];
        
        self::logAlert($alert);
        self::triggerNotification($alert);
    }
    
    private static function logAlert($alert) {
        // Create logs directory if it doesn't exist
        $log_dir = dirname(self::$persistent_log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_entry = json_encode($alert) . "\n";
        file_put_contents(self::$persistent_log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    private static function triggerNotification($alert) {
        // Flash message for immediate display
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION['alerts'][] = $alert;
        
        // Sound notification for critical/warning/error
        if (in_array($alert['type'], ['error', 'warning', 'critical'])) {
            $_SESSION['play_sound'] = $alert['type'];
        }
        
        // Email notification for critical alerts
        if ($alert['type'] === 'critical') {
            self::sendEmailNotification($alert);
        }
    }
    
    private static function sendEmailNotification($alert) {
        // Email logic placeholder
        // error_log("Sending email for alert: " . $alert['message']);
    }
    
    public static function getAlerts($limit = 50) {
        $alerts = [];
        if (file_exists(self::$persistent_log_file)) {
            $lines = file(self::$persistent_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                // Get last $limit lines
                $lines = array_slice($lines, -$limit);
                foreach ($lines as $line) {
                    $data = json_decode($line, true);
                    if ($data) {
                        $alerts[] = $data;
                    }
                }
            }
        }
        return array_reverse($alerts);
    }
    
    public static function checkLowStock() {
        global $conn;
        if (!$conn) return;
        
        $low_stock = $conn->query("SELECT Med_ID, Med_Name, Med_Qty, Location_Rack FROM meds WHERE Med_Qty <= 10 ORDER BY Med_Qty ASC");
        
        if ($low_stock && $low_stock->num_rows > 0) {
            while($med = $low_stock->fetch_assoc()) {
                // Determine if we should alert (avoid spamming? For now we just list them)
                // In a real scheduled job, we'd check if we already alerted recently.
            }
        }
    }
    
    public static function checkExpiryAlerts() {
        global $conn;
        if (!$conn) return;

        try {
            $expiry_alerts = $conn->query("
                SELECT DISTINCT p.Med_ID, m.Med_Name, p.Exp_Date, p.P_Qty as Med_Qty
                FROM purchase p
                JOIN meds m ON p.Med_ID = m.Med_ID
                WHERE p.Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND p.Exp_Date >= CURDATE()
                ORDER BY p.Exp_Date ASC
            ");
        } catch (Exception $e) {
            // Log error
        }
    }
    
    public static function checkSystemHealth() {
        global $conn;
        if (!$conn) {
            self::addAlert('critical', 'Database Connection Failed', ['timestamp' => date('Y-m-d H:i:s')]);
        }
    }
}
?>
