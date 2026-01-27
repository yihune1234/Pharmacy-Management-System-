<?php
// Determine the relative path to the pharmacist root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'pharmacist') ? '' : '../';
?>

<!-- Tailwind CSS Base Setup -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style type="text/tailwindcss">
    @layer base {
        body { font-family: 'Inter', sans-serif; @apply bg-slate-50 text-slate-900; }
    }
    .sidenav.mobile-visible { @apply translate-x-0; }
    .dropdown-container.show { @apply block; }
</style>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <div class="sidenav fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800">
        <div class="p-6 flex items-center space-x-3 border-b border-slate-800">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                <span class="text-white font-bold text-xl">+</span>
            </div>
            <h2 class="text-white font-extrabold text-xl tracking-tighter">PHARMACIA</h2>
        </div>
        
        <nav class="p-4 space-y-1 overflow-y-auto max-h-[calc(100vh-80px)]">
            <a href="<?php echo $path; ?>dashboard.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="<?php echo $path; ?>inventory/view.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                <span class="font-medium text-left flex-grow">Inventory</span>
            </a>

            <a href="<?php echo $path; ?>sales/pos1.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group bg-blue-600/10 text-blue-400">
                <span class="font-medium">New Sale</span>
            </a>

            <!-- Customers -->
            <div>
                <button class="dropdown-btn w-full flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                    <span class="font-medium text-left flex-grow">Customers</span>
                    <svg class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="dropdown-container hidden bg-slate-950/50 rounded-lg mt-1 space-y-1 py-1">
                    <a href="<?php echo $path; ?>customers/add.php" class="block px-8 py-2 text-sm hover:text-white">Add New Customer</a>
                    <a href="<?php echo $path; ?>customers/view.php" class="block px-8 py-2 text-sm hover:text-white">View Customers</a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-grow lg:ml-64 transition-all duration-300">
        <!-- Top Nav -->
        <?php
        include $path . "../../config/config.php";
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $sql="SELECT E_FNAME from EMPLOYEE WHERE E_ID='$_SESSION[user]'";
        $result=$conn->query($sql);
        $row=$result->fetch_row();
        $ename=$row[0];
        ?>
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-40">
            <div class="flex items-center">
                <button id="menu-toggle" class="p-2 -ml-2 rounded-lg hover:bg-slate-100 lg:hidden">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800 ml-4 lg:ml-0 hidden sm:block">Pharmacist Portal</h1>
            </div>
            
            <div class="flex items-center space-x-6">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-900"><?php echo $ename; ?></p>
                    <p class="text-xs text-slate-500">Pharmacist</p>
                </div>
                <a href="<?php echo $path; ?>../auth/logout.php" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm">
                    Log Out
                </a>
            </div>
        </header>

        <main class="p-8 pb-12">

<script>
    document.getElementById('menu-toggle').addEventListener('click', function() {
        document.querySelector('.sidenav').classList.toggle('mobile-visible');
    });

    window.addEventListener('click', function(e) {
        if (window.innerWidth < 1024) {
            const sidebar = document.querySelector('.sidenav');
            const toggle = document.getElementById('menu-toggle');
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('mobile-visible');
            }
        }
    });

    var dropdowns = document.getElementsByClassName("dropdown-btn");
    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].addEventListener("click", function() {
            var content = this.nextElementSibling;
            if (content.style.display === "block") {
                content.style.display = "none";
                this.querySelector('svg').style.transform = 'rotate(0deg)';
            } else {
                content.style.display = "block";
                this.querySelector('svg').style.transform = 'rotate(180deg)';
            }
        });
    }
</script>
	