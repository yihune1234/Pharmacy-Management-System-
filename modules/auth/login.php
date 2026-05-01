<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/security_enhanced.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/security_audit.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$login_error = '';
$rate_limit_error = '';

if (isset($_POST['submit'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message("Invalid security token. Please try again.", "error");
    } else {
        $uname = trim($_POST['uname'] ?? '');
        $password = trim($_POST['pwd'] ?? '');
        $ip_address = get_client_ip();

        if ($uname === '' || $password === '') {
            set_flash_message("Please enter both username and password.", "error");
        } else {
            // Check rate limiting
            $rate_limit_key = 'login_attempt_' . md5($ip_address);
            if (!check_rate_limit($rate_limit_key, 10, 300)) {
                $rate_limit_error = 'Too many login attempts. Please try again later.';
                set_flash_message($rate_limit_error, "error");
                log_security_event(0, 'rate_limit_exceeded', 'Login rate limit exceeded from IP: ' . $ip_address, $ip_address);
            } else {
                // Get user by username
                $stmt = $conn->prepare("
                    SELECT e.E_ID, e.E_Fname, e.E_Username, e.E_Password, r.role_name
                    FROM employee e
                    LEFT JOIN roles r ON e.role_id = r.role_id
                    WHERE e.E_Username = ?
                    LIMIT 1
                ");

                if ($stmt) {
                    $stmt->bind_param("s", $uname);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows === 1) {
                        $row = $result->fetch_assoc();
                        $user_id = $row['E_ID'];

                        // Check if account is locked
                        if (is_account_locked($user_id)) {
                            $remaining_time = get_lockout_remaining_time($user_id);
                            $minutes = ceil($remaining_time / 60);
                            set_flash_message("Account locked due to multiple failed login attempts. Please try again in $minutes minutes.", "error");
                            log_security_event($user_id, 'login_attempt_locked_account', 'Login attempt on locked account', $ip_address);
                        } elseif (password_verify($password, $row['E_Password'])) {
                            // Password correct
                            reset_failed_login_attempts($user_id);

                            // Check if 2FA is enabled
                            $tfa_stmt = $conn->prepare("
                                SELECT tfa_enabled FROM two_factor_auth 
                                WHERE user_id = ? AND tfa_enabled = 1
                            ");
                            $tfa_stmt->bind_param("i", $user_id);
                            $tfa_stmt->execute();
                            $tfa_result = $tfa_stmt->get_result();
                            $tfa_enabled = $tfa_result->num_rows > 0;
                            $tfa_stmt->close();

                            if ($tfa_enabled) {
                                // Set pending 2FA state and redirect to 2FA verification
                                $_SESSION['pending_2fa_user_id'] = $user_id;
                                log_security_event($user_id, 'login_2fa_pending', 'User proceeding to 2FA verification', $ip_address);
                                header("Location: 2fa_verify.php");
                                exit();
                            } else {
                                // No 2FA, complete login directly
                                $_SESSION['user'] = $user_id;
                                $_SESSION['username'] = $row['E_Username'];
                                $_SESSION['name'] = $row['E_Fname'];
                                $_SESSION['role'] = strtolower($row['role_name'] ?? 'admin');
                                $_SESSION['last_activity'] = time();

                                set_flash_message("Welcome back, " . htmlspecialchars($row['E_Fname']) . "!", "success");
                                log_security_event($user_id, 'login_success', 'User logged in successfully', $ip_address);

                                switch ($_SESSION['role']) {
                                    case 'admin':
                                        header("Location: ../admin/dashboard.php");
                                        break;
                                    case 'pharmacist':
                                        header("Location: ../pharmacist/dashboard.php");
                                        break;
                                    case 'cashier':
                                        header("Location: ../cashier/dashboard.php");
                                        break;
                                    default:
                                        header("Location: ../admin/dashboard.php");
                                        break;
                                }
                                exit();
                            }
                        } else {
                            // Password incorrect
                            record_failed_login($user_id, $ip_address);
                            set_flash_message("Invalid username or password. Please try again.", "error");
                            log_security_event($user_id, 'login_failed', 'Failed login attempt', $ip_address);
                        }
                    } else {
                        // User not found
                        set_flash_message("Invalid username or password. Please try again.", "error");
                        log_security_event(0, 'login_failed_unknown_user', 'Login attempt with unknown username: ' . $uname, $ip_address);
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Console - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .btn-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        }
    </style>
</head>
<body class="bg-[#f0f4f8] min-h-screen flex items-center justify-center p-6 relative overflow-y-auto overflow-x-hidden">
    
    <!-- Background Accents -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-[480px] relative z-10 transition-all duration-500">
        <!-- Logo Section -->
        <div class="flex flex-col items-center mb-10 group">
            <div class="w-24 h-24 bg-white rounded-3xl shadow-2xl shadow-blue-200/50 flex items-center justify-center mb-6 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                <!-- Replace with your logo image -->
                <img src="../../assets/images/logo.png" alt="Pharma Logo" class="w-16 h-16 object-contain" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063067.png'">
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic">Pharmacy</h1>
            <div class="flex items-center space-x-2 mt-2">
                <span class="h-[1px] w-8 bg-blue-600"></span>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em]">Integrated Intelligence</p>
                <span class="h-[1px] w-8 bg-blue-600"></span>
            </div>
        </div>

        <!-- Login Console -->
        <div class="glass p-10 rounded-[3rem] shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] relative">
            <?php render_flash_message(); ?>
            
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-2">System Authentication</h2>
                <p class="text-slate-500 font-medium text-sm">Synchronize your credentials to gain sector access.</p>
            </div>

            <form method="post" action="" class="space-y-8">
                <?php echo csrf_token_input(); ?>
                
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Authorized Personnel ID</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fas fa-id-card-clip text-lg"></i>
                        </span>
                        <input type="text" name="uname" placeholder="Enter Username" required
                               class="w-full bg-white/50 border border-slate-200/60 rounded-[1.5rem] pl-14 pr-6 py-5 text-slate-900 font-bold placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 focus:bg-white transition-all shadow-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Encryption Key</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fas fa-shield-halved text-lg"></i>
                        </span>
                        <input type="password" name="pwd" placeholder="Type Security Key" required
                               class="w-full bg-white/50 border border-slate-200/60 rounded-[1.5rem] pl-14 pr-6 py-5 text-slate-900 font-bold placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 focus:bg-white transition-all shadow-sm">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" name="submit"
                            class="btn-gradient w-full text-white font-black py-6 rounded-[1.5rem] shadow-2xl shadow-blue-500/20 transform transition-all hover:scale-[1.02] hover:shadow-blue-500/30 active:scale-[0.98] flex items-center justify-center space-x-3 text-lg overflow-hidden relative group">
                        <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        <span class="relative">Execute Access Protocol</span>
                        <i class="fas fa-arrow-right-long relative group-hover:translate-x-2 transition-transform"></i>
                    </button>
                    
                    <div class="flex items-center justify-center mt-8 space-x-4 opacity-50">
                        <div class="h-1 w-1 rounded-full bg-slate-400"></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Secure Terminal Session</p>
                        <div class="h-1 w-1 rounded-full bg-slate-400"></div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Institutional Grade Security • v5.0.2</p>
        </div>
    </div>

</body>
</html>
