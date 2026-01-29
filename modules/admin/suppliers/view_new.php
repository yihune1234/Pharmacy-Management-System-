<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get supplier statistics with purchase data
$sql = "SELECT s.*, 
               COUNT(p.P_ID) as Total_Purchases,
               COALESCE(SUM(p.P_Qty * p.P_Cost), 0) as Total_Investment,
               COALESCE(SUM(p.P_Qty), 0) as Total_Quantity,
               MAX(p.Pur_Date) as Last_Purchase_Date,
               AVG(p.P_Cost) as Avg_Unit_Cost,
               CASE 
                   WHEN s.Rating >= 4.5 THEN 'Excellent'
                   WHEN s.Rating >= 3.5 THEN 'Good'
                   WHEN s.Rating >= 2.5 THEN 'Average'
                   WHEN s.Rating >= 1.5 THEN 'Poor'
                   ELSE 'Very Poor'
               END as Rating_Text
        FROM suppliers s
        LEFT JOIN purchase p ON s.Sup_ID = p.Sup_ID
        GROUP BY s.Sup_ID
        ORDER BY s.Sup_Name";

$result = $conn->query($sql);

// Get summary statistics
$total_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'];
$active_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers WHERE Status = 'Active'")->fetch_assoc()['count'];
$total_investment = $conn->query("SELECT COALESCE(SUM(P_Qty * P_Cost), 0) as total FROM purchase")->fetch_assoc()['total'];
$avg_rating = $conn->query("SELECT COALESCE(AVG(Rating), 0) as avg FROM suppliers WHERE Rating > 0")->fetch_assoc()['avg'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Supplier Management</h2>
            <p class="text-slate-500 mt-1 font-medium">Manage suppliers and track purchase relationships</p>
        </div>
        <div class="flex space-x-3">
            <a href="balance_report.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Balance Report
            </a>
            <a href="add_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Supplier
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Suppliers</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $total_suppliers; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Suppliers</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $active_suppliers; ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Investment</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($total_investment, 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg Rating</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo number_format($avg_rating, 1); ?> ⭐</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Supplier Directory</h3>
            <p class="text-slate-600 mt-2">Complete supplier database with purchase tracking and performance ratings</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Company Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Purchases</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Investment</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($supplier = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo str_pad($supplier['Sup_ID'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($supplier['Sup_Name']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($supplier['Contact_Person'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="text-xs"><?php echo htmlspecialchars($supplier['Sup_Phno']); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($supplier['Sup_Mail'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo $supplier['Total_Purchases']; ?> orders
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($supplier['Total_Investment'], 2); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center">
                                        <span class="text-amber-500 mr-1">⭐</span>
                                        <span class="font-bold"><?php echo number_format($supplier['Rating'], 1); ?></span>
                                        <span class="text-xs text-slate-500 ml-1">(<?php echo $supplier['Rating_Text']; ?>)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $status_color = ['Active' => 'bg-emerald-100 text-emerald-700', 'Inactive' => 'bg-slate-100 text-slate-700', 'Probation' => 'bg-amber-100 text-amber-700', 'Blacklisted' => 'bg-red-100 text-red-700'];
                                    $status = $supplier['Status'] ?? 'Active';
                                    ?>
                                    <span class="<?php echo $status_color[$status]; ?> px-3 py-1 rounded-full text-xs font-bold">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="supplier_purchases.php?id=<?php echo $supplier['Sup_ID']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        Purchases
                                    </a>
                                    <a href="edit_new.php?id=<?php echo $supplier['Sup_ID']; ?>" 
                                       class="text-emerald-600 hover:text-emerald-800 font-bold text-sm">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                No suppliers found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Top Suppliers by Investment -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top Suppliers by Investment</h3>
            <?php
            $top_suppliers = $conn->query("
                SELECT s.Sup_Name, s.Sup_ID, COALESCE(SUM(p.P_Qty * p.P_Cost), 0) as Total_Investment
                FROM suppliers s
                LEFT JOIN purchase p ON s.Sup_ID = p.Sup_ID
                GROUP BY s.Sup_ID, s.Sup_Name
                HAVING Total_Investment > 0
                ORDER BY Total_Investment DESC
                LIMIT 5
            ");
            ?>
            <div class="space-y-4">
                <?php if ($top_suppliers && $top_suppliers->num_rows > 0): ?>
                    <?php while($supplier = $top_suppliers->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div class="font-medium text-slate-900"><?php echo htmlspecialchars($supplier['Sup_Name']); ?></div>
                            <div class="font-bold text-slate-900">Rs. <?php echo number_format($supplier['Total_Investment'], 0); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No purchase data available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rating Distribution -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Performance Rating Distribution</h3>
            <canvas id="ratingChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        // Rating Distribution Chart
        const ratingCtx = document.getElementById('ratingChart').getContext('2d');
        const ratingData = <?php 
            $ratings = $conn->query("
                SELECT Rating, COUNT(*) as count 
                FROM suppliers 
                WHERE Rating > 0 
                GROUP BY Rating 
                ORDER BY Rating DESC
            ");
            $labels = [];
            $counts = [];
            while($row = $ratings->fetch_assoc()) {
                $labels[] = $row['Rating'] . ' Stars';
                $counts[] = $row['count'];
            }
            echo json_encode(['labels' => $labels, 'data' => $counts]);
        ?>;
        
        new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: ratingData.labels,
                datasets: [{
                    label: 'Number of Suppliers',
                    data: ratingData.data,
                    backgroundColor: 'rgba(251, 191, 36, 0.8)',
                    borderColor: 'rgb(251, 191, 36)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
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
