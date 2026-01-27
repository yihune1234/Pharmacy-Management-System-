<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

if (isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['sname']);
    $add  = $conn->real_escape_string($_POST['sadd']);
    $phno = $conn->real_escape_string($_POST['sphno']);
    $mail = $conn->real_escape_string($_POST['smail']);

    if (empty($name)) {
        set_flash_message("Please enter the supplier company name.", "warning");
    } else {
        $sql = "INSERT INTO suppliers (Sup_Name, Sup_Add, Sup_Phno, Sup_Mail) 
                VALUES ('$name', '$add', '$phno', '$mail')";
        
        if ($conn->query($sql)) {
            set_flash_message("Supplier '$name' registered successfully.", "success");
            header("Location: view.php");
            exit();
        } else {
            set_flash_message("Could not register supplier. Potential duplicate detected.", "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-center lg:text-left">Add Partner</h2>
            <p class="text-slate-500 mt-1 font-medium text-center lg:text-left">Register a new medical supplier to the network.</p>
        </div>
        <a href="view.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-10 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40">
            <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center">
                <span class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center mr-3 text-sm">01</span>
                Company Information
            </h3>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="sname" placeholder="e.g. HealthLogics Inc." required
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Headquarters Address</label>
                    <input type="text" name="sadd" placeholder="Street, City, Country"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone Line</label>
                        <input type="tel" name="sphno" placeholder="+1..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="smail" placeholder="biz@supplier.com"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" name="add" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black py-5 rounded-2xl shadow-xl transition-all active:scale-95 flex items-center justify-center">
                        Register Supplier
                    </button>
                    <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-4">System will assign a unique Supplier ID</p>
                </div>
            </form>
        </div>

        <div class="hidden lg:block relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[2.5rem] flex flex-col items-center justify-center text-white p-12 text-center overflow-hidden">
                <svg class="w-32 h-32 mb-8 opacity-20" fill="currentColor" viewBox="0 0 20 20"><path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.106A1 1 0 003 9v5.924a1 1 0 00.553.894l4.5 2.25a1 1 0 001.447-.894V15.03l-4.5-2.25a1 1 0 01-.553-.894V8.106z"></path></svg>
                <h4 class="text-2xl font-black mb-4">Supplier Ecosystem</h4>
                <p class="text-blue-100 font-medium leading-relaxed">Maintaining accurate supplier records is crucial for tracking stock origin and ensuring quality compliance across your entire medical inventory.</p>
            </div>
        </div>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>