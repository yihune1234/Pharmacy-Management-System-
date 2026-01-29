<?php
/**
 * Session Validation Middleware
 * Provides secure session management and role-based access control
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Get current user role
 */
function get_user_role() {
    return $_SESSION['role'] ?? '';
}

/**
 * Check if user has specific role
 */
function has_role($required_role) {
    return get_user_role() === strtolower($required_role);
}

/**
 * Validate session and redirect if not authenticated
 */
function require_auth($redirect_to = '../auth/login.php') {
    if (!is_logged_in()) {
        set_flash_message("Please login to access this page.", "error");
        header("Location: " . $redirect_to);
        exit();
    }
}

/**
 * Validate role-based access
 */
function require_role($required_role, $redirect_to = '../auth/login.php') {
    require_auth($redirect_to);
    
    if (!has_role($required_role)) {
        set_flash_message("Access denied. You don't have permission to view this page.", "error");
        header("Location: " . $redirect_to);
        exit();
    }
}

/**
 * Auto logout after inactivity (30 minutes)
 */
function check_session_timeout() {
    $timeout = 1800; // 30 minutes in seconds
    $current_time = time();
    
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = $current_time - $_SESSION['last_activity'];
        
        if ($inactive_time > $timeout) {
            // Session expired, logout user
            session_unset();
            session_destroy();
            set_flash_message("Session expired due to inactivity. Please login again.", "warning");
            header("Location: ../auth/login.php");
            exit();
        }
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = $current_time;
}

/**
 * Validate admin access
 */
function require_admin() {
    require_role('admin');
}

/**
 * Validate pharmacist access
 */
function require_pharmacist() {
    require_role('pharmacist');
}

/**
 * Validate cashier access
 */
function require_cashier() {
    require_role('cashier');
}

/**
 * Get current user information
 */
function get_current_user_info() {
    return [
        'id' => $_SESSION['user'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'name' => $_SESSION['name'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ];
}

/**
 * Check if accessing correct role area
 */
function validate_role_area($current_area) {
    $user_role = get_user_role();
    
    // Check if user is trying to access wrong area
    if ($current_area === 'admin' && $user_role !== 'admin') {
        set_flash_message("Access denied. Admin area restricted.", "error");
        header("Location: ../auth/login.php");
        exit();
    }
    
    if ($current_area === 'pharmacist' && !in_array($user_role, ['pharmacist', 'admin'])) {
        set_flash_message("Access denied. Pharmacist area restricted.", "error");
        header("Location: ../auth/login.php");
        exit();
    }
    
    if ($current_area === 'cashier' && !in_array($user_role, ['cashier', 'admin'])) {
        set_flash_message("Access denied. Cashier area restricted.", "error");
        header("Location: ../auth/login.php");
        exit();
    }
}

// Auto-check session timeout on every page load
check_session_timeout();
?>
