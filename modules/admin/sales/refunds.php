<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Handle refund processing
if (isset($_POST['process_refund'])) {
    $sale_id = (int)$_POST['sale_id'];
    $refund_reason = $conn->real_escape_string($_POST['refund_reason']);
    
    if ($sale_id > 0 && !empty($refund_reason)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check if sale exists and is not already refunded
            $sale_check = $conn->query("SELECT * FROM sales WHERE Sale_ID = $sale_id AND Refunded = 0");
            $sale = $sale_check->fetch_assoc();
            
            if (!$sale) {
                throw new Exception("Sale not found or already refunded");
            }
            
            // Get sale items to restore stock
            $items_query = $conn->query("SELECT Med_ID, Sale_Qty FROM sales_items WHERE Sale_ID = $sale_id");
            
            // Restore stock for each item
            while ($item = $items_query->fetch_assoc()) {
                $update_stock = $conn->query("UPDATE meds SET Med_Qty = Med_Qty + {$item['Sale_Qty']} WHERE Med_ID = {$item['Med_ID']}");
                if (!$update_stock) {
                    throw new Exception("Failed to restore stock for item");
                }
            }
            
            // Mark sale as refunded
            $refund_update = $conn->query("UPDATE sales SET Refunded = 1, Refund_Reason = '$refund_reason', Refund_Date = NOW() WHERE Sale_ID = $sale_id");
            if (!$refund_update) {
                throw new Exception("Failed to mark sale as refunded");
            }
            
            // Create refund record
            $refund_insert = $conn->query("INSERT INTO refunds (Sale_ID, Refund_Amount, Refund_Reason, Refund_Date, Employee_ID) 
                                         VALUES ($sale_id, {$sale['Total_Amt']}, '$refund_reason', NOW(), {$_SESSION['user']})");
            
            $conn->commit();
            set_flash_message("Sale #$sale_id has been refunded successfully. Stock has been restored.", "success");
            
        } catch (Exception $e) {
            $conn->rollback();
            set_flash_message("Refund failed: " . $e->getMessage(), "error");
        }
        
        header("Location: refunds.php");
        exit();
    }
}

// Get all sales with refund status
$sales_query = $conn->query("
    SELECT s.*, c.C_Fname, c.C_Lname, e.E_Fname as Employee_Name,
           CASE WHEN s.Refunded = 1 THEN 'Refunded' ELSE 'Completed' END as Status
    FROM sales s
    LEFT JOIN customer c ON s.C_ID = c.C_ID
    LEFT JOIN employee e ON s.E_ID = e.E_ID
    ORDER BY s.Sale_ID DESC
");

// Get refund statistics
$refunded_count = $conn->query("SELECT COUNT(*) as count FROM sales WHERE Refunded = 1")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT COUNT(*) as count FROM sales")->fetch_assoc()['count'];
$total_refunded_amount = $conn->query("SELECT SUM(Total_Amt) as total FROM sales WHERE Refunded = 1")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sales Management</h2>
            <p class="text-slate-500 mt-1 font-medium">View sales history and process refunds</p>
        </div>
        <div class="flex space-x-3">
            <a href="pos_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Sale
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Sales</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $total_sales; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Refunded Sales</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $refunded_count; ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Refunded Amount</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($total_refunded_amount, 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Net Revenue</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $total_revenue = $conn->query("SELECT SUM(Total_Amt) as total FROM sales WHERE Refunded = 0")->fetch_assoc()['total'];
                        echo number_format($total_revenue, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Sales History</h3>
            <p class="text-slate-600 mt-2">Complete sales transactions with refund status</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Sale ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($sales_query && $sales_query->num_rows > 0): ?>
                        <?php while($sale = $sales_query->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo str_pad($sale['Sale_ID'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M j, Y h:i A', strtotime($sale['S_Date'] . ' ' . $sale['S_Time'])); ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($sale['C_Fname'] . ' ' . $sale['C_Lname']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($sale['Employee_Name']); ?></td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($sale['Total_Amt'], 2); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($sale['Status'] == 'Refunded'): ?>
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Refunded</span>
                                    <?php else: ?>
                                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="receipt.php?sale_id=<?php echo $sale['Sale_ID']; ?>" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        Receipt
                                    </a>
                                    <?php if ($sale['Status'] == 'Completed'): ?>
                                        <button onclick="showRefundModal(<?php echo $sale['Sale_ID']; ?>, <?php echo $sale['Total_Amt']; ?>)" 
                                                class="text-red-600 hover:text-red-800 font-bold text-sm">
                                            Refund
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-sm">Refunded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                No sales records found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Process Refund</h3>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="sale_id" id="refund_sale_id">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Refund Amount</label>
                    <input type="text" id="refund_amount" readonly 
                           class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-slate-900">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Refund Reason <span class="text-red-500">*</span></label>
                    <textarea name="refund_reason" required rows="3" 
                              placeholder="Please specify the reason for refund..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" name="process_refund" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-all">
                        Process Refund
                    </button>
                    <button type="button" onclick="hideRefundModal()" 
                            class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 rounded-xl transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRefundModal(saleId, amount) {
            document.getElementById('refund_sale_id').value = saleId;
            document.getElementById('refund_amount').value = 'Rs. ' + amount.toFixed(2);
            document.getElementById('refundModal').classList.remove('hidden');
        }
        
        function hideRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
        }
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
