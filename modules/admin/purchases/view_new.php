<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

// Get purchase details with supplier and medicine information
$sql = "SELECT p.P_ID, p.P_Qty, p.P_Cost, p.Pur_Date, p.Mfg_Date, p.Exp_Date,
               s.Sup_Name, m.Med_Name, m.Med_Price, m.Med_Qty as Current_Stock
        FROM purchase p
        LEFT JOIN suppliers s ON p.Sup_ID = s.Sup_ID
        LEFT JOIN meds m ON p.Med_ID = m.Med_ID
        ORDER BY p.Pur_Date DESC, p.P_ID DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <main class="flex-1 overflow-auto">
        <div class="p-8">
            <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Purchase History</h2>
            <p class="text-slate-500 mt-1 font-medium">View all stock purchases from suppliers</p>
        </div>
        <div class="flex space-x-3">
            <a href="supplier_report.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Supplier Report
            </a>
            <a href="add_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Purchase
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Purchases</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $result ? $result->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Investment</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $total_sql = "SELECT SUM(P_Cost * P_Qty) as total FROM purchase";
                        $total_result = $conn->query($total_sql);
                        $total_row = $total_result->fetch_assoc();
                        echo number_format($total_row['total'] ?? 0, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">This Month</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $month_sql = "SELECT COUNT(*) as count FROM purchase WHERE MONTH(Pur_Date) = MONTH(CURDATE()) AND YEAR(Pur_Date) = YEAR(CURDATE())";
                        $month_result = $conn->query($month_sql);
                        $month_row = $month_result->fetch_assoc();
                        echo $month_row['count'] ?? 0;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Suppliers</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $sup_sql = "SELECT COUNT(DISTINCT Sup_ID) as count FROM purchase";
                        $sup_result = $conn->query($sup_sql);
                        $sup_row = $sup_result->fetch_assoc();
                        echo $sup_row['count'] ?? 0;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Purchase ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Cost/Unit</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Cost</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo $row['P_ID']; ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M j, Y', strtotime($row['Pur_Date'])); ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($row['Sup_Name'] ?? 'Unknown'); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-900"><?php echo htmlspecialchars($row['Med_Name']); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo $row['P_Qty']; ?> units
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">Rs. <?php echo number_format($row['P_Cost'], 2); ?></td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($row['P_Cost'] * $row['P_Qty'], 2); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo $row['Current_Stock']; ?> units
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button onclick="generateInvoice(<?php echo $row['P_ID']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        Invoice
                                    </button>
                                    <a href="delete.php?id=<?php echo $row['P_ID']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this purchase?')" 
                                       class="text-red-600 hover:text-red-800 font-bold text-sm">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                No purchase records found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function generateInvoice(purchaseId) {
            window.open(`invoice.php?id=${purchaseId}`, '_blank', 'width=800,height=600');
        }
    </script>
        </div>
    </main>
    </div>
</body>
</html>
