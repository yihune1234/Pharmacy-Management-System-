<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

// Get supplier statistics
$supplier_stats = $conn->query("
    SELECT s.Sup_ID, s.Sup_Name, s.Sup_Phno, s.Sup_Mail,
           COUNT(p.P_ID) as Total_Purchases,
           SUM(p.P_Qty * p.P_Cost) as Total_Investment,
           SUM(p.P_Qty) as Total_Quantity,
           MAX(p.Pur_Date) as Last_Purchase_Date
    FROM suppliers s
    LEFT JOIN purchase p ON s.Sup_ID = p.Sup_ID
    GROUP BY s.Sup_ID, s.Sup_Name, s.Sup_Phno, s.Sup_Mail
    ORDER BY Total_Investment DESC
");

// Get monthly purchase trends
$monthly_trends = $conn->query("
    SELECT DATE_FORMAT(Pur_Date, '%Y-%m') as Month,
           COUNT(*) as Purchase_Count,
           SUM(P_Qty * P_Cost) as Total_Value
    FROM purchase
    WHERE Pur_Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(Pur_Date, '%Y-%m')
    ORDER BY Month DESC
");

// Get top medicines by purchase volume
$top_medicines = $conn->query("
    SELECT m.Med_Name, 
           SUM(p.P_Qty) as Total_Quantity,
           SUM(p.P_Qty * p.P_Cost) as Total_Cost,
           COUNT(p.P_ID) as Purchase_Count
    FROM meds m
    JOIN purchase p ON m.Med_ID = p.Med_ID
    GROUP BY m.Med_ID, m.Med_Name
    ORDER BY Total_Quantity DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Report - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Supplier Report</h2>
            <p class="text-slate-500 mt-1 font-medium">Comprehensive supplier performance analysis</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Report
            </button>
            <a href="view_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Purchases
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Suppliers</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $supplier_stats ? $supplier_stats->num_rows : 0; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Investment</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $total_investment = 0;
                        if ($supplier_stats) {
                            while($row = $supplier_stats->fetch_assoc()) {
                                $total_investment += $row['Total_Investment'];
                            }
                            $supplier_stats->data_seek(0);
                        }
                        echo number_format($total_investment, 0);
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
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Purchases</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $total_purchases = 0;
                        if ($supplier_stats) {
                            while($row = $supplier_stats->fetch_assoc()) {
                                $total_purchases += $row['Total_Purchases'];
                            }
                            $supplier_stats->data_seek(0);
                        }
                        echo $total_purchases;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg Purchase Value</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $avg_value = $total_purchases > 0 ? $total_investment / $total_purchases : 0;
                        echo number_format($avg_value, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Trend Chart -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Monthly Purchase Trend</h3>
            <canvas id="monthlyChart" width="400" height="200"></canvas>
        </div>

        <!-- Top Medicines Chart -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top Medicines by Quantity</h3>
            <canvas id="medicinesChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Supplier Details Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Supplier Performance Details</h3>
            <p class="text-slate-600 mt-2">Complete supplier purchase history and statistics</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Purchases</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Quantity</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Investment</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Last Purchase</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($supplier_stats && $supplier_stats->num_rows > 0): ?>
                        <?php while($row = $supplier_stats->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['Sup_Name']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="text-xs"><?php echo htmlspecialchars($row['Sup_Phno'] ?? 'N/A'); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($row['Sup_Mail'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo $row['Total_Purchases']; ?> orders
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo number_format($row['Total_Quantity']); ?> units</td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($row['Total_Investment'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if ($row['Last_Purchase_Date']): ?>
                                        <?php echo date('M j, Y', strtotime($row['Last_Purchase_Date'])); ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">No purchases</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($row['Total_Purchases'] > 0): ?>
                                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Active</span>
                                    <?php else: ?>
                                        <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                No supplier data available
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

        // Top Medicines Chart
        const medicinesCtx = document.getElementById('medicinesChart').getContext('2d');
        const medicinesData = <?php 
            $medNames = [];
            $quantities = [];
            if ($top_medicines) {
                while($row = $top_medicines->fetch_assoc()) {
                    $medNames[] = substr($row['Med_Name'], 0, 20) . (strlen($row['Med_Name']) > 20 ? '...' : '');
                    $quantities[] = intval($row['Total_Quantity']);
                }
            }
            echo json_encode(['labels' => $medNames, 'data' => $quantities]);
        ?>;
        
        new Chart(medicinesCtx, {
            type: 'bar',
            data: {
                labels: medicinesData.labels,
                datasets: [{
                    label: 'Quantity',
                    data: medicinesData.data,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
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
