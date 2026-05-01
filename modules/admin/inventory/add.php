<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

if (isset($_POST['add'])) {
    $name     = $conn->real_escape_string($_POST['medname']);
    $qty      = (int)$_POST['qty'];
    $category = $conn->real_escape_string($_POST['cat']);
    $sprice   = (float)$_POST['sp'];
    $location = $conn->real_escape_string($_POST['loc']);

    if (empty($name) || empty($category)) {
        set_flash_message("Operational failure: Required fields missing.", "error");
    } else {
        $sql = "INSERT INTO meds (Med_Name, Med_Qty, Category, Med_Price, Location_Rack) 
                VALUES ('$name', $qty, '$category', $sprice, '$location')";
        
        if ($conn->query($sql)) {
            set_flash_message("Asset '$name' successfully integrated into synchronization.", "success");
            header("Location: view.php");
            exit();
        } else {
            set_flash_message("Integration protocol failed. Verify data integrity.", "error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Registration - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header -->
    <div class="mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Asset Integration</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Register Medicine</h1>
            <p class="text-slate-500 font-medium mt-1">Append new pharmaceutical assets to the global inventory matrix.</p>
        </div>
        <a href="view.php" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
            <i class="fas fa-arrow-left-long mr-2"></i> Return to Matrix
        </a>
    </div>

    <!-- Main Registration Console -->
    <div class="max-w-6xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Data Entry Block -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    
                    <h3 class="text-xl font-black text-slate-900 uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black shadow-lg shadow-blue-200">01</span>
                        Asset Signature
                    </h3>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Official Asset Name</label>
                            <input type="text" name="medname" placeholder="Enter pharmaceutical designation..." required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700 placeholder:text-slate-300">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Classification</label>
                                <select name="cat" required
                                    class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700 appearance-none">
                                    <option value="">Select Category</option>
                                    <option>Tablet</option>
                                    <option>Capsule</option>
                                    <option>Syrup</option>
                                    <option>Injection</option>
                                    <option>Ointment</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Initial Stockpile</label>
                                <input type="number" name="qty" placeholder="0" value="0"
                                    class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Block -->
            <div class="space-y-8">
                <div class="bg-slate-900 p-10 rounded-[3rem] shadow-2xl relative overflow-hidden h-full">
                    <div class="absolute bottom-0 right-0 w-48 h-48 bg-blue-600/10 rounded-full blur-3xl -mb-24 -mr-24"></div>
                    
                    <h3 class="text-xl font-black text-white uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-white/10 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black backdrop-blur">02</span>
                        Valuation
                    </h3>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Target Price (Rs)</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-blue-500 font-extrabold">Rs.</span>
                                <input type="number" step="0.01" name="sp" placeholder="0.00" required
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl pl-14 pr-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-black text-white placeholder:text-slate-600 text-2xl">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Logical Storage</label>
                            <div class="relative">
                                <i class="fas fa-location-arrow absolute left-5 top-1/2 -translate-y-1/2 text-slate-600"></i>
                                <input type="text" name="loc" placeholder="Shelf e.g. B-09"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white placeholder:text-slate-600">
                            </div>
                        </div>

                        <div class="pt-10">
                            <button type="submit" name="add" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-6 rounded-2xl shadow-xl shadow-blue-500/20 transition-all active:scale-95 flex items-center justify-center text-lg">
                                <i class="fas fa-microchip mr-3"></i> Execute Protocol
                            </button>
                            <p class="text-center text-[9px] text-slate-500 font-black uppercase tracking-[0.3em] mt-6">Secure Transaction Encrypted</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>
</body>
</html>
