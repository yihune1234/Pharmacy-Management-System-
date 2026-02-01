require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate cashier access
require_cashier();
validate_role_area('cashier');

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
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Point of Sale</h2>
            <p class="text-slate-500 mt-1 font-medium">Create and manage customer transactions.</p>
        </div>
        <?php if ($sid): ?>
        <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-xl font-bold text-sm">Order #<?php echo str_pad($sid, 6, '0', STR_PAD_LEFT); ?></span>
            <a href="pos1.php" class="text-rose-600 font-bold text-sm hover:underline">Cancel Order</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Main POS Area -->
        <div class="xl:col-span-2 space-y-8">
            
            <?php if (!$sid): ?>
            <!-- Initial Customer Selection -->
            <div class="bg-white p-10 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <div class="max-w-md mx-auto text-center">
                    <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-3">Welcome to POS</h3>
                    <p class="text-slate-500 mb-8 font-medium">To start a new transaction, please select a customer from the database.</p>
                    
                    <form method="post" class="space-y-4 text-left">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Identify Customer</label>
                            <select name="cid" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-bold text-slate-700 appearance-none">
                                <option value="0">--- Select Member ---</option>
                                <?php
                                $customers = $conn->query("SELECT C_ID, C_Fname, C_Lname, C_Phno FROM customer ORDER BY C_Fname ASC");
                                while($c = $customers->fetch_assoc()) {
                                    echo "<option value='".$c['C_ID']."'>".$c['C_Fname']." ".$c['C_Lname']." (".$c['C_Phno'].")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="custadd" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl shadow-xl hover:bg-slate-800 transition-all active:scale-95">
                            Start Transaction
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Item Search & Selection -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm">01</span>
                    Select Medicine
                </h3>
                
                <form method="post" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <input type="hidden" name="sid" value="<?php echo $sid; ?>">
                    <div class="md:col-span-8">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Search Product</label>
                        <select name="med_id" id="med_select" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-bold text-slate-700 appearance-none">
                            <option value="">--- Search by Name ---</option>
                            <?php
                            $meds = $conn->query("SELECT Med_ID, Med_Name, Med_Price, Med_Qty FROM meds WHERE Med_Qty > 0 ORDER BY Med_Name ASC");
                            while($m = $meds->fetch_assoc()) {
                                echo "<option value='".$m['Med_ID']."' data-price='".$m['Med_Price']."' data-qty='".$m['Med_Qty']."'>".$m['Med_Name']." (".$m['Med_Qty']." in stock) - Rs. ".$m['Med_Price']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Quantity</label>
                        <input type="number" name="qty" value="1" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-bold text-slate-700">
                    </div>
                    <input type="hidden" name="price" id="med_price">
                    <div class="md:col-span-2">
                        <button type="submit" name="add_item" class="w-full bg-blue-600 text-white font-black h-[58px] rounded-2xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all active:scale-95 flex items-center justify-center">
                            Add
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Order Table -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Items in Order</h3>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?php echo count($order_items); ?> products added</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit Price</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Qty</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($order_items as $item): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-4 font-bold text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></td>
                                <td class="px-6 py-4 text-slate-500 font-medium text-sm">Rs. <?php echo number_format($item['Med_Price'], 2); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold"><?php echo $item['sale_qty']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">Rs. <?php echo number_format($item['tot_price'], 2); ?></td>
                                <td class="px-8 py-4 text-right">
                                    <a href="delete_pos.php?mid=<?php echo $item['med_id']; ?>&sid=<?php echo $sid; ?>" class="text-rose-400 hover:text-rose-600 transition-colors">
                                        <svg class="w-5 h-5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($order_items)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center text-slate-400 font-medium">No items added to this order yet.</td>
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
            <div class="bg-slate-900 p-10 rounded-[2.5rem] shadow-2xl text-white sticky top-28">
                <h3 class="text-xl font-bold mb-8 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path></svg>
                    Bill Summary
                </h3>
                
                <div class="space-y-6 mb-10">
                    <div class="flex justify-between items-center pb-6 border-b border-white/10">
                        <span class="text-slate-400 font-medium">Subtotal</span>
                        <span class="text-lg font-bold">Rs. <?php echo number_format($order_total, 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-6 border-b border-white/10">
                        <span class="text-slate-400 font-medium">Tax (0%)</span>
                        <span class="text-lg font-bold">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-300 font-bold">Order Total</span>
                        <span class="text-3xl font-black text-emerald-400">Rs. <?php echo number_format($order_total, 2); ?></span>
                    </div>
                </div>

                <form action="pos2.php" method="post">
                    <input type="hidden" name="sid" value="<?php echo $sid; ?>">
                    <button type="submit" name="custadd" <?php echo (!$sid || empty($order_items)) ? 'disabled' : ''; ?> 
                        class="w-full bg-emerald-500 hover:bg-emerald-400 disabled:bg-slate-700 disabled:text-slate-500 text-slate-900 font-black py-5 rounded-2xl shadow-xl shadow-emerald-500/20 transition-all active:scale-95 flex items-center justify-center">
                        Complete Payment
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </button>
                </form>
                <p class="text-center text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-6">Confirm all items before finishing</p>
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
