<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

if (isset($_POST['add'])) {
    $name     = $conn->real_escape_string($_POST['medname']);
    $qty      = (int)$_POST['qty'];
    $category = $conn->real_escape_string($_POST['cat']);
    $sprice   = (float)$_POST['sp'];
    $location = $conn->real_escape_string($_POST['loc']);

    if (empty($name) || empty($category)) {
        set_flash_message("Please fill in all required fields.", "error");
    } else {
        $sql = "INSERT INTO meds (Med_Name, Med_Qty, Category, Med_Price, Location_Rack) 
                VALUES ('$name', $qty, '$category', $sprice, '$location')";
        
        if ($conn->query($sql)) {
            set_flash_message("Medicine '$name' added successfully!", "success");
            header("Location: view.php");
            exit();
        } else {
            set_flash_message("Something went wrong. Please check your data and try again.", "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Register Product</h2>
            <p class="text-slate-500 mt-1 font-medium">Add a new pharmaceutical item to your inventory.</p>
        </div>
        <a href="view.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="max-w-5xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/40">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                            <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm">01</span>
                            Primary Information
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Medicine Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="medname" placeholder="e.g. Paracetamol 500mg" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Stock Category <span class="text-rose-500">*</span></label>
                                    <select name="cat" required
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                                        <option value="">Select Category</option>
                                        <option>Tablet</option>
                                        <option>Capsule</option>
                                        <option>Syrup</option>
                                        <option>Injection</option>
                                        <option>Ointment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Initial Quantity</label>
                                    <input type="number" name="qty" placeholder="0" value="0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Details -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/40 h-full">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                            <span class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mr-3 text-sm">02</span>
                            Pricing & Storage
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Unit Price (Rs)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="sp" placeholder="0.00" required
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-black text-slate-700">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Storage Location</label>
                                <input type="text" name="loc" placeholder="Shelf A-1"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                        </div>

                        <div class="mt-12">
                            <button type="submit" name="add" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Save Medicine
                            </button>
                            <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-4">Medicine ID will be auto-generated</p>
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

    <?php $conn->close(); ?>
</body>
</html>

