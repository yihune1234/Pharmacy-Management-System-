<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

// Get medicine ID from URL
$med_id = $_GET['id'] ?? 0;

if ($med_id == 0) {
    set_flash_message("Invalid medicine ID.", "error");
    header("Location: view.php");
    exit();
}

// Get medicine details
$sql = "SELECT * FROM meds WHERE Med_ID = $med_id";
$result = $conn->query($sql);
$medicine = $result->fetch_assoc();

if (!$medicine) {
    set_flash_message("Medicine not found.", "error");
    header("Location: view.php");
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $name     = $conn->real_escape_string($_POST['medname']);
    $qty      = (int)$_POST['qty'];
    $category = $conn->real_escape_string($_POST['cat']);
    $sprice   = (float)$_POST['sp'];
    $location = $conn->real_escape_string($_POST['loc']);
    $barcode  = $conn->real_escape_string($_POST['barcode'] ?? '');
    $min_stock = (int)($_POST['min_stock'] ?? 10);

    if (empty($name) || empty($category)) {
        set_flash_message("Please fill in all required fields.", "error");
    } else {
        // Check if barcode already exists (if provided and different from current)
        if (!empty($barcode) && $barcode !== $medicine['Barcode']) {
            $check_sql = "SELECT Med_ID FROM meds WHERE Barcode = '$barcode' AND Med_ID != $med_id";
            $check_result = $conn->query($check_sql);
            if ($check_result && $check_result->num_rows > 0) {
                set_flash_message("Barcode already exists in the system.", "error");
            } else {
                $sql = "UPDATE meds SET 
                        Med_Name = '$name', 
                        Med_Qty = $qty, 
                        Category = '$category', 
                        Med_Price = $sprice, 
                        Location_Rack = '$location',
                        Barcode = " . (empty($barcode) ? "NULL" : "'$barcode'") . ",
                        Min_Stock_Level = $min_stock,
                        Updated_At = CURRENT_TIMESTAMP
                        WHERE Med_ID = $med_id";
                
                if ($conn->query($sql)) {
                    set_flash_message("Medicine '$name' updated successfully!", "success");
                    header("Location: view.php");
                    exit();
                } else {
                    set_flash_message("Something went wrong. Please check your data and try again.", "error");
                }
            }
        } else {
            $sql = "UPDATE meds SET 
                    Med_Name = '$name', 
                    Med_Qty = $qty, 
                    Category = '$category', 
                    Med_Price = $sprice, 
                    Location_Rack = '$location',
                    Barcode = " . (empty($barcode) ? "NULL" : "'$barcode'") . ",
                    Min_Stock_Level = $min_stock,
                    Updated_At = CURRENT_TIMESTAMP
                    WHERE Med_ID = $med_id";
            
            if ($conn->query($sql)) {
                set_flash_message("Medicine '$name' updated successfully!", "success");
                header("Location: view.php");
                exit();
            } else {
                set_flash_message("Something went wrong. Please check your data and try again.", "error");
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
    <title>Edit Medicine - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Medicine</h2>
            <p class="text-slate-500 mt-1 font-medium">Update medicine information and inventory details.</p>
        </div>
        <a href="view.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="max-w-5xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $med_id; ?>" method="post" class="space-y-8">
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
                                <input type="text" name="medname" value="<?php echo htmlspecialchars($medicine['Med_Name']); ?>" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Barcode (Optional)</label>
                                    <input type="text" name="barcode" value="<?php echo htmlspecialchars($medicine['Barcode'] ?? ''); ?>" placeholder="e.g. 1234567890123" maxlength="50"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                                    <p class="text-xs text-slate-400 mt-1">Unique barcode for scanning</p>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Min Stock Level</label>
                                    <input type="number" name="min_stock" value="<?php echo htmlspecialchars($medicine['Min_Stock_Level'] ?? 10); ?>" min="1" max="1000"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                                    <p class="text-xs text-slate-400 mt-1">Alert when stock falls below this</p>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Stock Category <span class="text-rose-500">*</span></label>
                                <select name="cat" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                                    <option value="">Select Category</option>
                                    <option value="Tablet" <?php echo $medicine['Category'] == 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                                    <option value="Capsule" <?php echo $medicine['Category'] == 'Capsule' ? 'selected' : ''; ?>>Capsule</option>
                                    <option value="Syrup" <?php echo $medicine['Category'] == 'Syrup' ? 'selected' : ''; ?>>Syrup</option>
                                    <option value="Injection" <?php echo $medicine['Category'] == 'Injection' ? 'selected' : ''; ?>>Injection</option>
                                    <option value="Ointment" <?php echo $medicine['Category'] == 'Ointment' ? 'selected' : ''; ?>>Ointment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Details -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/40">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                            <span class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mr-3 text-sm">02</span>
                            Stock & Pricing
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Current Quantity</label>
                                <input type="number" name="qty" value="<?php echo htmlspecialchars($medicine['Med_Qty']); ?>" min="0"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Selling Price (Rs)</label>
                                <input type="number" name="sp" value="<?php echo htmlspecialchars($medicine['Med_Price']); ?>" step="0.01" min="0" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Storage Location</label>
                                <input type="text" name="loc" value="<?php echo htmlspecialchars($medicine['Location_Rack']); ?>" placeholder="e.g. A1-B2"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" name="update" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Update Medicine
                </button>
            </div>
        </form>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
