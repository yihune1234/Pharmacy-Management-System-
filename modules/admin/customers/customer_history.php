<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$customer_id = $_GET['id'] ?? 0;

if ($customer_id == 0) {
    set_flash_message("Invalid customer ID.", "error");
    header("Location: view_new.php");
    exit();
}

// Get customer details
$customer_sql = "SELECT * FROM customer WHERE C_ID = $customer_id";
$customer_result = $conn->query($customer_sql);
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    set_flash_message("Customer not found.", "error");
    header("Location: view_new.php");
    exit();
}

// Get customer sales history
$sales_sql = "SELECT s.*, 
                     COUNT(si.Med_ID) as Item_Count,
                     GROUP_CONCAT(CONCAT(m.Med_Name, ' (', si.Sale_Qty, ')') SEPARATOR ', ') as Items
              FROM sales s
              LEFT JOIN sales_items si ON s.Sale_ID = si.Sale_ID
              LEFT JOIN meds m ON si.Med_ID = m.Med_ID
              WHERE s.C_ID = $customer_id
              GROUP BY s.Sale_ID
              ORDER BY s.S_Date DESC, s.S_Time DESC";

$sales_result = $conn->query($sales_sql);

// Get customer statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_sales,
        COALESCE(SUM(Total_Amt), 0) as total_spent,
        COALESCE(AVG(Total_Amt), 0) as avg_sale,
        MAX(S_Date) as last_purchase,
        MIN(S_Date) as first_purchase
    FROM sales 
    WHERE C_ID = $customer_id AND Refunded = 0
")->fetch_assoc();

// Get top purchased medicines
$top_meds = $conn->query("
    SELECT m.Med_Name, SUM(si.Sale_Qty) as Total_Quantity, SUM(si.Tot_Price) as Total_Spent
    FROM sales_items si
    JOIN sales s ON si.Sale_ID = s.Sale_ID
    JOIN meds m ON si.Med_ID = m.Med_ID
    WHERE s.C_ID = $customer_id AND s.Refunded = 0
    GROUP BY m.Med_ID, m.Med_Name
    ORDER BY Total_Quantity DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer History - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Customer History</h2>
            <p class="text-slate-500 mt-1 font-medium">
                <?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?> - Purchase History & Analytics
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="customer_invoices.php?id=<?php echo $customer_id; ?>" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                View Invoices
            </a>
            <a href="view_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Customers
            </a>
        </div>
    </div>

    <!-- Customer Overview Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-8 rounded-3xl border border-blue-200 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Customer</div>
                <div class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></div>
                <div class="text-sm text-slate-600">ID: #<?php echo str_pad($customer['C_ID'], 5, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Contact</div>
                <div class="text-sm text-slate-900"><?php echo htmlspecialchars($customer['C_Phno']); ?></div>
                <div class="text-sm text-slate-600"><?php echo htmlspecialchars($customer['C_Mail'] ?? 'N/A'); ?></div>
            </div>
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Loyalty Status</div>
                <?php
                $points = $customer['Loyalty_Points'] ?? 0;
                $tier = $points >= 1000 ? 'Gold' : ($points >= 500 ? 'Silver' : 'Bronze');
                $tier_color = ['Bronze' => 'bg-slate-100 text-slate-700', 'Silver' => 'bg-gray-100 text-gray-700', 'Gold' => 'bg-amber-100 text-amber-700'];
                ?>
                <span class="<?php echo $tier_color[$tier]; ?> px-3 py-1 rounded-full text-xs font-bold">
                    <?php echo $tier; ?> (<?php echo $points; ?> pts)
                </span>
            </div>
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Customer Since</div>
                <div class="text-sm text-slate-900"><?php echo $stats['first_purchase'] ? date('M j, Y', strtotime($stats['first_purchase'])) : 'New Customer'; ?></div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Sales</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $stats['total_sales']; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Spent</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($stats['total_spent'], 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg. Sale</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($stats['avg_sale'], 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Last Purchase</p>
                    <p class="text-lg font-black text-slate-900"><?php echo $stats['last_purchase'] ? date('M j, Y', strtotime($stats['last_purchase'])) : 'Never'; ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Purchase History -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
                    <h3 class="text-xl font-bold text-slate-900">Purchase History</h3>
                    <p class="text-slate-600 mt-2">Complete transaction history</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Sale ID</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($sales_result && $sales_result->num_rows > 0): ?>
                                <?php while($sale = $sales_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo str_pad($sale['Sale_ID'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M j, Y h:i A', strtotime($sale['S_Date'] . ' ' . $sale['S_Time'])); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            <div class="text-xs"><?php echo $sale['Item_Count']; ?> items</div>
                                            <div class="text-xs text-slate-400 truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($sale['Items']); ?>">
                                                <?php echo htmlspecialchars(substr($sale['Items'], 0, 50)) . '...'; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($sale['Total_Amt'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php if ($sale['Refunded']): ?>
                                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Refunded</span>
                                            <?php else: ?>
                                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="../sales/receipt.php?sale_id=<?php echo $sale['Sale_ID']; ?>" 
                                               target="_blank"
                                               class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                                Receipt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                        No purchase history found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Purchased Medicines -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Top Purchased Medicines</h3>
                
                <?php if ($top_meds && $top_meds->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($med = $top_meds->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                <div>
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($med['Med_Name']); ?></div>
                                    <div class="text-sm text-slate-500"><?php echo $med['Total_Quantity']; ?> units</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-900">Rs. <?php echo number_format($med['Total_Spent'], 0); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No purchase data available</p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="../sales/pos_new.php" class="block bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-3 rounded-xl transition-all flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create New Sale
                    </a>
                    <a href="edit_new.php?id=<?php echo $customer_id; ?>" class="block bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-4 py-3 rounded-xl transition-all flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
