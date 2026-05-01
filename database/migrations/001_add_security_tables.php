<?php
/**
 * Database Migration: Add Security Tables
 * Creates tables for 2FA, login attempts tracking, and security audit logging
 * 
 * Run this migration to add security enhancements to the PHARMACIA system
 */

require_once __DIR__ . '/../../config/config.php';

$migration_name = '001_add_security_tables';
$migration_timestamp = date('Y-m-d H:i:s');

echo "Running migration: $migration_name\n";
echo "Timestamp: $migration_timestamp\n\n";

$errors = [];
$success_count = 0;

// ============================================================================
// CREATE TWO_FACTOR_AUTH TABLE
// ============================================================================

echo "Creating two_factor_auth table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS two_factor_auth (
    tfa_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    tfa_secret VARCHAR(255) NOT NULL,
    backup_codes LONGTEXT NOT NULL COMMENT 'JSON array of hashed backup codes',
    tfa_enabled BOOLEAN DEFAULT 0,
    enabled_at TIMESTAMP NULL,
    disabled_at TIMESTAMP NULL,
    last_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employee(E_ID) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_tfa_enabled (tfa_enabled)
)";

if ($conn->query($sql)) {
    echo "✓ two_factor_auth table created successfully\n";
    $success_count++;
} else {
    $error = "✗ Error creating two_factor_auth table: " . $conn->error;
    echo $error . "\n";
    $errors[] = $error;
}

// ============================================================================
// CREATE LOGIN_ATTEMPTS TABLE
// ============================================================================

echo "\nCreating login_attempts table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_attempt_ip VARCHAR(45),
    last_attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employee(E_ID) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_locked_until (locked_until),
    INDEX idx_last_attempt_time (last_attempt_time)
)";

if ($conn->query($sql)) {
    echo "✓ login_attempts table created successfully\n";
    $success_count++;
} else {
    $error = "✗ Error creating login_attempts table: " . $conn->error;
    echo $error . "\n";
    $errors[] = $error;
}

// ============================================================================
// CREATE SECURITY_AUDIT_LOG TABLE
// ============================================================================

echo "\nCreating security_audit_log table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS security_audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    resource VARCHAR(255),
    record_id INT,
    details LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employee(E_ID) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    INDEX idx_ip_address (ip_address),
    INDEX idx_user_action (user_id, action)
)";

if ($conn->query($sql)) {
    echo "✓ security_audit_log table created successfully\n";
    $success_count++;
} else {
    $error = "✗ Error creating security_audit_log table: " . $conn->error;
    echo $error . "\n";
    $errors[] = $error;
}

// ============================================================================
// CREATE RATE_LIMITS TABLE
// ============================================================================

echo "\nCreating rate_limits table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS rate_limits (
    limit_id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_attempt_time (attempt_time),
    INDEX idx_ip_address (ip_address)
)";

if ($conn->query($sql)) {
    echo "✓ rate_limits table created successfully\n";
    $success_count++;
} else {
    $error = "✗ Error creating rate_limits table: " . $conn->error;
    echo $error . "\n";
    $errors[] = $error;
}

// ============================================================================
// MIGRATION SUMMARY
// ============================================================================

echo "\n" . str_repeat("=", 60) . "\n";
echo "Migration Summary\n";
echo str_repeat("=", 60) . "\n";
echo "Tables created successfully: $success_count\n";
echo "Errors encountered: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nMigration completed at: " . date('Y-m-d H:i:s') . "\n";

// Record migration in database
$migration_record = "
INSERT INTO migrations (migration_name, executed_at) 
VALUES ('$migration_name', '$migration_timestamp')
ON DUPLICATE KEY UPDATE executed_at = '$migration_timestamp'
";

// Create migrations table if it doesn't exist
$conn->query("
CREATE TABLE IF NOT EXISTS migrations (
    migration_id INT AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

if ($conn->query($migration_record)) {
    echo "Migration record saved to database.\n";
} else {
    echo "Warning: Could not save migration record: " . $conn->error . "\n";
}

?>
