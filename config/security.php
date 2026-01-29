<?php
// Security configuration for PHARMACIA Pharmacy Management System

// Security settings
define('SECURITY_ENABLED', true);
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// CSRF Protection
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input validation and sanitization
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    return preg_match('/^[0-9\-\+\(\)\s]+$/', $phone);
}

function validate_number($number, $min = null, $max = null) {
    if (!is_numeric($number)) return false;
    $num = (float)$number;
    if ($min !== null && $num < $min) return false;
    if ($max !== null && $num > $max) return false;
    return true;
}

// SQL Injection Prevention
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

// XSS Prevention
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Password Security
function hash_password($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 1
    ]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Session Security
function secure_session_start() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
    
    // Regenerate session ID to prevent session fixation
    if (!isset($_SESSION['initialized'])) {
        session_regenerate_id(true);
        $_SESSION['initialized'] = true;
    }
}

// Rate Limiting
function check_rate_limit($identifier, $max_attempts = 5, $window = 300) {
    $cache_key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$cache_key])) {
        $_SESSION[$cache_key] = [];
    }
    
    $now = time();
    $_SESSION[$cache_key] = array_filter($_SESSION[$cache_key], function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    if (count($_SESSION[$cache_key]) >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$cache_key][] = $now;
    return true;
}

// IP Security
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

// File Upload Security
function secure_file_upload($file, $allowed_types = [], $max_size = 5242880) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'Invalid file upload';
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = 'File size exceeds maximum allowed size';
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!empty($allowed_types) && !in_array($mime_type, $allowed_types)) {
        $errors[] = 'File type not allowed';
    }
    
    // Check for malicious content
    $file_content = file_get_contents($file['tmp_name']);
    if (strpos($file_content, '<?php') !== false || strpos($file_content, '<script') !== false) {
        $errors[] = 'File contains potentially malicious content';
    }
    
    return $errors;
}

// Database Security
function secure_query($sql, $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    
    if (!empty($params)) {
        $types = '';
        $bind_params = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $bind_params[] = $param;
        }
        
        $stmt->bind_param($types, ...$bind_params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    return $stmt;
}

// HTTPS Enforcement
function enforce_https() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    
    if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }
    
    // Redirect to HTTPS if not secure
    if (SECURITY_ENABLED && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url");
        exit();
    }
    
    return false;
}

// Security Headers
function set_security_headers() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data:; font-src \'self\';');
}

// Initialize security
secure_session_start();
set_security_headers();
enforce_https();

// Auto-cleanup old sessions
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_destroy();
    header('Location: /modules/auth/login.php?timeout=1');
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();
?>
