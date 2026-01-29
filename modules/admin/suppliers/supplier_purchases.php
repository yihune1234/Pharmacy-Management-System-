<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$supplier_id = $_GET['id'] ?? 0;

if ($supplier_id == 0) {
    set_flash_message("Invalid supplier ID.", "error");
    header("Location: view_new.php");
    exit();
}

// Get supplier details
$supplier_sql = "SELECT * FROM suppliers WHERE Sup_ID = $supplier_id";
$supplier_result = $conn->query($supplier_sql);
$supplier = $supplier_result->fetch_assoc();

if (!$supplier) {
    set_flash_message("Supplier not found.", "error");
    header("Location: view_new.php");
    exit();
}

// Get supplier purchase history
$purchases_sql = "SELECT p.*, m.Med_Name, 
                         COUNT(si.Med_ID) as Item_Count
                  FROM purchase p
                  LEFT JOIN meds m ON p.Med_ID = m.Med_ID
                  LEFT JOIN sales_items si ON p.Med_ID = si.Med_ID AND p.P_ID = si.Sale_ID
                  WHERE p.Sup_ID = $supplier_id
                  GROUP BY p.P_ID
                  ORDER BY p.Pur_Date DESC, p.P_ID DESC";

$purchases_result = $conn->query($purchases_sql);

// Get supplier statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_purchases,
        COALESCE(SUM(P_Qty * P_Cost), 0) as total_spent,
        COALESCE(AVG(P_Qty * P_Cost), 0) as avg_purchase,
        MAX(Pur_Date) as last_purchase,
        MIN(Pur_Date) as first_purchase,
        COALESCE(SUM(P_Qty), 0) as total_quantity,
        COUNT(DISTINCT Med_ID) as unique_medicines
    FROM purchase 
    WHERE Sup_ID = $supplier_id
")->fetch_assoc();

// Get top purchased medicines from this supplier
$top_meds = $conn->query("
    SELECT m.Med_Name, SUM(p.P_Qty) as Total_Quantity, SUM(p.P_Qty * p.P_Cost) as Total_Cost
    FROM purchase p
    JOIN meds m ON p.Med_ID = m.Med_ID
    WHERE p.Sup_ID = $supplier_id
    GROUP BY m.Med_ID, m.Med_Name
    ORDER BY Total_Quantity DESC
    LIMIT 5
");

// Get monthly purchase trends
$monthly_trends = $conn->query("
    SELECT DATE_FORMAT(Pur_Date, '%Y-%m') as Month,
           COUNT(*) as Purchase_Count,
           SUM(P_Qty * P_Cost) as Total_Value
    FROM purchase
    WHERE Sup_ID = $supplier_id AND Pur_Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(Pur_Date, '%Y-%m')
    ORDER BY Month DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Purchase History - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Supplier Purchase History</h2>
            <p class="text-slate-500 mt-1 font-medium">
                <?php echo htmlspecialchars($supplier['Sup_Name']); ?> - Complete Purchase Records
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="balance_report.php?id=<?php echo $supplier_id; ?>" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Balance Report
            </a>
            <a href="view_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Suppliers
            </a>
        </div>
    </div>

    <!-- Supplier Overview Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-8 rounded-3xl border border-blue-200 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Supplier</div>
                <div class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($supplier['Sup_Name']); ?></div>
                <div class="text-sm text-slate-600">ID: #<?php echo str_pad($supplier['Sup_ID'], 5, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Contact</div>
                <div class="text-sm text-slate-900"><?php echo htmlspecialchars($supplier['Sup_Phno']); ?></div>
                <div class="text-sm text-slate-600"><?php echo htmlspecialchars($supplier['Sup_Mail'] ?? 'N/A'); ?></div>
            </div>
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Performance</div>
                <div class="flex items-center">
                    <span class="text-amber-500 mr-1">⭐</span>
                    <span class="font-bold"><?php echo number_format($supplier['Rating'] ?? 4, 1); ?></span>
                    <span class="text-xs text-slate-500 ml-1">Rating</span>
                </div>
                <div class="text-sm text-slate-600"><?php echo $supplier['Status'] ?? 'Active'; ?></div>
            </div>
            <div>
                <div class="text-sm text-blue-600 font-medium mb-1">Supplier Since</div>
                <div class="text-sm text-slate-900"><?php echo $stats['first_purchase'] ? date('M j, Y', strtotime($stats['first_purchase'])) : 'New Supplier'; ?></div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Purchases</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $stats['total_purchases']; ?></p>
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
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg Purchase</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($stats['avg_purchase'], 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Unique Medicines</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $stats['unique_medicines']; ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Purchase ID</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($purchases_result && $purchases_result->num_rows > 0): ?>
                                <?php while($purchase = $purchases_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo str_pad($purchase['P_ID'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M j, Y', strtotime($purchase['Pur_Date'])); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-900"><?php echo htmlspecialchars($purchase['Med_Name']); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-bold">
                                                <?php echo $purchase['P_Qty']; ?> units
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">Rs. <?php echo number_format($purchase['P_Cost'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($purchase['P_Qty'] * $purchase['P_Cost'], 2); ?></td>
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
                                    <div class="font-bold text-slate-900">Rs. <?php echo number_format($med['Total_Cost'], 0); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No purchase data available</p>
                <?php endif; ?>
            </div>

            <!-- Monthly Trend Chart -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Monthly Purchase Trend</h3>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="../purchases/add_new.php" class="block bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-3 rounded-xl transition-all flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Purchase
                    </a>
                    <a href="edit_new.php?id=<?php echo $supplier_id; ?>" class="block bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-4 py-3 rounded-xl transition-all flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Supplier
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = <?php 
            $months = [];
            $values = [];
            if ($monthly_trends) {
                while($row = $monthly_trends->fetch_assoc()) {
                    $months[] = date('M Y', strtotime($row['Month'] . '-01'));
                    $values[] = floatval($row['Total_Value']);
                }
            }
            echo json_encode(['labels' => $months, 'data' => $values]);
        ?>;
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Purchase Value (Rs)',
                    data: monthlyData.data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
