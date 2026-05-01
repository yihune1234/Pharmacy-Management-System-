<?php
/**
 * 2FA Setup Module
 * Allows users to enable two-factor authentication
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/security_enhanced.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/alerts.php';

// Require authentication
require_auth();

$user_id = $_SESSION['user'];
$user_email = $_SESSION['username'];
$step = $_GET['step'] ?? 'select';
$message = '';
$error = '';

// Check if 2FA is already enabled
$check_stmt = $conn->prepare("
    SELECT tfa_enabled, tfa_secret FROM two_factor_auth 
    WHERE user_id = ?
");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$tfa_record = $check_result->fetch_assoc();
$check_stmt->close();

$tfa_already_enabled = $tfa_record && $tfa_record['tfa_enabled'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'generate_secret') {
            // Generate new secret
            $secret = generate_2fa_secret();
            $_SESSION['temp_2fa_secret'] = $secret;
            $_SESSION['temp_2fa_time'] = time();
            $step = 'verify';
            $message = 'Secret generated. Please scan the QR code with your authenticator app.';
        }
        
        elseif ($action === 'verify_code') {
            // Verify the TOTP code
            $code = trim($_POST['totp_code'] ?? '');
            $secret = $_SESSION['temp_2fa_secret'] ?? '';
            
            if (empty($code)) {
                $error = 'Please enter the verification code.';
            } elseif (empty($secret)) {
                $error = 'Session expired. Please start over.';
                $step = 'select';
            } elseif (!verify_totp_code($secret, $code)) {
                $error = 'Invalid verification code. Please try again.';
            } else {
                // Code verified, generate backup codes
                $backup_codes = generate_backup_codes(10);
                $hashed_codes = hash_backup_codes($backup_codes);
                
                // Save to database
                if ($tfa_record) {
                    // Update existing record
                    $update_stmt = $conn->prepare("
                        UPDATE two_factor_auth 
                        SET tfa_secret = ?, backup_codes = ?, tfa_enabled = 1, enabled_at = NOW()
                        WHERE user_id = ?
                    ");
                    $hashed_codes_json = json_encode($hashed_codes);
                    $update_stmt->bind_param("ssi", $secret, $hashed_codes_json, $user_id);
                } else {
                    // Insert new record
                    $insert_stmt = $conn->prepare("
                        INSERT INTO two_factor_auth (user_id, tfa_secret, backup_codes, tfa_enabled, enabled_at)
                        VALUES (?, ?, ?, 1, NOW())
                    ");
                    $hashed_codes_json = json_encode($hashed_codes);
                    $insert_stmt->bind_param("iss", $user_id, $secret, $hashed_codes_json);
                    $insert_stmt = $update_stmt;
                }
                
                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    $_SESSION['backup_codes'] = $backup_codes;
                    $step = 'backup_codes';
                    $message = '2FA enabled successfully! Save your backup codes in a safe place.';
                    
                    // Log security event
                    log_security_event($user_id, 'tfa_enabled', '2FA has been enabled', get_client_ip());
                } else {
                    $error = 'Failed to save 2FA settings. Please try again.';
                }
            }
        }
        
        elseif ($action === 'disable_2fa') {
            // Disable 2FA
            $password = trim($_POST['password'] ?? '');
            
            if (empty($password)) {
                $error = 'Please enter your password to disable 2FA.';
            } else {
                // Verify password
                $user_stmt = $conn->prepare("
                    SELECT E_Password FROM employee WHERE E_ID = ?
                ");
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user_row = $user_result->fetch_assoc();
                $user_stmt->close();
                
                if (!password_verify($password, $user_row['E_Password'])) {
                    $error = 'Invalid password.';
                } else {
                    // Disable 2FA
                    $disable_stmt = $conn->prepare("
                        UPDATE two_factor_auth 
                        SET tfa_enabled = 0, disabled_at = NOW()
                        WHERE user_id = ?
                    ");
                    $disable_stmt->bind_param("i", $user_id);
                    
                    if ($disable_stmt->execute()) {
                        $disable_stmt->close();
                        $message = '2FA has been disabled.';
                        $step = 'select';
                        
                        // Log security event
                        log_security_event($user_id, 'tfa_disabled', '2FA has been disabled', get_client_ip());
                    } else {
                        $error = 'Failed to disable 2FA. Please try again.';
                    }
                }
            }
        }
    }
}

// Get QR code URL if secret is generated
$qr_code_url = '';
if ($step === 'verify' && isset($_SESSION['temp_2fa_secret'])) {
    $qr_code_url = generate_2fa_qr_code($user_email, $_SESSION['temp_2fa_secret']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Setup - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="javascript:history.back()" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
            <h1 class="text-4xl font-bold text-slate-900">Two-Factor Authentication</h1>
            <p class="text-slate-600 mt-2">Enhance your account security with 2FA</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-start">
                <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-green-900">Success</h3>
                    <p class="text-green-800"><?php echo htmlspecialchars($message); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start">
                <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-red-900">Error</h3>
                    <p class="text-red-800"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Step 1: Select Action -->
        <?php if ($step === 'select'): ?>
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">2FA Status</h2>
                
                <div class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-shield-alt text-blue-600 text-2xl mr-3"></i>
                        <h3 class="text-lg font-semibold text-slate-900">Current Status</h3>
                    </div>
                    <p class="text-slate-700">
                        <?php if ($tfa_already_enabled): ?>
                            <span class="text-green-600 font-semibold">✓ 2FA is enabled</span>
                        <?php else: ?>
                            <span class="text-orange-600 font-semibold">✗ 2FA is not enabled</span>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if (!$tfa_already_enabled): ?>
                    <form method="POST" class="space-y-4">
                        <?php echo csrf_token_input(); ?>
                        <input type="hidden" name="action" value="generate_secret">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                            <i class="fas fa-qrcode mr-2"></i> Enable 2FA
                        </button>
                    </form>
                <?php else: ?>
                    <div class="space-y-4">
                        <p class="text-slate-600 mb-4">Your account is protected with two-factor authentication.</p>
                        <button onclick="document.getElementById('disable-form').style.display='block'" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition">
                            <i class="fas fa-times mr-2"></i> Disable 2FA
                        </button>
                    </div>

                    <!-- Disable 2FA Form (Hidden) -->
                    <form method="POST" id="disable-form" style="display:none;" class="mt-6 p-6 bg-red-50 rounded-lg border border-red-200">
                        <?php echo csrf_token_input(); ?>
                        <input type="hidden" name="action" value="disable_2fa">
                        <h3 class="text-lg font-semibold text-red-900 mb-4">Disable 2FA</h3>
                        <p class="text-red-800 mb-4">Enter your password to confirm:</p>
                        <input type="password" name="password" placeholder="Enter your password" required 
                               class="w-full px-4 py-2 border border-red-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition">
                                Confirm Disable
                            </button>
                            <button type="button" onclick="document.getElementById('disable-form').style.display='none'" class="flex-1 bg-slate-300 hover:bg-slate-400 text-slate-900 font-bold py-2 rounded-lg transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Step 2: Verify Code -->
        <?php if ($step === 'verify'): ?>
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Scan QR Code</h2>
                
                <div class="mb-8 p-6 bg-slate-50 rounded-lg text-center">
                    <p class="text-slate-700 mb-4">Scan this QR code with your authenticator app:</p>
                    <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="2FA QR Code" class="mx-auto w-64 h-64 border-4 border-slate-200 rounded-lg">
                    <p class="text-sm text-slate-600 mt-4">Or enter this secret manually:</p>
                    <code class="block bg-white p-3 rounded border border-slate-200 mt-2 font-mono text-lg tracking-wider">
                        <?php echo htmlspecialchars($_SESSION['temp_2fa_secret'] ?? ''); ?>
                    </code>
                </div>

                <form method="POST" class="space-y-4">
                    <?php echo csrf_token_input(); ?>
                    <input type="hidden" name="action" value="verify_code">
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Enter 6-digit code from your app:</label>
                        <input type="text" name="totp_code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-2xl tracking-widest"
                               autofocus>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i> Verify Code
                    </button>
                </form>

                <form method="POST" class="mt-4">
                    <?php echo csrf_token_input(); ?>
                    <input type="hidden" name="action" value="generate_secret">
                    <button type="submit" class="w-full bg-slate-300 hover:bg-slate-400 text-slate-900 font-bold py-3 rounded-lg transition">
                        <i class="fas fa-redo mr-2"></i> Generate New Secret
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Step 3: Backup Codes -->
        <?php if ($step === 'backup_codes'): ?>
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Save Backup Codes</h2>
                
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important:</strong> Save these backup codes in a safe place. You can use them to access your account if you lose access to your authenticator app.
                    </p>
                </div>

                <div class="bg-slate-50 p-6 rounded-lg mb-6 border border-slate-200">
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($_SESSION['backup_codes'] ?? [] as $code): ?>
                            <code class="bg-white p-3 rounded border border-slate-300 font-mono text-sm">
                                <?php echo htmlspecialchars($code); ?>
                            </code>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button onclick="copyBackupCodes()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        <i class="fas fa-copy mr-2"></i> Copy All Codes
                    </button>
                    <button onclick="downloadBackupCodes()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">
                        <i class="fas fa-download mr-2"></i> Download
                    </button>
                </div>

                <form method="POST" class="mt-6">
                    <?php echo csrf_token_input(); ?>
                    <a href="../../modules/admin/dashboard.php" class="block w-full bg-slate-600 hover:bg-slate-700 text-white font-bold py-3 rounded-lg transition text-center">
                        <i class="fas fa-check mr-2"></i> Complete Setup
                    </a>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function copyBackupCodes() {
            const codes = Array.from(document.querySelectorAll('code')).map(el => el.textContent).join('\n');
            navigator.clipboard.writeText(codes).then(() => {
                alert('Backup codes copied to clipboard!');
            });
        }

        function downloadBackupCodes() {
            const codes = Array.from(document.querySelectorAll('code')).map(el => el.textContent).join('\n');
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(codes));
            element.setAttribute('download', 'backup-codes.txt');
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }
    </script>
</body>
</html>
