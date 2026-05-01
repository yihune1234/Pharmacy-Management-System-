<?php
/**
 * Enhanced Security Configuration for PHARMACIA
 * Provides 2FA, account lockout, encryption, CSRF protection, and rate limiting
 */

require_once __DIR__ . '/config.php';

// ============================================================================
// 2FA SETUP FUNCTIONS
// ============================================================================

/**
 * Generate a secret key for 2FA (TOTP)
 * Uses base32 encoding for compatibility with authenticator apps
 */
function generate_2fa_secret() {
    $bytes = random_bytes(32);
    return base32_encode($bytes);
}

/**
 * Base32 encoding for TOTP secret
 */
function base32_encode($input) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0; $i < strlen($input); $i++) {
        $v = ($v << 8) | ord($input[$i]);
        $vbits += 8;
        while ($vbits >= 5) {
            $vbits -= 5;
            $output .= $alphabet[($v >> $vbits) & 31];
        }
    }
    
    if ($vbits > 0) {
        $output .= $alphabet[($v << (5 - $vbits)) & 31];
    }
    
    return $output;
}

/**
 * Base32 decoding for TOTP verification
 */
function base32_decode($input) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0; $i < strlen($input); $i++) {
        $c = strpos($alphabet, $input[$i]);
        if ($c === false) continue;
        $v = ($v << 5) | $c;
        $vbits += 5;
        if ($vbits >= 8) {
            $vbits -= 8;
            $output .= chr(($v >> $vbits) & 255);
        }
    }
    
    return $output;
}

/**
 * Generate QR code URL for 2FA setup
 * Uses Google Charts API for QR code generation
 */
function generate_2fa_qr_code($user_email, $secret, $app_name = 'PHARMACIA') {
    $label = urlencode($app_name . ' (' . $user_email . ')');
    $secret_encoded = urlencode($secret);
    
    $url = 'https://chart.googleapis.com/chart?chs=300x300&chld=M|0&cht=qr&chl=';
    $url .= urlencode('otpauth://totp/' . $label . '?secret=' . $secret_encoded . '&issuer=' . urlencode($app_name));
    
    return $url;
}

/**
 * Verify TOTP code
 */
function verify_totp_code($secret, $code, $time_window = 1) {
    $secret_decoded = base32_decode($secret);
    $time = floor(time() / 30);
    
    for ($i = -$time_window; $i <= $time_window; $i++) {
        $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time + $i), $secret_decoded, true);
        $offset = ord($hash[19]) & 0xf;
        $code_generated = (((ord($hash[$offset]) & 0x7f) << 24) |
                          ((ord($hash[$offset + 1]) & 0xff) << 16) |
                          ((ord($hash[$offset + 2]) & 0xff) << 8) |
                          (ord($hash[$offset + 3]) & 0xff)) % 1000000;
        
        if ($code_generated == $code) {
            return true;
        }
    }
    
    return false;
}

/**
 * Generate backup codes for 2FA
 * Returns array of 10 backup codes
 */
function generate_backup_codes($count = 10) {
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
        $code = strtoupper(bin2hex(random_bytes(4)));
        $codes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
    }
    return $codes;
}

/**
 * Hash backup codes for storage
 */
function hash_backup_codes($codes) {
    $hashed = [];
    foreach ($codes as $code) {
        $hashed[] = password_hash($code, PASSWORD_ARGON2ID);
    }
    return $hashed;
}

/**
 * Verify backup code
 */
function verify_backup_code($code, $hashed_codes) {
    foreach ($hashed_codes as $hashed) {
        if (password_verify($code, $hashed)) {
            return true;
        }
    }
    return false;
}

// ============================================================================
// ACCOUNT LOCKOUT MECHANISM
// ============================================================================

/**
 * Check if account is locked due to failed login attempts
 */
function is_account_locked($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT locked_until FROM login_attempts 
        WHERE user_id = ? AND locked_until > NOW()
        LIMIT 1
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $is_locked = $result->num_rows > 0;
        $stmt->close();
        return $is_locked;
    }
    
    return false;
}

/**
 * Get remaining lockout time in seconds
 */
function get_lockout_remaining_time($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT TIMESTAMPDIFF(SECOND, NOW(), locked_until) as remaining 
        FROM login_attempts 
        WHERE user_id = ? AND locked_until > NOW()
        LIMIT 1
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return max(0, $row['remaining']);
        }
        $stmt->close();
    }
    
    return 0;
}

/**
 * Record failed login attempt
 */
function record_failed_login($user_id, $ip_address) {
    global $conn;
    
    $max_attempts = 5;
    $lockout_duration = 900; // 15 minutes
    
    // Get current failed attempts
    $stmt = $conn->prepare("
        SELECT failed_attempts FROM login_attempts 
        WHERE user_id = ? AND locked_until IS NULL
        LIMIT 1
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $attempts = $row['failed_attempts'] + 1;
            
            if ($attempts >= $max_attempts) {
                // Lock account
                $locked_until = date('Y-m-d H:i:s', time() + $lockout_duration);
                $update_stmt = $conn->prepare("
                    UPDATE login_attempts 
                    SET failed_attempts = ?, locked_until = ?, last_attempt_ip = ?, last_attempt_time = NOW()
                    WHERE user_id = ?
                ");
                $update_stmt->bind_param("issi", $attempts, $locked_until, $ip_address, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Update failed attempts
                $update_stmt = $conn->prepare("
                    UPDATE login_attempts 
                    SET failed_attempts = ?, last_attempt_ip = ?, last_attempt_time = NOW()
                    WHERE user_id = ?
                ");
                $update_stmt->bind_param("isi", $attempts, $ip_address, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        } else {
            // First failed attempt
            $insert_stmt = $conn->prepare("
                INSERT INTO login_attempts (user_id, failed_attempts, last_attempt_ip, last_attempt_time)
                VALUES (?, 1, ?, NOW())
            ");
            $insert_stmt->bind_param("is", $user_id, $ip_address);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        
        $stmt->close();
    }
}

/**
 * Reset failed login attempts on successful login
 */
function reset_failed_login_attempts($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE login_attempts 
        SET failed_attempts = 0, locked_until = NULL
        WHERE user_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// ============================================================================
// DATA ENCRYPTION/DECRYPTION UTILITIES
// ============================================================================

/**
 * Encrypt sensitive data using AES-256-GCM
 */
function encrypt_data($data, $encryption_key = null) {
    if ($encryption_key === null) {
        $encryption_key = getenv('ENCRYPTION_KEY') ?: hash('sha256', 'PHARMACIA_DEFAULT_KEY', true);
    }
    
    $iv = openssl_random_pseudo_bytes(16);
    $cipher = 'aes-256-gcm';
    
    $encrypted = openssl_encrypt($data, $cipher, $encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
    
    // Combine IV, tag, and encrypted data
    $result = base64_encode($iv . $tag . $encrypted);
    
    return $result;
}

/**
 * Decrypt sensitive data
 */
function decrypt_data($encrypted_data, $encryption_key = null) {
    if ($encryption_key === null) {
        $encryption_key = getenv('ENCRYPTION_KEY') ?: hash('sha256', 'PHARMACIA_DEFAULT_KEY', true);
    }
    
    $data = base64_decode($encrypted_data);
    $iv = substr($data, 0, 16);
    $tag = substr($data, 16, 16);
    $encrypted = substr($data, 32);
    
    $cipher = 'aes-256-gcm';
    $decrypted = openssl_decrypt($encrypted, $cipher, $encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
    
    return $decrypted;
}

/**
 * Hash sensitive data for comparison (one-way)
 */
function hash_sensitive_data($data) {
    return hash('sha256', $data);
}

// ============================================================================
// CSRF TOKEN MANAGEMENT
// ============================================================================

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token for form
 */
function get_csrf_token() {
    return generate_csrf_token();
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Token expires after 1 hour
    $token_age = time() - ($_SESSION['csrf_token_time'] ?? 0);
    if ($token_age > 3600) {
        unset($_SESSION['csrf_token']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token
 */
function regenerate_csrf_token() {
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);
    return generate_csrf_token();
}

/**
 * Output CSRF token as hidden input
 */
function csrf_token_input() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// ============================================================================
// RATE LIMITING FUNCTIONS
// ============================================================================

/**
 * Check rate limit for an action
 */
function check_rate_limit($identifier, $max_attempts = 5, $window = 300) {
    global $conn;
    
    $ip_address = get_client_ip();
    $now = date('Y-m-d H:i:s');
    $window_start = date('Y-m-d H:i:s', time() - $window);
    
    // Clean old entries
    $cleanup_stmt = $conn->prepare("
        DELETE FROM rate_limits 
        WHERE identifier = ? AND attempt_time < ?
    ");
    $cleanup_stmt->bind_param("ss", $identifier, $window_start);
    $cleanup_stmt->execute();
    $cleanup_stmt->close();
    
    // Count recent attempts
    $count_stmt = $conn->prepare("
        SELECT COUNT(*) as attempt_count FROM rate_limits 
        WHERE identifier = ? AND attempt_time > ?
    ");
    $count_stmt->bind_param("ss", $identifier, $window_start);
    $count_stmt->execute();
    $result = $count_stmt->get_result();
    $row = $result->fetch_assoc();
    $attempt_count = $row['attempt_count'];
    $count_stmt->close();
    
    if ($attempt_count >= $max_attempts) {
        return false;
    }
    
    // Record this attempt
    $insert_stmt = $conn->prepare("
        INSERT INTO rate_limits (identifier, ip_address, attempt_time)
        VALUES (?, ?, ?)
    ");
    $insert_stmt->bind_param("sss", $identifier, $ip_address, $now);
    $insert_stmt->execute();
    $insert_stmt->close();
    
    return true;
}

/**
 * Get remaining rate limit attempts
 */
function get_rate_limit_remaining($identifier, $max_attempts = 5, $window = 300) {
    global $conn;
    
    $window_start = date('Y-m-d H:i:s', time() - $window);
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempt_count FROM rate_limits 
        WHERE identifier = ? AND attempt_time > ?
    ");
    $stmt->bind_param("ss", $identifier, $window_start);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return max(0, $max_attempts - $row['attempt_count']);
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
