# PHARMACIA Security Enhancements

This document outlines the comprehensive security enhancements implemented for the PHARMACIA pharmacy management system.

## Overview

The security enhancements include:
- Two-Factor Authentication (2FA) with TOTP and backup codes
- Account lockout mechanism after failed login attempts
- Data encryption/decryption utilities
- CSRF token management
- Rate limiting for login attempts
- Comprehensive security audit logging
- Suspicious activity detection

## Installation

### Step 1: Run Database Migration

Execute the migration script to create the required security tables:

```bash
php database/migrations/001_add_security_tables.php
```

This creates the following tables:
- `two_factor_auth` - Stores 2FA secrets and backup codes
- `login_attempts` - Tracks failed login attempts and account lockouts
- `security_audit_log` - Logs all security-related events
- `rate_limits` - Tracks rate limiting for various actions

### Step 2: Update Configuration

Ensure your `.env` file includes:

```env
# Optional: Set encryption key for data encryption
ENCRYPTION_KEY=your_encryption_key_here

# Database configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=pharmacy_db
```

## Features

### 1. Two-Factor Authentication (2FA)

#### Setup
Users can enable 2FA from their account settings:

```php
// Access 2FA setup page
modules/auth/2fa_setup.php
```

#### How it works:
1. User initiates 2FA setup
2. System generates a secret key
3. User scans QR code with authenticator app (Google Authenticator, Authy, etc.)
4. User verifies with 6-digit code
5. System generates 10 backup codes
6. User saves backup codes in safe location

#### Verification during login:
- After entering username/password, user is prompted for 2FA code
- User can use either authenticator app or backup code
- Failed attempts are logged

#### API Functions:

```php
// Generate 2FA secret
$secret = generate_2fa_secret();

// Generate QR code URL
$qr_url = generate_2fa_qr_code($email, $secret);

// Verify TOTP code
if (verify_totp_code($secret, $code)) {
    // Code is valid
}

// Generate backup codes
$codes = generate_backup_codes(10);

// Verify backup code
if (verify_backup_code($code, $hashed_codes)) {
    // Code is valid
}
```

### 2. Account Lockout Mechanism

#### Configuration
- Maximum failed attempts: 5
- Lockout duration: 15 minutes
- Tracked per user account

#### How it works:
1. Each failed login attempt is recorded
2. After 5 failed attempts, account is locked
3. Locked account cannot login for 15 minutes
4. Lockout is automatically cleared after duration expires
5. Successful login resets failed attempt counter

#### API Functions:

```php
// Check if account is locked
if (is_account_locked($user_id)) {
    // Account is locked
}

// Get remaining lockout time
$remaining = get_lockout_remaining_time($user_id);

// Record failed login
record_failed_login($user_id, $ip_address);

// Reset failed attempts
reset_failed_login_attempts($user_id);
```

### 3. Data Encryption/Decryption

#### Encryption
Uses AES-256-GCM for authenticated encryption:

```php
// Encrypt sensitive data
$encrypted = encrypt_data($sensitive_data);

// Decrypt sensitive data
$decrypted = decrypt_data($encrypted);

// Hash sensitive data (one-way)
$hash = hash_sensitive_data($data);
```

#### Security Features:
- AES-256-GCM cipher
- Random IV for each encryption
- Authentication tag for integrity verification
- Base64 encoding for storage

### 4. CSRF Token Management

#### Implementation
All forms should include CSRF token:

```php
// In HTML form
<?php echo csrf_token_input(); ?>

// Or manually
<input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
```

#### Verification
```php
// Verify CSRF token in POST handler
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Invalid security token');
}
```

#### Features:
- Automatic token generation
- Token expiration after 1 hour
- Secure comparison using hash_equals()
- Regeneration support

### 5. Rate Limiting

#### Configuration
- Default: 10 attempts per 5 minutes per IP
- Customizable per action

#### Implementation
```php
// Check rate limit
if (!check_rate_limit($identifier, $max_attempts, $window)) {
    die('Too many attempts. Please try again later.');
}

// Get remaining attempts
$remaining = get_rate_limit_remaining($identifier);
```

#### Tracked Actions:
- Login attempts
- Password reset requests
- API calls
- File uploads

### 6. Security Audit Logging

#### Logged Events
- User login/logout
- Failed login attempts
- 2FA setup/disable
- Data access and modifications
- Privilege changes
- Suspicious activities

#### API Functions:

```php
// Log access
log_access($user_id, $resource, $action, $details);

// Log security event
log_security_event($user_id, $event_type, $description, $ip_address);

// Log data modification
log_data_modification($user_id, $table, $action, $record_id, $old_values, $new_values);

// Get audit log
$logs = get_security_audit_log($filters, $limit, $offset);

// Export audit log
$csv = export_security_audit_log($filters, 'csv');
```

### 7. Suspicious Activity Detection

#### Detects:
- Multiple failed login attempts
- Login from new IP address
- Rapid successive logins
- Bulk data access
- Off-hours access
- Privilege escalation attempts

#### API Functions:

```php
// Detect suspicious login
$alerts = detect_suspicious_login($user_id, $ip_address);

// Detect suspicious data access
$alerts = detect_suspicious_data_access($user_id);

// Detect privilege escalation
$alerts = detect_privilege_escalation($user_id, $old_role, $new_role);
```

## File Structure

```
config/
├── security_enhanced.php          # Enhanced security functions
└── security.php                   # Original security config

modules/auth/
├── login.php                       # Updated with security features
├── 2fa_setup.php                   # 2FA enrollment page
└── 2fa_verify.php                  # 2FA verification during login

includes/
└── security_audit.php              # Audit logging functions

database/
└── migrations/
    └── 001_add_security_tables.php # Database migration
```

## Database Schema

### two_factor_auth
```sql
- tfa_id (INT, PK)
- user_id (INT, FK)
- tfa_secret (VARCHAR)
- backup_codes (LONGTEXT, JSON)
- tfa_enabled (BOOLEAN)
- enabled_at (TIMESTAMP)
- disabled_at (TIMESTAMP)
- last_verified_at (TIMESTAMP)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### login_attempts
```sql
- attempt_id (INT, PK)
- user_id (INT, FK)
- failed_attempts (INT)
- locked_until (TIMESTAMP)
- last_attempt_ip (VARCHAR)
- last_attempt_time (TIMESTAMP)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### security_audit_log
```sql
- log_id (INT, PK)
- user_id (INT, FK)
- action (VARCHAR)
- resource (VARCHAR)
- record_id (INT)
- details (LONGTEXT)
- ip_address (VARCHAR)
- user_agent (TEXT)
- timestamp (TIMESTAMP)
```

### rate_limits
```sql
- limit_id (INT, PK)
- identifier (VARCHAR)
- ip_address (VARCHAR)
- attempt_time (TIMESTAMP)
```

## Integration Guide

### 1. Update Existing Login Flow

The login.php has been updated to include:
- CSRF token verification
- Rate limiting checks
- Account lockout checks
- 2FA verification flow
- Security event logging

### 2. Add 2FA Setup to User Settings

Add a link to 2FA setup in user account settings:

```php
<a href="../../modules/auth/2fa_setup.php">
    <i class="fas fa-shield-alt"></i> Manage 2FA
</a>
```

### 3. Log Security Events

Add logging to sensitive operations:

```php
// After user modifies data
log_data_modification(
    $user_id,
    'meds',
    'update',
    $med_id,
    json_encode($old_values),
    json_encode($new_values)
);
```

### 4. Monitor Suspicious Activity

Create an admin dashboard to monitor security events:

```php
$filters = ['action' => 'login_failed'];
$logs = get_security_audit_log($filters, 100);

foreach ($logs as $log) {
    echo $log['timestamp'] . ' - ' . $log['E_Username'] . ' - ' . $log['ip_address'];
}
```

## Security Best Practices

### For Administrators

1. **Regular Audits**: Review security audit logs regularly
2. **Monitor Lockouts**: Check for accounts frequently locked
3. **Update Secrets**: Rotate encryption keys periodically
4. **Backup Codes**: Remind users to save backup codes
5. **IP Whitelisting**: Consider IP whitelisting for sensitive operations

### For Users

1. **Enable 2FA**: Always enable 2FA for enhanced security
2. **Save Backup Codes**: Store backup codes in secure location
3. **Update Password**: Change password regularly
4. **Verify IP**: Check login history for unfamiliar IPs
5. **Report Suspicious**: Report suspicious activity immediately

## Troubleshooting

### 2FA Issues

**Problem**: QR code not displaying
- Solution: Ensure Google Charts API is accessible
- Alternative: Manually enter the secret key

**Problem**: TOTP code always invalid
- Solution: Check device time synchronization
- Solution: Verify secret key is correct

**Problem**: Backup codes not working
- Solution: Ensure code format is correct (XXXX-XXXX)
- Solution: Check if code has already been used

### Account Lockout

**Problem**: Account locked after failed attempts
- Solution: Wait 15 minutes for automatic unlock
- Solution: Admin can manually reset via database

**Problem**: Cannot unlock account
- Solution: Check login_attempts table
- Solution: Update locked_until to NULL

### Rate Limiting

**Problem**: "Too many attempts" error
- Solution: Wait for rate limit window to expire
- Solution: Check rate_limits table for cleanup

## Performance Considerations

1. **Audit Log Size**: Archive old logs regularly
2. **Rate Limits Cleanup**: Automatic cleanup of expired entries
3. **Database Indexes**: Ensure indexes are created for performance
4. **Encryption Overhead**: Minimal impact on performance

## Compliance

These security enhancements help meet:
- OWASP Top 10 requirements
- HIPAA security standards (for healthcare)
- PCI DSS requirements (for payment processing)
- GDPR data protection requirements

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review security audit logs
3. Check database migration status
4. Verify all files are in correct locations

## Version History

- **v1.0** (2024): Initial security enhancements
  - 2FA with TOTP and backup codes
  - Account lockout mechanism
  - Data encryption utilities
  - CSRF token management
  - Rate limiting
  - Security audit logging
  - Suspicious activity detection

## License

These security enhancements are part of the PHARMACIA system and follow the same license terms.
