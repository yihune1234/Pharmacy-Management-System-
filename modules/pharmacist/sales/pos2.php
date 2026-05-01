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

$sid = $_POST['sid'] ?? $_GET['sid'] ?? null;

if (!$sid) {
    header("Location: pos1.php");
    exit();
}

// Calculate Total if it's the final step
if (isset($_POST['custadd'])) {
    $res = $conn->query("SELECT SUM(Tot_Price) AS TOTAL FROM sales_items WHERE Sale_ID='$sid'");
    $row = $res->fetch_assoc();
    $tot = $row['TOTAL'] ?? 0;
    
    $conn->query("UPDATE sales SET Total_Amt='$tot' WHERE Sale_ID='$sid'");
    set_flash_message("✅ Transaction processed successfully!", "success");
}

// Fetch Order & Customer Details
$order_query = "SELECT s.*, c.C_Fname, c.C_Lname, c.C_Phno, e.E_Fname 
                FROM sales s 
                JOIN customer c ON s.C_ID = c.C_ID 
                JOIN employee e ON s.E_ID = e.E_ID 
                WHERE s.Sale_ID = '$sid'";
$order_res = $conn->query($order_query);
$order = $order_res->fetch_assoc();

// Fetch Items
$items_res = $conn->query("SELECT si.*, m.Med_Name, m.Med_Price 
                           FROM sales_items si 
                           JOIN meds m ON si.Med_ID = m.Med_ID 
                           WHERE si.Sale_ID = '$sid'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice - PHARMACIA</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .invoice-card { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between no-print">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sales Invoice</h2>
            <p class="text-slate-500 mt-1 font-medium">Finalize transaction and print receipt.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="bg-white text-slate-900 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 shadow-sm hover:bg-slate-50 flex items-center transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Receipt
            </button>
            <a href="pos1.php" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg hover:bg-slate-800 flex items-center transition-all">
                New Sale
            </a>
        </div>
    </div>

    <!-- Invoice Card -->
    <div class="max-w-4xl mx-auto bg-white rounded-[3rem] border border-slate-200 shadow-2xl p-12 mb-12 relative overflow-hidden invoice-card">
        
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 z-0 no-print"></div>

        <div class="relative z-10">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between mb-12">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center">
                            <span class="text-white font-black text-xl">+</span>
                        </div>
                        <h2 class="text-slate-900 font-black text-2xl tracking-tighter">PHARMACIA</h2>
                    </div>
                    <p class="text-slate-400 text-sm font-medium">123 Health Ave, Medical District<br>Tel: +1 (555) 000-1111</p>
                </div>
                <div class="mt-8 md:mt-0 md:text-right">
                    <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tighter mb-2">Invoice</h1>
                    <p class="text-slate-500 font-bold">#<?php echo str_pad($sid, 6, '0', STR_PAD_LEFT); ?></p>
                    <p class="text-slate-400 text-sm mt-1"><?php echo date('d F, Y', strtotime($order['S_Date'] ?? 'today')); ?></p>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12 p-8 bg-slate-50 rounded-[2rem]">
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Billed To</h4>
                    <p class="text-slate-900 font-black text-lg"><?php echo htmlspecialchars($order['C_Fname'] . ' ' . $order['C_Lname']); ?></p>
                    <p class="text-slate-500 font-medium mt-1">Phone: <?php echo htmlspecialchars($order['C_Phno']); ?></p>
                </div>
                <div class="md:text-right">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Issued By</h4>
                    <p class="text-slate-900 font-black text-lg"><?php echo htmlspecialchars($order['E_Fname']); ?></p>
                    <p class="text-slate-500 font-medium mt-1 italic">Pharmacist In-Charge</p>
                </div>
            </div>

            <!-- Items table -->
            <table class="w-full mb-12">
                <thead>
                    <tr class="border-b-2 border-slate-100">
                        <th class="py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Item Description</th>
                        <th class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Quantity</th>
                        <th class="py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Unit Price</th>
                        <th class="py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($items_res): while($item = $items_res->fetch_assoc()): ?>
                    <tr>
                        <td class="py-6 font-bold text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></td>
                        <td class="py-6 text-center text-slate-600 font-bold"><?php echo $item['Sale_Qty']; ?></td>
                        <td class="py-6 text-right text-slate-500 font-medium">Rs. <?php echo number_format($item['Med_Price'], 2); ?></td>
                        <td class="py-6 text-right font-black text-slate-900">Rs. <?php echo number_format($item['Tot_Price'], 2); ?></td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="flex flex-col items-end pt-8 border-t-2 border-slate-100">
                <div class="w-full md:w-64 space-y-4">
                    <div class="flex justify-between text-slate-500 font-medium">
                        <span>Subtotal</span>
                        <span>Rs. <?php echo number_format($order['Total_Amt'] ?? 0, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-slate-500 font-medium">
                        <span>Tax (0%)</span>
                        <span>Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-4 px-6 bg-slate-900 rounded-2xl mt-4">
                        <span class="text-white font-bold uppercase tracking-widest text-xs">Total Amount</span>
                        <span class="text-emerald-400 font-black text-xl">Rs. <?php echo number_format($order['Total_Amt'] ?? 0, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-16 text-center border-t border-slate-100 pt-8 no-print">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-4">Thank you for choosing Pharmacia</p>
                <div class="flex justify-center space-x-8">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-1 bg-emerald-500 rounded-full mb-2"></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Safe</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-1 bg-blue-500 rounded-full mb-2"></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Reliable</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-1 bg-indigo-500 rounded-full mb-2"></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Quality</p>
                    </div>
                </div>
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