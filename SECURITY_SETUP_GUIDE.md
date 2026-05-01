# PHARMACIA Security Setup Guide

Quick start guide for implementing security enhancements.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- OpenSSL extension enabled
- GD extension enabled (for QR code generation)

## Installation Steps

### Step 1: Run Database Migration

```bash
cd database/migrations
php 001_add_security_tables.php
```

Expected output:
```
Running migration: 001_add_security_tables
Timestamp: 2024-01-15 10:30:45

Creating two_factor_auth table...
✓ two_factor_auth table created successfully
Creating login_attempts table...
✓ login_attempts table created successfully
Creating security_audit_log table...
✓ security_audit_log table created successfully
Creating rate_limits table...
✓ rate_limits table created successfully

Migration Summary
============================================================
Tables created successfully: 4
Errors encountered: 0

Migration completed at: 2024-01-15 10:30:45
Migration record saved to database.
```

### Step 2: Verify Installation

Check that all files are in place:

```
✓ config/security_enhanced.php
✓ modules/auth/2fa_setup.php
✓ modules/auth/2fa_verify.php
✓ includes/security_audit.php
✓ database/migrations/001_add_security_tables.php
✓ Updated modules/auth/login.php
```

### Step 3: Test Login Flow

1. Navigate to login page: `http://localhost/modules/auth/login.php`
2. Enter credentials
3. Verify CSRF token is present in form
4. Check rate limiting works (try 11 failed attempts)
5. Verify account lockout after 5 failed attempts

### Step 4: Enable 2FA for Admin User

1. Login as admin
2. Navigate to: `http://localhost/modules/auth/2fa_setup.php`
3. Click "Enable 2FA"
4. Scan QR code with authenticator app
5. Enter 6-digit code
6. Save backup codes

### Step 5: Test 2FA Login

1. Logout
2. Login with username/password
3. Verify redirected to 2FA verification page
4. Enter code from authenticator app
5. Verify successful login

## Configuration

### Environment Variables

Add to `.env` file:

```env
# Encryption key (optional, uses default if not set)
ENCRYPTION_KEY=your_secure_encryption_key_here

# Database configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=pharmacy_db
DB_PORT=3306
```

### Security Settings

Edit `config/security_enhanced.php` to customize:

```php
// 2FA Settings
define('2FA_WINDOW', 1);  // Time window for TOTP verification

// Account Lockout Settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900);  // 15 minutes in seconds

// Rate Limiting
define('RATE_LIMIT_ATTEMPTS', 10);
define('RATE_LIMIT_WINDOW', 300);  // 5 minutes in seconds

// CSRF Token
define('CSRF_TOKEN_EXPIRY', 3600);  // 1 hour in seconds
```

## Usage Examples

### Enable 2FA for a User

```php
<?php
require_once 'config/config.php';
require_once 'config/security_enhanced.php';

$user_id = 1;
$user_email = 'admin@pharmacy.com';

// Generate secret
$secret = generate_2fa_secret();

// Generate QR code
$qr_url = generate_2fa_qr_code($user_email, $secret);

// Save to database
$stmt = $conn->prepare("
    INSERT INTO two_factor_auth (user_id, tfa_secret, tfa_enabled)
    VALUES (?, ?, 1)
");
$stmt->bind_param("is", $user_id, $secret);
$stmt->execute();
?>
```

### Check Account Lockout Status

```php
<?php
require_once 'config/config.php';
require_once 'config/security_enhanced.php';

$user_id = 1;

if (is_account_locked($user_id)) {
    $remaining = get_lockout_remaining_time($user_id);
    echo "Account locked for " . ceil($remaining / 60) . " more minutes";
} else {
    echo "Account is not locked";
}
?>
```

### View Security Audit Log

```php
<?php
require_once 'config/config.php';
require_once 'includes/security_audit.php';

// Get all login failures in last 24 hours
$filters = [
    'action' => 'login_failed',
    'date_from' => date('Y-m-d H:i:s', time() - 86400)
];

$logs = get_security_audit_log($filters, 100);

foreach ($logs as $log) {
    echo $log['timestamp'] . ' - ' . $log['E_Username'] . ' - ' . $log['ip_address'] . "\n";
}
?>
```

### Export Audit Log

```php
<?php
require_once 'config/config.php';
require_once 'includes/security_audit.php';

$filters = ['date_from' => date('Y-m-d', time() - 7)];
$csv = export_security_audit_log($filters, 'csv');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit_log.csv"');
echo $csv;
?>
```

## Admin Dashboard Integration

### Add Security Monitoring Widget

```php
<?php
require_once 'includes/security_audit.php';

// Get recent security events
$recent_events = get_security_audit_log([], 10);

// Get failed login attempts
$failed_logins = get_all_failed_login_attempts(10);

// Get suspicious activities
$suspicious = [];
foreach ($failed_logins as $attempt) {
    if ($attempt['failed_attempts'] >= 3) {
        $suspicious[] = $attempt;
    }
}
?>

<div class="security-widget">
    <h3>Security Status</h3>
    
    <div class="alert-box">
        <p>Suspicious Accounts: <?php echo count($suspicious); ?></p>
        <p>Failed Logins (24h): <?php echo count($failed_logins); ?></p>
    </div>
    
    <h4>Recent Events</h4>
    <table>
        <tr>
            <th>Time</th>
            <th>User</th>
            <th>Action</th>
            <th>IP</th>
        </tr>
        <?php foreach ($recent_events as $event): ?>
        <tr>
            <td><?php echo $event['timestamp']; ?></td>
            <td><?php echo $event['E_Username']; ?></td>
            <td><?php echo $event['action']; ?></td>
            <td><?php echo $event['ip_address']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
```

## Troubleshooting

### Database Migration Fails

**Error**: "Table already exists"
- Solution: Tables already created, safe to ignore

**Error**: "Foreign key constraint fails"
- Solution: Ensure employee table exists
- Solution: Run database/install.php first

### 2FA QR Code Not Displaying

**Error**: "Image not found"
- Solution: Check internet connection (uses Google Charts API)
- Solution: Use manual secret entry instead

**Error**: "Invalid secret"
- Solution: Regenerate secret
- Solution: Clear browser cache

### Login Rate Limiting Issues

**Error**: "Too many attempts" on first login
- Solution: Check rate_limits table for old entries
- Solution: Clear rate_limits table: `DELETE FROM rate_limits WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR);`

### CSRF Token Errors

**Error**: "Invalid security token"
- Solution: Clear browser cookies
- Solution: Check session is enabled
- Solution: Verify csrf_token_input() is in form

## Security Checklist

- [ ] Database migration completed
- [ ] All files in correct locations
- [ ] Login page includes CSRF token
- [ ] 2FA setup page accessible
- [ ] Rate limiting working
- [ ] Account lockout working
- [ ] Audit logging working
- [ ] Admin can view security logs
- [ ] Backup codes saved by users
- [ ] HTTPS enabled in production

## Performance Optimization

### Database Indexes

Verify indexes are created:

```sql
-- Check two_factor_auth indexes
SHOW INDEX FROM two_factor_auth;

-- Check login_attempts indexes
SHOW INDEX FROM login_attempts;

-- Check security_audit_log indexes
SHOW INDEX FROM security_audit_log;

-- Check rate_limits indexes
SHOW INDEX FROM rate_limits;
```

### Cleanup Old Data

```sql
-- Archive old audit logs (keep last 90 days)
DELETE FROM security_audit_log 
WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Clean up old rate limit entries
DELETE FROM rate_limits 
WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 DAY);
```

## Next Steps

1. **User Training**: Educate users about 2FA
2. **Monitoring**: Set up alerts for suspicious activity
3. **Backup**: Regular database backups
4. **Updates**: Keep PHP and MySQL updated
5. **Audit**: Regular security audits

## Support Resources

- Security Enhancements Documentation: `SECURITY_ENHANCEMENTS.md`
- System Guide: `SYSTEM_GUIDE.md`
- System Flows: `SYSTEM_FLOWS.md`

## Version

- Current Version: 1.0
- Last Updated: 2024
- Compatibility: PHP 7.4+, MySQL 5.7+
