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

<!-- Tailwind CSS Base Setup -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style type="text/tailwindcss">
    @layer base {
        body { font-family: 'Inter', sans-serif; @apply bg-slate-50 text-slate-900; }
    }
    .sidenav.mobile-visible { @apply translate-x-0; }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { @apply bg-slate-100; }
    ::-webkit-scrollbar-thumb { @apply bg-slate-300 rounded-full; }
    ::-webkit-scrollbar-thumb:hover { @apply bg-slate-400; }
</style>

<?php render_flash_message(); ?>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <div class="sidenav fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-400 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800 shadow-2xl">
        <div class="p-8 flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-cash-register text-white"></i>
            </div>
            <div>
                <h2 class="text-white font-black text-xl tracking-tighter leading-none">PHARMACIA</h2>
                <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500 mt-1">Cashier Terminal</p>
            </div>
        </div>
        
        <nav class="px-4 space-y-1 overflow-y-auto max-h-[calc(100vh-120px)] custom-scrollbar">
            <a href="<?php echo $path; ?>dashboard.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'bg-white/10 text-white' : ''; ?>">
                <i class="fas fa-home w-5 h-5 mr-3 text-slate-500 group-hover:text-blue-400"></i>
                <span class="font-semibold text-sm">Dashboard</span>
            </a>
            
            <a href="<?php echo $path; ?>sales/pos1.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group">
                <i class="fas fa-shopping-cart w-5 h-5 mr-3 text-slate-500 group-hover:text-blue-400"></i>
                <span class="font-semibold text-sm">Point of Sale</span>
            </a>

            <div class="pt-4 mt-4 border-t border-slate-800">
                <p class="px-4 text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-2">Patients & Loyalty</p>
                <a href="<?php echo $path; ?>customers/view.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group">
                    <i class="fas fa-users w-5 h-5 mr-3 text-slate-500 group-hover:text-blue-400"></i>
                    <span class="font-semibold text-sm">Patient Registry</span>
                </a>
                <a href="<?php echo $path; ?>customers/add.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group">
                    <i class="fas fa-user-plus w-5 h-5 mr-3 text-slate-500 group-hover:text-blue-400"></i>
                    <span class="font-semibold text-sm">New Profile</span>
                </a>
            </div>

            <div class="pt-4 mt-6">
                <a href="<?php echo $path; ?>sales/pos1.php" class="flex items-center px-4 py-4 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 hover:bg-blue-500 transition-all font-bold">
                    <i class="fas fa-plus mr-3"></i>
                    New Transaction
                </a>
            </div>
        </nav>

        <div class="absolute bottom-0 left-0 w-full p-6 border-t border-slate-800 bg-slate-900">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                    <?php echo strtoupper(substr($_SESSION['name'] ?? 'C', 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Cashier'); ?></div>
                    <div class="text-[9px] text-slate-500 uppercase font-black uppercase tracking-tighter">Authorized Cashier</div>
                </div>
                <a href="<?php echo $path; ?>../auth/logout.php" class="text-slate-500 hover:text-red-500 transition-colors">
                    <i class="fas fa-power-off text-sm"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-grow lg:ml-64 transition-all duration-300">
        <!-- Top Nav -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-40">
            <div class="flex items-center">
                <button id="menu-toggle" class="p-2 -ml-2 rounded-xl hover:bg-slate-100 lg:hidden text-slate-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="ml-4 lg:ml-0">
                    <h1 class="text-sm font-bold text-slate-400 uppercase tracking-widest hidden sm:block">Checkout / <span class="text-slate-900 italic">Terminal Active</span></h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gateway Online</span>
                </div>
            </div>
        </header>

        <main class="p-4 md:p-8 lg:p-12 max-w-7xl mx-auto">

<script>
    document.getElementById('menu-toggle').addEventListener('click', function() {
        document.querySelector('.sidenav').classList.toggle('mobile-visible');
    });

    window.addEventListener('click', function(e) {
        if (window.innerWidth < 1024) {
            const sidebar = document.querySelector('.sidenav');
            const toggle = document.getElementById('menu-toggle');
            if (sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('mobile-visible');
            }
        }
    });
</script>
