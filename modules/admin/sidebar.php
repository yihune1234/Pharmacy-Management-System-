<?php
// Determine the relative path to the admin root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'admin') ? '' : '../';

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get dashboard statistics for sidebar badges
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'] ?? 0;
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'] ?? 0;
$total_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'] ?? 0;

// Low stock check
$low_stock_result = $conn->query("SELECT COUNT(*) as count FROM meds WHERE Med_Qty <= 10");
$low_stock = $low_stock_result->fetch_assoc()['count'] ?? 0;
?>

<!-- Tailwind CSS & Google Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { font-family: 'Outfit', sans-serif; }
    
    .glass-effect {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    
    .sidebar-active {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        color: white !important;
    }
    
    .nav-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        margin-bottom: 4px;
    }
    
    .nav-item:hover:not(.sidebar-active) {
        background: rgba(59, 130, 246, 0.05);
        transform: translateX(4px);
    }
    
    .nav-item i { transition: transform 0.3s ease; }
    .nav-item:hover i { transform: scale(1.1); }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    
    @keyframes slow-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.05); }
    }
    .alert-pulse { animation: slow-pulse 2s infinite; }
</style>

<div class="flex h-screen bg-[#f8fafc] overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200/60 transition-transform duration-300 transform -translate-x-full lg:translate-x-0 glass-effect">
        <div class="flex flex-col h-full">
            <!-- Brand Logo -->
            <div class="p-8">
                <div class="flex items-center space-x-3 group cursor-pointer" onclick="location.href='<?php echo $path; ?>dashboard.php'">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-200 transform group-hover:rotate-6 transition-transform">
                        <i class="fas fa-prescription text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none">PHARMACIA</h1>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">Medical System</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 pb-4 overflow-y-auto custom-scrollbar">
                <div class="space-y-8">
                    <!-- Dashboard Section -->
                    <div>
                        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Intelligence</p>
                        <div class="space-y-1">
                            <a href="<?php echo $path; ?>dashboard.php" class="nav-item flex items-center space-x-3 px-4 py-3 text-slate-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'sidebar-active' : ''; ?>">
                                <i class="fas fa-home-alt text-lg"></i>
                                <span class="font-semibold text-sm">Dashboard</span>
                            </a>
                            <a href="<?php echo $path; ?>sales/pos1.php" class="nav-item flex items-center space-x-3 px-4 py-3 text-slate-600">
                                <i class="fas fa-cash-register text-lg"></i>
                                <span class="font-semibold text-sm">Point of Sale</span>
                            </a>
                        </div>
                    </div>

                    <!-- Operations Section -->
                    <div>
                        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Operations</p>
                        <div class="space-y-1">
                            <a href="<?php echo $path; ?>inventory/view.php" class="nav-item flex items-center justify-between px-4 py-3 text-slate-600">
                                <span class="flex items-center space-x-3">
                                    <i class="fas fa-boxes-stacked text-lg text-purple-600"></i>
                                    <span class="font-semibold text-sm text-slate-700">Stock Inventory</span>
                                </span>
                                <?php if($low_stock > 0): ?>
                                    <span class="bg-rose-100 text-rose-600 text-[10px] font-black px-2 py-0.5 rounded-full border border-rose-200 alert-pulse"><?php echo $low_stock; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo $path; ?>purchases/view_new.php" class="nav-item flex items-center space-x-3 px-4 py-3 text-slate-600">
                                <i class="fas fa-cart-flatbed text-lg text-indigo-500"></i>
                                <span class="font-semibold text-sm text-slate-700">Procurement</span>
                            </a>
                        </div>
                    </div>

                    <!-- Directory Section -->
                    <div>
                        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Core Directory</p>
                        <div class="space-y-1">
                            <a href="<?php echo $path; ?>customers/view_new.php" class="nav-item flex items-center justify-between px-4 py-3 text-slate-600">
                                <span class="flex items-center space-x-3">
                                    <i class="fas fa-fingerprint text-lg text-emerald-500"></i>
                                    <span class="font-semibold text-sm text-slate-700">Patient Database</span>
                                </span>
                                <span class="text-[10px] font-black text-slate-400"><?php echo $total_customers; ?></span>
                            </a>
                            <a href="<?php echo $path; ?>suppliers/view_new.php" class="nav-item flex items-center space-x-3 px-4 py-3 text-slate-600">
                                <i class="fas fa-handshake-angle text-lg text-amber-500"></i>
                                <span class="font-semibold text-sm text-slate-700">Partner Network</span>
                            </a>
                            <a href="<?php echo $path; ?>employees/view_new.php" class="nav-item flex items-center space-x-3 px-4 py-3 text-slate-600">
                                <i class="fas fa-id-badge text-lg text-blue-500"></i>
                                <span class="font-semibold text-sm text-slate-700">Staff Management</span>
                            </a>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div>
                        <p class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Analysis</p>
                        <div class="space-y-1">
                            <a href="<?php echo $path; ?>reports/reports_dashboard.php" class="nav-item flex items-center space-x-3 px-4 py-3 text-slate-600">
                                <i class="fas fa-chart-line text-lg text-rose-500"></i>
                                <span class="font-semibold text-sm text-slate-700">Visual Insights</span>
                            </a>
                            <a href="<?php echo $path; ?>alerts/alerts.php" class="nav-item flex items-center justify-between px-4 py-3 text-slate-600">
                                <span class="flex items-center space-x-3">
                                    <i class="fas fa-shield-virus text-lg text-orange-500"></i>
                                    <span class="font-semibold text-sm text-slate-700">Security Alerts</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- User Status Card -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/60 flex items-center space-x-3">
                    <div class="relative">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold">
                            <?php echo strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Chief Administrator</p>
                    </div>
                    <a href="<?php echo $path; ?>../auth/logout.php" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <i class="fas fa-power-off text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 lg:ml-72 flex flex-col min-h-screen">
        <!-- Top Navigation Bar -->
        <header class="h-20 bg-white/70 glass-effect border-b border-slate-200/60 flex items-center justify-between px-8 sticky top-0 z-40">
            <!-- Left Header -->
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="lg:hidden w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="hidden sm:block">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Application</h2>
                    <h1 class="text-lg font-bold text-slate-800 tracking-tight leading-none">Command Center</h1>
                </div>
            </div>

            <!-- Global Search -->
            <div class="hidden md:flex flex-1 max-w-lg mx-12">
                <div class="relative w-full group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                        <i class="fas fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" placeholder="Global search console..." 
                           class="w-full bg-slate-100/50 border border-slate-200/60 rounded-2xl pl-11 pr-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <!-- Right Header -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <button class="w-10 h-10 rounded-xl hover:bg-slate-100 flex items-center justify-center text-slate-500 relative transition-all">
                        <i class="fas fa-plus-square"></i>
                    </button>
                    <button class="w-10 h-10 rounded-xl hover:bg-slate-100 flex items-center justify-center text-slate-500 relative transition-all">
                        <i class="fas fa-envelope-open"></i>
                    </button>
                    <button class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-200 transition-all hover:scale-105">
                        <i class="fas fa-gear"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-1 p-8 overflow-y-auto w-full max-w-[1600px] mx-auto">

<script>
    // Sidebar Toggle
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

    // Sub-menu animations could be added here
</script>
<?php render_flash_message(); ?>
