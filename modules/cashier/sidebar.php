<?php
// Determine the relative path to the cashier root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'cashier') ? '' : '../';

require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Validate cashier access
require_cashier();
validate_role_area('cashier');
?>

<!-- Tailwind CSS & Google Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../../assets/css/design-system.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style type="text/tailwindcss">
    @layer components {
        .nav-link { @apply flex items-center space-x-3 px-5 py-3.5 rounded-2xl text-slate-400 font-semibold transition-all duration-300 hover:bg-white/5 hover:text-white hover:translate-x-1; }
        .nav-link-active { @apply bg-blue-600 text-white shadow-lg shadow-blue-500/20 translate-x-1; }
    }
</style>

<?php render_flash_message(); ?>

<div class="flex h-screen bg-[#f8fafc] overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 border-r border-slate-800 transition-transform duration-300 transform -translate-x-full lg:translate-x-0">
        <div class="flex flex-col h-full">
            <!-- Brand Logo -->
            <div class="p-8">
                <div class="flex items-center space-x-3 group cursor-pointer" onclick="location.href='<?php echo $path; ?>dashboard.php'">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/20 transform group-hover:rotate-6 transition-transform">
                        <i class="fas fa-cash-register text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-white tracking-tight leading-none">PHARMACIA</h1>
                        <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">Cashier Terminal</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 space-y-8 overflow-y-auto">
                <div>
                    <p class="px-5 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Daily Operations</p>
                    <div class="space-y-1">
                        <a href="<?php echo $path; ?>dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'nav-link-active' : ''; ?>">
                            <i class="fas fa-home text-lg"></i>
                            <span class="text-sm">Dashboard</span>
                        </a>
                        <a href="<?php echo $path; ?>sales/pos1.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'sales') !== false) ? 'nav-link-active' : ''; ?>">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <span class="text-sm">Point of Sale</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-5 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Patient Registry</p>
                    <div class="space-y-1">
                        <a href="<?php echo $path; ?>customers/view.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'view.php' && strpos($_SERVER['PHP_SELF'], 'customers') !== false) ? 'nav-link-active' : ''; ?>">
                            <i class="fas fa-users text-lg"></i>
                            <span class="text-sm">View Patients</span>
                        </a>
                        <a href="<?php echo $path; ?>customers/add.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add.php' && strpos($_SERVER['PHP_SELF'], 'customers') !== false) ? 'nav-link-active' : ''; ?>">
                            <i class="fas fa-user-plus text-lg"></i>
                            <span class="text-sm">Register Patient</span>
                        </a>
                    </div>
                </div>

                <div class="mt-8 px-4">
                    <a href="<?php echo $path; ?>sales/pos1.php" class="btn-primary btn-blue w-full italic">
                        <i class="fas fa-plus mr-3"></i> New Bill
                    </a>
                </div>
            </nav>

            <!-- User Status Card -->
            <div class="p-6 border-t border-slate-800 bg-slate-900/50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center font-bold border border-blue-500/20">
                        <?php echo strtoupper(substr($_SESSION['name'] ?? 'C', 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Cashier'); ?></p>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">Authorized Clerk</p>
                    </div>
                    <a href="<?php echo $path; ?>../auth/logout.php" class="text-slate-500 hover:text-rose-500 transition-colors">
                        <i class="fas fa-power-off text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 lg:ml-72 flex flex-col min-h-screen transition-all duration-300">
        <!-- Top Navigation Bar -->
        <header class="h-20 bg-white/70 glass-effect border-b border-slate-200/60 flex items-center justify-between px-8 sticky top-0 z-40">
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="lg:hidden w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Terminal Status</h2>
                    <h1 class="text-lg font-bold text-slate-800 tracking-tight leading-none italic">Active Session</h1>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gateway Ready</span>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-1 p-8 overflow-y-auto w-full max-w-[1600px] mx-auto animate-entrance">

<script>
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    toggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('-translate-x-full');
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.add('-translate-x-full');
        }
    });
</script>
