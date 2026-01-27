<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['submit'])) {

    $uname = trim($_POST['uname'] ?? '');
    $password = trim($_POST['pwd'] ?? '');

    if ($uname === '' || $password === '') {
        set_flash_message("Please enter both username and password.", "error");
    } else {

        $stmt = $conn->prepare("
            SELECT e.E_ID, e.E_Fname, e.username, e.password, r.role_name
            FROM employee e
            LEFT JOIN roles r ON e.role_id = r.role_id
            WHERE e.username = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $uname);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();

                // Plain password check (upgrade later to password_verify)
                if ($password === $row['password']) {

                    $_SESSION['user'] = $row['E_ID'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['name'] = $row['E_Fname'];
                    $_SESSION['role'] = strtolower($row['role_name'] ?? '');

                    set_flash_message("Welcome back, " . htmlspecialchars($row['E_Fname']) . "!", "success");

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
                            session_destroy();
                            set_flash_message("Your account has no assigned role. Please contact the administrator.", "error");
                            header("Location: login.php");
                            break;
                    }
                    exit();
                }
            }
        }

        set_flash_message("Invalid username or password. Please try again.", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACIA - Secure Access</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-6 antialiased">

<?php render_flash_message(); ?>

<div class="w-full max-w-[440px]">
    <!-- Brand -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 rounded-[1.5rem] shadow-2xl mb-4 transform -rotate-6">
            <span class="text-white text-3xl font-black">+</span>
        </div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic">Pharmacia</h1>
        <p class="text-slate-400 font-bold uppercase tracking-[0.3em] text-[10px] mt-2">Precision Medical Systems</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] p-10 relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>

        <div class="relative z-10">
            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">System Login</h2>
                <p class="text-slate-500 font-medium text-sm">Enter your secure credentials to proceed.</p>
            </div>

            <form method="post" action="" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Username</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" name="uname" placeholder="System ID" required
                               class="w-full bg-slate-50/50 border border-slate-200 rounded-2xl pl-12 pr-6 py-4 text-slate-900 font-semibold placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all"/>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Security Key</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2a3 3 0 01-3 3H6a3 3 0 01-3-3v-2m18-4s-3 7-9 7-9-7-9-7 3-7 9-7 9 7 9 7z"/>
                            </svg>
                        </span>
                        <input type="password" name="pwd" placeholder="••••••••" required
                               class="w-full bg-slate-50/50 border border-slate-200 rounded-2xl pl-12 pr-6 py-4 text-slate-900 font-semibold placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all"/>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" name="submit"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-black font-black py-4 rounded-2xl shadow-xl shadow-slate-200 transform transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center space-x-2">
                        <span>Authorize Access</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-12 text-center">
        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">Institutional Platform v4.0</p>
    </div>
</div>

</body>
</html>
