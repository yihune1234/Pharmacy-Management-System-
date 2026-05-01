<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

// Get suppliers for dropdown
$suppliers = $conn->query("SELECT Sup_ID, Sup_Name FROM suppliers ORDER BY Sup_Name");

// Get medicines for dropdown
$medicines = $conn->query("SELECT Med_ID, Med_Name FROM meds ORDER BY Med_Name");

// Handle form submission
if (isset($_POST['add_purchase'])) {
    $supplier_id = (int)$_POST['supplier_id'];
    $medicine_id = (int)$_POST['medicine_id'];
    $quantity = (int)$_POST['quantity'];
    $cost_price = (float)$_POST['cost_price'];
    $purchase_date = $_POST['purchase_date'];
    $mfg_date = $_POST['mfg_date'] ?? null;
    $exp_date = $_POST['exp_date'] ?? null;

    // Validation
    if ($supplier_id == 0 || $medicine_id == 0 || $quantity <= 0 || $cost_price <= 0) {
        set_flash_message("Please fill in all required fields with valid values.", "error");
    } else {
        // Insert purchase record
        $sql = "INSERT INTO purchase (Med_ID, Sup_ID, P_Qty, P_Cost, Pur_Date, Mfg_Date, Exp_Date) 
                VALUES ($medicine_id, $supplier_id, $quantity, $cost_price, '$purchase_date', " . 
                ($mfg_date ? "'$mfg_date'" : "NULL") . ", " . ($exp_date ? "'$exp_date'" : "NULL") . ")";
        
        if ($conn->query($sql)) {
            set_flash_message("Purchase recorded successfully! Stock has been updated.", "success");
            header("Location: view.php");
            exit();
        } else {
            set_flash_message("Error recording purchase. Please try again.", "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Purchase - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">New Purchase</h2>
            <p class="text-slate-500 mt-1 font-medium">Record stock purchase from supplier</p>
        </div>
        <a href="view.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            View Purchases
        </a>
    </div>

    <div class="max-w-4xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-8">
            <!-- Supplier Selection -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Supplier Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Select Supplier <span class="text-rose-500">*</span></label>
                        <select name="supplier_id" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="0">Choose Supplier</option>
                            <?php while($supplier = $suppliers->fetch_assoc()): ?>
                                <option value="<?php echo $supplier['Sup_ID']; ?>"><?php echo htmlspecialchars($supplier['Sup_Name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Purchase Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>
            </div>

            <!-- Medicine Selection -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Medicine Details
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Select Medicine <span class="text-rose-500">*</span></label>
                        <select name="medicine_id" id="medicine_id" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="0">Choose Medicine</option>
                            <?php 
                            $medicines->data_seek(0);
                            while($medicine = $medicines->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $medicine['Med_ID']; ?>" 
                                        data-price="<?php echo $medicine['Med_Price']; ?>">
                                    <?php echo htmlspecialchars($medicine['Med_Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Quantity <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" min="1" required
                               placeholder="Enter quantity"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Cost Price (Rs) <span class="text-rose-500">*</span></label>
                        <input type="number" name="cost_price" id="cost_price" step="0.01" min="0" required
                               placeholder="0.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                        <p class="text-xs text-slate-400 mt-1">Total cost: Rs. <span id="total_cost">0.00</span></p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Current Stock</label>
                        <input type="text" id="current_stock" readonly
                               placeholder="Select medicine"
                               class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-5 py-4 text-slate-600">
                    </div>
                </div>

                <!-- Date Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Manufacturing Date (Optional)</label>
                        <input type="date" name="mfg_date"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Expiry Date (Optional)</label>
                        <input type="date" name="exp_date"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" name="add_purchase" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Record Purchase
                </button>
            </div>
        </form>
    </div>

    <script>
        // Calculate total cost
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('cost_price').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const costPrice = parseFloat(document.getElementById('cost_price').value) || 0;
            const total = quantity * costPrice;
            document.getElementById('total_cost').textContent = total.toFixed(2);
        }
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
