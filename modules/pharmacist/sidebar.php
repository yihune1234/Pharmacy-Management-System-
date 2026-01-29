<?php
// Determine the relative path to the pharmacist root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'pharmacist') ? '' : '../';

require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');
?>

<!-- Tailwind CSS Base Setup -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    <div class="sidenav fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 text-slate-400 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800 shadow-2xl">
        <div class="p-8 flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <span class="text-white font-black text-xl">+</span>
            </div>
            <div>
                <h2 class="text-white font-black text-xl tracking-tighter leading-none">PHARMACIA</h2>
                <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500 mt-1">Pharmacist</p>
            </div>
        </div>
        
        <nav class="px-4 space-y-1 overflow-y-auto max-h-[calc(100vh-120px)] custom-scrollbar">
            <a href="<?php echo $path; ?>dashboard.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-500 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="font-semibold text-sm">Dashboard</span>
            </a>
            
            <a href="<?php echo $path; ?>inventory/view.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group">
                <svg class="w-5 h-5 mr-3 text-slate-500 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="font-semibold text-sm">Inventory Tracking</span>
            </a>

            <div class="pt-4 mt-4 border-t border-slate-800">
                <p class="px-4 text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-2">Customer Relations</p>
                <button class="dropdown-btn w-full flex items-center px-4 py-3 rounded-xl hover:bg-white/5 hover:text-white transition-all group">
                    <svg class="w-5 h-5 mr-3 text-slate-500 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="font-semibold text-sm text-left flex-grow">Customers</span>
                    <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="dropdown-container hidden bg-black/20 rounded-xl mt-1 py-1 mx-2">
                    <a href="<?php echo $path; ?>customers/add.php" class="block px-8 py-2 text-xs font-medium hover:text-white hover:translate-x-1 transition-all">New Profile</a>
                    <a href="<?php echo $path; ?>customers/view.php" class="block px-8 py-2 text-xs font-medium hover:text-white hover:translate-x-1 transition-all">Member List</a>
                </div>
            </div>

            <div class="pt-4 mt-6">
                <a href="<?php echo $path; ?>sales/pos1.php" class="flex items-center px-4 py-4 rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 transition-all font-bold">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Process New Sale
                </a>
            </div>
        </nav>

        <div class="absolute bottom-0 left-0 w-full p-6 border-t border-slate-800 bg-slate-950">
            <?php
            include $path . "../../config/config.php";
            if (session_status() == PHP_SESSION_NONE) { session_start(); }
            $sql="SELECT E_FNAME from EMPLOYEE WHERE E_ID='$_SESSION[user]'";
            $result=$conn->query($sql);
            $row=$result->fetch_row();
            $ename=$row[0];
            ?>
            <div class="px-4 py-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                        <?php echo strtoupper(substr($ename, 0, 1)); ?>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($ename); ?></div>
                        <div class="text-xs text-slate-500">Pharmacist</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <a href="<?php echo $path; ?>change_password.php" class="block text-xs text-blue-600 hover:text-blue-800 font-medium">
                        🔐 Change Password
                    </a>
                    <a href="<?php echo $path; ?>../auth/logout.php" class="block text-xs text-red-600 hover:text-red-800 font-medium">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-grow lg:ml-64 transition-all duration-300">
        <!-- Top Nav -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-40">
            <div class="flex items-center">
                <button id="menu-toggle" class="p-2 -ml-2 rounded-xl hover:bg-slate-100 lg:hidden text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="ml-4 lg:ml-0">
                    <h1 class="text-sm font-bold text-slate-400 uppercase tracking-widest hidden sm:block">Healthcare / <span class="text-slate-900">Dispensing Terminal</span></h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative hidden md:block">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" placeholder="Quick Find..." class="bg-slate-100 border-none rounded-2xl py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-emerald-500 w-64 transition-all">
                </div>
                <div class="h-10 w-[1px] bg-slate-200 mx-2 hidden sm:block"></div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">System Online</span>
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

    var dropdowns = document.getElementsByClassName("dropdown-btn");
    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].addEventListener("click", function() {
            var content = this.nextElementSibling;
            var icon = this.querySelector('svg:last-child');
            if (content.style.display === "block") {
                content.style.display = "none";
                icon.style.transform = 'rotate(0deg)';
                this.classList.remove('text-white', 'bg-white/5');
            } else {
                content.style.display = "block";
                icon.style.transform = 'rotate(180deg)';
                this.classList.add('text-white', 'bg-white/5');
            }
        });
    }
</script>

	