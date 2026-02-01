<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Order Creation/Initialization
$sid = $_GET['sid'] ?? null;
if (!$sid && isset($_POST['custadd'])) {
    $cid = $conn->real_escape_string($_POST['cid']);
    $eid = $_SESSION['user'];
    
    if ($cid == "0") {
        set_flash_message("Please select a customer to proceed.", "warning");
    } else {
        $qry = "INSERT INTO sales(c_id, e_id, s_date, s_time, total_amt) VALUES ('$cid', '$eid', CURDATE(), CURTIME(), 0)";
        if ($conn->query($qry)) {
            $sid = $conn->insert_id;
            header("Location: pos1.php?sid=" . $sid);
            exit();
        } else {
            set_flash_message("Could not initialize sale. Please try again.", "error");
        }
    }
}

// Handle Adding Items
if (isset($_POST['add_item'])) {
    $sid = $_POST['sid'];
    $mid = $_POST['med_id'];
    $qty = (int)$_POST['qty'];
    $price = (float)$_POST['price'];
    $total = $price * $qty;
    
    // Check stock
    $stock_check = $conn->query("SELECT Med_Qty, Med_Name FROM meds WHERE Med_ID = '$mid'")->fetch_assoc();
    if ($stock_check['Med_Qty'] < $qty) {
        set_flash_message("Insufficient stock for " . $stock_check['Med_Name'] . ". Only " . $stock_check['Med_Qty'] . " left.", "error");
    } else {
        $sql = "INSERT INTO sales_items(sale_id, med_id, sale_qty, tot_price) VALUES ('$sid', '$mid', '$qty', '$total')
                ON DUPLICATE KEY UPDATE sale_qty = sale_qty + '$qty', tot_price = tot_price + '$total'";
        
        if ($conn->query($sql)) {
            set_flash_message("Added to order successfully!", "success");
            header("Location: pos1.php?sid=" . $sid);
            exit();
        } else {
            set_flash_message("Error adding item to order.", "error");
        }
    }
}

// Fetch Current Items in Order
$order_items = [];
$order_total = 0;
if ($sid) {
    $res = $conn->query("SELECT si.*, m.Med_Name, m.Med_Price FROM sales_items si JOIN meds m ON si.med_id = m.Med_ID WHERE si.sale_id = '$sid'");
    while($row = $res->fetch_assoc()) {
        $order_items[] = $row;
        $order_total += $row['tot_price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <p class="subheading-premium">Clinical Dispensation</p>
            <h1 class="heading-premium">Point of Sale</h1>
            <p class="text-slate-500 mt-1 font-medium">Create and manage customer transactions.</p>
        </div>
        <?php if ($sid): ?>
        <div class="flex items-center space-x-3">
            <div class="px-6 py-3 bg-blue-50 text-blue-600 rounded-2xl font-black text-xs uppercase tracking-widest border border-blue-100 flex items-center">
                <i class="fas fa-hashtag mr-2 opacity-50"></i> Order <?php echo str_pad($sid, 6, '0', STR_PAD_LEFT); ?>
            </div>
            <a href="pos1.php" class="btn-primary !bg-rose-50 !text-rose-600 !shadow-none hover:!bg-rose-100 !px-6">
                <i class="fas fa-times-circle mr-2"></i> Cancel
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
        
        <!-- Main POS Area -->
        <div class="xl:col-span-2 space-y-10">
            
            <?php if (!$sid): ?>
            <!-- Initial Customer Selection -->
            <div class="premium-card p-12">
                <div class="max-w-md mx-auto text-center">
                    <div class="stat-icon !w-20 !h-20 bg-emerald-50 text-emerald-600 mx-auto mb-8">
                        <i class="fas fa-user-plus text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-3 italic">READY FOR CHECKOUT</h3>
                    <p class="text-slate-500 mb-10 font-medium">Identify the patient to initialize the digital protocol and start processing the order.</p>
                    
                    <form method="post" class="space-y-6 text-left">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Identify Patient Entity</label>
                            <div class="relative">
                                <select name="cid" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none font-bold text-slate-700 appearance-none cursor-pointer">
                                    <option value="0">--- SELECT MEMBER FROM DATABASE ---</option>
                                    <?php
                                    $customers = $conn->query("SELECT C_ID, C_Fname, C_Lname, C_Phno FROM customer ORDER BY C_Fname ASC");
                                    while($c = $customers->fetch_assoc()) {
                                        echo "<option value='".$c['C_ID']."'>".$c['C_Fname']." ".$c['C_Lname']." (".$c['C_Phno'].")</option>";
                                    }
                                    ?>
                                </select>
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="custadd" class="btn-primary btn-slate w-full !py-5 uppercase tracking-[0.2em] italic">
                            Initialize Session &rarr;
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Item Search & Selection -->
            <div class="premium-card p-8 bg-blue-50/30 !border-blue-100/50">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center font-black italic">01</div>
                    <h3 class="text-lg font-black text-slate-900 uppercase italic">Add Medication</h3>
                </div>
                
                <form method="post" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                    <input type="hidden" name="sid" value="<?php echo $sid; ?>">
                    <div class="md:col-span-8">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Product Catalog</label>
                        <div class="relative">
                            <select name="med_id" id="med_select" required class="w-full bg-white border border-slate-200 rounded-2xl px-6 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none font-bold text-slate-700 appearance-none cursor-pointer">
                                <option value="">--- SEARCH PHARMACIA RECORDS ---</option>
                                <?php
                                $meds = $conn->query("SELECT Med_ID, Med_Name, Med_Price, Med_Qty FROM meds WHERE Med_Qty > 0 ORDER BY Med_Name ASC");
                                while($m = $meds->fetch_assoc()) {
                                    echo "<option value='".$m['Med_ID']."' data-price='".$m['Med_Price']."' data-qty='".$m['Med_Qty']."'>".$m['Med_Name']." (".$m['Med_Qty']." IN STOCK) - RS. ".$m['Med_Price']."</option>";
                                }
                                ?>
                            </select>
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <i class="fas fa-search text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Volume</label>
                        <input type="number" name="qty" value="1" min="1" required class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none font-bold text-slate-700 text-center">
                    </div>
                    <input type="hidden" name="price" id="med_price">
                    <div class="md:col-span-2">
                        <button type="submit" name="add_item" class="btn-primary btn-blue w-full h-[60px] !px-0">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Order Table -->
            <div class="premium-card overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-black text-slate-900 uppercase italic">Digital Cart</h3>
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 pulse"></span>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo count($order_items); ?> Segments</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Medical Item</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit Rate</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Quantity</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Summation</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($order_items as $item): ?>
                            <tr class="hover:bg-blue-50/20 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            <i class="fas fa-pills text-[10px]"></i>
                                        </div>
                                        <span class="font-bold text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-slate-500 font-bold text-sm italic">Rs. <?php echo number_format($item['Med_Price'], 0); ?></td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-4 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest"><?php echo $item['sale_qty']; ?> UNIT</span>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-slate-900">Rs. <?php echo number_format($item['tot_price'], 0); ?></td>
                                <td class="px-8 py-5 text-right">
                                    <a href="delete_pos.php?mid=<?php echo $item['med_id']; ?>&sid=<?php echo $sid; ?>" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-400 flex items-center justify-center ml-auto hover:bg-rose-600 hover:text-white transition-all active:scale-90">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($order_items)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="opacity-20 mb-4 text-4xl"><i class="fas fa-shopping-basket"></i></div>
                                    <p class="text-slate-400 font-black text-[10px] uppercase tracking-[0.2em]">Cart Protocol Empty</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Order Summary Column -->
        <div class="space-y-8">
            <div class="premium-card bg-slate-900 p-10 shadow-2xl text-white sticky top-28 !border-slate-800">
                <div class="flex items-center space-x-3 mb-10">
                    <div class="w-10 h-10 bg-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="text-xl font-black uppercase italic tracking-tight">Intelligence Sum</h3>
                </div>
                
                <div class="space-y-6 mb-12">
                    <div class="flex justify-between items-center pb-6 border-b border-white/5">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Gross Inventory</span>
                        <span class="text-sm font-bold">Rs. <?php echo number_format($order_total, 0); ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-6 border-b border-white/5">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Tax (0%)</span>
                        <span class="text-sm font-bold">Rs. 0</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-slate-300 font-black text-[10px] uppercase tracking-[0.2em] italic">Final Settlement</span>
                        <span class="text-3xl font-black text-emerald-400 tracking-tighter">Rs. <?php echo number_format($order_total, 0); ?></span>
                    </div>
                </div>

                <form action="pos2.php" method="post" class="space-y-4">
                    <input type="hidden" name="sid" value="<?php echo $sid; ?>">
                    <button type="submit" name="custadd" <?php echo (!$sid || empty($order_items)) ? 'disabled' : ''; ?> 
                        class="btn-primary w-full !bg-emerald-500 !text-slate-900 !py-5 uppercase tracking-[0.2em] disabled:!bg-slate-800 disabled:!text-slate-600 disabled:!shadow-none flex items-center justify-center">
                        Finalize & Print &rarr;
                    </button>
                    <?php if($sid): ?>
                    <a href="pos1.php" class="block text-center text-[10px] font-black text-slate-500 uppercase tracking-widest hover:text-rose-500 transition-colors py-2">Void Transaction</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>

    <script>
        const medSelect = document.getElementById('med_select');
        const medPrice = document.getElementById('med_price');
        
        if (medSelect) {
            medSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                medPrice.value = selectedOption.getAttribute('data-price') || 0;
            });
        }
    </script>
    <?php $conn->close(); ?>
</body>
</html>
