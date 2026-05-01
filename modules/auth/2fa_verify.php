<?php
/**
 * 2FA Verification Module
 * Verifies 2FA codes during login
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/security_enhanced.php';
require_once __DIR__ . '/../../includes/alerts.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is in 2FA verification state
if (!isset($_SESSION['pending_2fa_user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['pending_2fa_user_id'];
$error = '';
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $method = $_POST['method'] ?? 'totp';
        
        if ($method === 'totp') {
            // Verify TOTP code
            $code = trim($_POST['totp_code'] ?? '');
            
            if (empty($code)) {
                $error = 'Please enter the verification code.';
            } else {
                // Get user's 2FA secret
                $stmt = $conn->prepare("
                    SELECT tfa_secret FROM two_factor_auth 
                    WHERE user_id = ? AND tfa_enabled = 1
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $tfa_record = $result->fetch_assoc();
                $stmt->close();
                
                if (!$tfa_record) {
                    $error = 'Two-factor authentication not configured.';
                } elseif (verify_totp_code($tfa_record['tfa_secret'], $code)) {
                    // Code verified, complete login
                    complete_2fa_login($user_id);
                } else {
                    $error = 'Invalid verification code. Please try again.';
                    log_security_event($user_id, 'failed_2fa_attempt', 'Failed 2FA verification attempt', get_client_ip());
                }
            }
        }
        
        elseif ($method === 'backup') {
            // Verify backup code
            $code = trim($_POST['backup_code'] ?? '');
            
            if (empty($code)) {
                $error = 'Please enter a backup code.';
            } else {
                // Get user's backup codes
                $stmt = $conn->prepare("
                    SELECT backup_codes FROM two_factor_auth 
                    WHERE user_id = ? AND tfa_enabled = 1
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $tfa_record = $result->fetch_assoc();
                $stmt->close();
                
                if (!$tfa_record) {
                    $error = 'Two-factor authentication not configured.';
                } else {
                    $backup_codes = json_decode($tfa_record['backup_codes'], true);
                    
                    if (verify_backup_code($code, $backup_codes)) {
                        // Remove used backup code
                        $new_backup_codes = array_filter($backup_codes, function($hashed) use ($code) {
                            return !password_verify($code, $hashed);
                        });
                        
                        $update_stmt = $conn->prepare("
                            UPDATE two_factor_auth 
                            SET backup_codes = ?
                            WHERE user_id = ?
                        ");
                        $new_codes_json = json_encode(array_values($new_backup_codes));
                        $update_stmt->bind_param("si", $new_codes_json, $user_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        // Complete login
                        complete_2fa_login($user_id);
                    } else {
                        $error = 'Invalid backup code. Please try again.';
                        log_security_event($user_id, 'failed_backup_code_attempt', 'Failed backup code verification', get_client_ip());
                    }
                }
            }
        }
    }
}

/**
 * Complete the login process after 2FA verification
 */
function complete_2fa_login($user_id) {
    global $conn;
    
    // Get user information
    $stmt = $conn->prepare("
        SELECT e.E_ID, e.E_Fname, e.E_Username, r.role_name
        FROM employee e
        LEFT JOIN roles r ON e.role_id = r.role_id
        WHERE e.E_ID = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        // Set session variables
        $_SESSION['user'] = $user['E_ID'];
        $_SESSION['username'] = $user['E_Username'];
        $_SESSION['name'] = $user['E_Fname'];
        $_SESSION['role'] = strtolower($user['role_name'] ?? 'admin');
        $_SESSION['last_activity'] = time();
        $_SESSION['2fa_verified'] = true;
        
        // Clear pending 2FA state
        unset($_SESSION['pending_2fa_user_id']);
        
        // Reset failed login attempts
        reset_failed_login_attempts($user_id);
        
        // Log successful login
        log_security_event($user_id, 'login_success', 'User logged in successfully with 2FA', get_client_ip());
        
        set_flash_message("Welcome back, " . htmlspecialchars($user['E_Fname']) . "!", "success");
        
        // Redirect based on role
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: ../../modules/admin/dashboard.php");
                break;
            case 'pharmacist':
                header("Location: ../../modules/pharmacist/dashboard.php");
                break;
            case 'cashier':
                header("Location: ../../modules/cashier/dashboard.php");
                break;
            default:
                header("Location: ../../modules/admin/dashboard.php");
                break;
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-blue-600 text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-slate-900">Two-Factor Authentication</h1>
            <p class="text-slate-600 mt-2">Verify your identity to continue</p>
        </div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start">
                <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-red-900">Error</h3>
                    <p class="text-red-800 text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Verification Form -->
        <div class="glass rounded-2xl shadow-xl p-8">
            <form method="POST" id="verification-form" class="space-y-6">
                <?php echo csrf_token_input(); ?>
                
                <!-- Tab Selection -->
                <div class="flex gap-2 mb-6">
                    <button type="button" onclick="switchTab('totp')" id="totp-tab" 
                            class="flex-1 py-2 px-4 rounded-lg font-semibold transition bg-blue-600 text-white">
                        <i class="fas fa-mobile-alt mr-2"></i> Authenticator
                    </button>
                    <button type="button" onclick="switchTab('backup')" id="backup-tab"
                            class="flex-1 py-2 px-4 rounded-lg font-semibold transition bg-slate-200 text-slate-700 hover:bg-slate-300">
                        <i class="fas fa-key mr-2"></i> Backup Code
                    </button>
                </div>

                <!-- TOTP Verification -->
                <div id="totp-section" class="space-y-4">
                    <input type="hidden" name="method" value="totp">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                            Enter 6-digit code from your authenticator app:
                        </label>
                        <input type="text" name="totp_code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" 
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-3xl tracking-widest font-mono"
                               autofocus>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i> Verify Code
                    </button>
                </div>

                <!-- Backup Code Verification -->
                <div id="backup-section" style="display:none;" class="space-y-4">
                    <input type="hidden" name="method" value="backup">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                            Enter one of your backup codes:
                        </label>
                        <input type="text" name="backup_code" placeholder="XXXX-XXXX" 
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-lg tracking-widest font-mono"
                               style="letter-spacing: 0.2em;">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i> Verify Code
                    </button>
                </div>
            </form>

            <!-- Help Text -->
            <div class="mt-6 pt-6 border-t border-slate-200">
                <p class="text-xs text-slate-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    Don't have access to your authenticator app? Use a backup code instead.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center">
            <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            const totpTab = document.getElementById('totp-tab');
            const backupTab = document.getElementById('backup-tab');
            const totpSection = document.getElementById('totp-section');
            const backupSection = document.getElementById('backup-section');
            const form = document.getElementById('verification-form');

            if (tab === 'totp') {
                totpTab.classList.add('bg-blue-600', 'text-white');
                totpTab.classList.remove('bg-slate-200', 'text-slate-700');
                backupTab.classList.remove('bg-blue-600', 'text-white');
                backupTab.classList.add('bg-slate-200', 'text-slate-700');
                totpSection.style.display = 'block';
                backupSection.style.display = 'none';
                form.querySelector('input[name="method"]').value = 'totp';
                form.querySelector('input[name="totp_code"]').focus();
            } else {
                backupTab.classList.add('bg-blue-600', 'text-white');
                backupTab.classList.remove('bg-slate-200', 'text-slate-700');
                totpTab.classList.remove('bg-blue-600', 'text-white');
                totpTab.classList.add('bg-slate-200', 'text-slate-700');
                totpSection.style.display = 'none';
                backupSection.style.display = 'block';
                form.querySelector('input[name="method"]').value = 'backup';
                form.querySelector('input[name="backup_code"]').focus();
            }
        }
    </script>
</body>
</html>
