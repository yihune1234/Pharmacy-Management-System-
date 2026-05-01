<?php
/**
 * Security Audit Module
 * Provides role-based access logging, failed login tracking, and suspicious activity detection
 */

require_once __DIR__ . '/../config/config.php';

// ============================================================================
// ROLE-BASED ACCESS LOGGING
// ============================================================================

/**
 * Log user access to a resource
 */
function log_access($user_id, $resource, $action, $details = '') {
    global $conn;
    
    $ip_address = get_client_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $timestamp = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("
        INSERT INTO security_audit_log (user_id, action, resource, details, ip_address, user_agent, timestamp)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("issssss", $user_id, $action, $resource, $details, $ip_address, $user_agent, $timestamp);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Log security event
 */
function log_security_event($user_id, $event_type, $description, $ip_address = '') {
    global $conn;
    
    if (empty($ip_address)) {
        $ip_address = get_client_ip();
    }
    
    $timestamp = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("
        INSERT INTO security_audit_log (user_id, action, resource, details, ip_address, timestamp)
        VALUES (?, ?, 'security_event', ?, ?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $event_type, $description, $ip_address, $timestamp);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Log data modification
 */
function log_data_modification($user_id, $table_name, $action, $record_id, $old_values = '', $new_values = '') {
    global $conn;
    
    $ip_address = get_client_ip();
    $timestamp = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("
        INSERT INTO security_audit_log (user_id, action, resource, record_id, details, ip_address, timestamp)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        $details = json_encode([
            'old_values' => $old_values,
            'new_values' => $new_values
        ]);
        $stmt->bind_param("issiiss", $user_id, $action, $table_name, $record_id, $details, $ip_address, $timestamp);
        $stmt->execute();
        $stmt->close();
    }
}

// ============================================================================
// FAILED LOGIN TRACKING
// ============================================================================

/**
 * Get failed login attempts for a user
 */
function get_failed_login_attempts($user_id, $hours = 24) {
    global $conn;
    
    $time_threshold = date('Y-m-d H:i:s', time() - ($hours * 3600));
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempt_count, MAX(last_attempt_time) as last_attempt
        FROM login_attempts
        WHERE user_id = ? AND last_attempt_time > ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $time_threshold);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    
    return null;
}

/**
 * Get failed login attempts by IP address
 */
function get_failed_login_attempts_by_ip($ip_address, $hours = 24) {
    global $conn;
    
    $time_threshold = date('Y-m-d H:i:s', time() - ($hours * 3600));
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempt_count, MAX(last_attempt_time) as last_attempt
        FROM login_attempts
        WHERE last_attempt_ip = ? AND last_attempt_time > ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("ss", $ip_address, $time_threshold);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    
    return null;
}

/**
 * Get all failed login attempts
 */
function get_all_failed_login_attempts($limit = 100) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT la.*, e.E_Username, e.E_Fname
        FROM login_attempts la
        LEFT JOIN employee e ON la.user_id = e.E_ID
        WHERE la.failed_attempts > 0
        ORDER BY la.last_attempt_time DESC
        LIMIT ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $attempts = [];
        while ($row = $result->fetch_assoc()) {
            $attempts[] = $row;
        }
        $stmt->close();
        return $attempts;
    }
    
    return [];
}

// ============================================================================
// SUSPICIOUS ACTIVITY DETECTION
// ============================================================================

/**
 * Detect suspicious login patterns
 */
function detect_suspicious_login($user_id, $ip_address) {
    global $conn;
    
    $alerts = [];
    
    // Check for multiple failed attempts
    $failed_attempts = get_failed_login_attempts($user_id, 1);
    if ($failed_attempts && $failed_attempts['attempt_count'] >= 3) {
        $alerts[] = [
            'type' => 'multiple_failed_attempts',
            'severity' => 'high',
            'message' => 'Multiple failed login attempts detected'
        ];
    }
    
    // Check for login from new IP
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM security_audit_log
        WHERE user_id = ? AND action = 'login_success' AND ip_address = ?
        AND timestamp > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $ip_address);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] == 0) {
            $alerts[] = [
                'type' => 'new_ip_login',
                'severity' => 'medium',
                'message' => 'Login from new IP address'
            ];
        }
    }
    
    // Check for rapid successive logins
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM security_audit_log
        WHERE user_id = ? AND action = 'login_success'
        AND timestamp > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 1) {
            $alerts[] = [
                'type' => 'rapid_login',
                'severity' => 'high',
                'message' => 'Rapid successive login attempts'
            ];
        }
    }
    
    return $alerts;
}

/**
 * Detect suspicious data access patterns
 */
function detect_suspicious_data_access($user_id) {
    global $conn;
    
    $alerts = [];
    
    // Check for bulk data access
    $stmt = $conn->prepare("
        SELECT COUNT(*) as access_count FROM security_audit_log
        WHERE user_id = ? AND action IN ('view', 'export', 'download')
        AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['access_count'] > 50) {
            $alerts[] = [
                'type' => 'bulk_data_access',
                'severity' => 'high',
                'message' => 'Unusual bulk data access detected'
            ];
        }
    }
    
    // Check for access outside normal hours
    $hour = date('H');
    if ($hour < 6 || $hour > 22) {
        $alerts[] = [
            'type' => 'off_hours_access',
            'severity' => 'low',
            'message' => 'Access outside normal business hours'
        ];
    }
    
    return $alerts;
}

/**
 * Detect privilege escalation attempts
 */
function detect_privilege_escalation($user_id, $old_role, $new_role) {
    global $conn;
    
    $alerts = [];
    
    if ($old_role !== $new_role) {
        // Check if user has permission to change roles
        $stmt = $conn->prepare("
            SELECT role_name FROM roles r
            JOIN employee e ON e.role_id = r.role_id
            WHERE e.E_ID = ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['role_name'] !== 'Admin') {
                $alerts[] = [
                    'type' => 'privilege_escalation_attempt',
                    'severity' => 'critical',
                    'message' => 'Unauthorized privilege escalation attempt'
                ];
            }
        }
    }
    
    return $alerts;
}

/**
 * Get security audit log
 */
function get_security_audit_log($filters = [], $limit = 100, $offset = 0) {
    global $conn;
    
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($filters['user_id'])) {
        $where_clauses[] = 'user_id = ?';
        $params[] = $filters['user_id'];
        $types .= 'i';
    }
    
    if (!empty($filters['action'])) {
        $where_clauses[] = 'action = ?';
        $params[] = $filters['action'];
        $types .= 's';
    }
    
    if (!empty($filters['ip_address'])) {
        $where_clauses[] = 'ip_address = ?';
        $params[] = $filters['ip_address'];
        $types .= 's';
    }
    
    if (!empty($filters['date_from'])) {
        $where_clauses[] = 'timestamp >= ?';
        $params[] = $filters['date_from'];
        $types .= 's';
    }
    
    if (!empty($filters['date_to'])) {
        $where_clauses[] = 'timestamp <= ?';
        $params[] = $filters['date_to'];
        $types .= 's';
    }
    
    $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    $query = "
        SELECT sal.*, e.E_Username, e.E_Fname
        FROM security_audit_log sal
        LEFT JOIN employee e ON sal.user_id = e.E_ID
        $where
        ORDER BY sal.timestamp DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        $stmt->close();
        return $logs;
    }
    
    return [];
}

/**
 * Get security audit log count
 */
function get_security_audit_log_count($filters = []) {
    global $conn;
    
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($filters['user_id'])) {
        $where_clauses[] = 'user_id = ?';
        $params[] = $filters['user_id'];
        $types .= 'i';
    }
    
    if (!empty($filters['action'])) {
        $where_clauses[] = 'action = ?';
        $params[] = $filters['action'];
        $types .= 's';
    }
    
    if (!empty($filters['ip_address'])) {
        $where_clauses[] = 'ip_address = ?';
        $params[] = $filters['ip_address'];
        $types .= 's';
    }
    
    $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    $query = "SELECT COUNT(*) as count FROM security_audit_log $where";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt && !empty($params)) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'];
    } elseif ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'];
    }
    
    return 0;
}

/**
 * Export security audit log
 */
function export_security_audit_log($filters = [], $format = 'csv') {
    $logs = get_security_audit_log($filters, 10000);
    
    if ($format === 'csv') {
        $output = "Timestamp,User,Action,Resource,IP Address,Details\n";
        foreach ($logs as $log) {
            $output .= sprintf(
                '"%s","%s","%s","%s","%s","%s"' . "\n",
                $log['timestamp'],
                $log['E_Username'] ?? 'Unknown',
                $log['action'],
                $log['resource'],
                $log['ip_address'],
                str_replace('"', '""', $log['details'])
            );
        }
        return $output;
    }
    
    return json_encode($logs, JSON_PRETTY_PRINT);
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

?>
