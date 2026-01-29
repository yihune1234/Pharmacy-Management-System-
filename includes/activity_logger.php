<?php
require_once __DIR__ . '/../../../config/config.php';

function log_activity($user_id, $action, $description) {
    global $conn;
    
    $user_id = (int)$user_id;
    $action = $conn->real_escape_string($action);
    $description = $conn->real_escape_string($description);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT'] ?? '');
    
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) 
            VALUES ($user_id, '$action', '$description', '$ip_address', '$user_agent', NOW())";
    
    $conn->query($sql);
}

function get_activity_logs($limit = 50, $user_id = null) {
    global $conn;
    
    $sql = "SELECT al.*, e.E_Fname, e.E_Lname, r.role_name
            FROM activity_logs al
            LEFT JOIN employee e ON al.user_id = e.E_ID
            LEFT JOIN roles r ON e.role_id = r.role_id";
    
    if ($user_id) {
        $sql .= " WHERE al.user_id = " . (int)$user_id;
    }
    
    $sql .= " ORDER BY al.created_at DESC LIMIT " . (int)$limit;
    
    $result = $conn->query($sql);
    $logs = [];
    
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    return $logs;
}

function get_user_activities($user_id, $days = 30) {
    global $conn;
    
    $sql = "SELECT action, COUNT(*) as count, DATE(created_at) as date
            FROM activity_logs 
            WHERE user_id = " . (int)$user_id . "
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
            GROUP BY action, DATE(created_at)
            ORDER BY date DESC, action";
    
    $result = $conn->query($sql);
    $activities = [];
    
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    return $activities;
}
?>
