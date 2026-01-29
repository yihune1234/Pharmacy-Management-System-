<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get reporting data
$daily_sales = $conn->query("SELECT S_Date, COUNT(*) as Total_Bills, SUM(Total_Amt) as Total_Sales FROM view_daily_sales ORDER BY S_Date DESC LIMIT 30");
$monthly_revenue = $conn->query("
    SELECT DATE_FORMAT(S_Date, '%Y-%m') as Month, 
           SUM(Total_Amt) as Revenue 
    FROM sales 
    WHERE S_Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(S_Date, '%Y-%m')
    ORDER BY Month DESC
");

$inventory_status = $conn->query("
    SELECT 
        COUNT(*) as total_medicines,
        SUM(CASE WHEN Med_Qty <= 10 THEN 1 ELSE 0 END) as low_stock_count,
        COUNT(CASE WHEN Exp_Date <= CURDATE() THEN 1 ELSE 0 END) as expired_count
    FROM meds
");

$expiry_alerts = $conn->query("
    SELECT Med_Name, Exp_Date, Med_Qty, Location_Rack
    FROM meds
    WHERE Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY Exp_Date ASC
    LIMIT 10
");

$low_stock = $conn->query("
    SELECT Med_Name, Med_Qty, Location_Rack
    FROM meds
    WHERE Med_Qty <= 10
    ORDER BY Med_Qty ASC
    LIMIT 10
");

$purchase_history = $conn->query("
    SELECT p.Pur_Date, s.Sup_Name, m.Med_Name, p.P_Qty, p.P_Cost
    FROM purchase p
    JOIN suppliers s ON p.Sup_ID = s.Sup_ID
    JOIN meds m ON p.Med_ID = m.Med_ID
    ORDER BY p.Pur_Date DESC
    LIMIT 20
");

$employee_performance = $conn->query("
    SELECT e.E_Fname, e.E_Lname, COUNT(s.Sale_ID) as Total_Sales, SUM(s.Total_Amt) as Total_Sales_Amount
    FROM employee e
    LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0
    GROUP BY e.E_ID
    ORDER BY Total_Sales_Amount DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reports Dashboard</h2>
            <p class="text-slate-500 mt-1 font-medium">Comprehensive business analytics and insights</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="exportPDF()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2v-10a2 2 0 00-2-2H9a2 2 0 00-2-2v10a2 2 0 002 2h10m-10 2V7a2 2 0 00-2-2h-2m0 10a2 2 0 002 2h10m-10 2V7a2 2 0 00-2-2h-2z"></path></svg>
                Export PDF
            </button>
            <button onclick="exportExcel()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v2a2 2 0 002-2h-2m-6 0l-10 10v10m10 0V10a2 2 0 002 2h10m-6 0v-10a2 2 0 00-2-2h-2m0 10a2 2 0 002 2h10"></path></svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Key Metrics Overview -->
    <div class="grid grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today's Revenue</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $today_revenue = $conn->query("SELECT COALESCE(SUM(Total_Amt), 0) as total FROM sales WHERE S_Date = CURDATE()")->fetch_assoc()['total'];
                        echo number_format($today_revenue, 0);
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
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Medicines</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $inventory_status['total_medicines']; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m0-10l-8 4m-8-4v10"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Low Stock Items</p>
                    <p class="text-2xl font-black text-red-600"><?php echo $inventory_status['low_stock_count']; ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 6h.01M3 20a6 6 0 01-6 0h12a6 6 0 016 0 0 0h-6 0 0-6 0v-6h6"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Expired Items</p>
                    <p class="text-2xl font-black text-amber-600"><?php echo $inventory_status['expired_count']; ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3v6m-9 18h10a2 2 0 002 2h10a2 2 0 002-2H4a2 2 0 00-2-2h-2m0 10a2 2 0 002 2h10a2 2 0 002-2h-2m0 10a2 2 0 002 2h10a2 2 0 002-2h-2m0 10a2 2 0 002 2h-2m0 10a2 2 0 002 2h-2m-6 10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2m-6 10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2m-6 10v10a2 2 0 00-2-2h2"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Daily Sales Chart -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Daily Sales Trend</h3>
            <canvas id="dailySalesChart" width="400" height="200"></canvas>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Monthly Revenue Trend</h3>
            <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <a href="sales_report.php" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2m10 10V7a2 2 0 00-2-2h-2m-6 10v10a2 2 0 00-2-2h-2m-6 10v10a2 2 0 00-2-2h-2m-6 10v10a2 2 0 00-2-2h-2"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">Sales Report</h4>
                    <p class="text-sm text-slate-500">Detailed sales analytics</p>
                </div>
            </div>
        </a>

        <a href="inventory_report.php" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m0-10l-8 4m-8 4v10m8-4v10m0 0l-8 4"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">Inventory Report</h4>
                    <p class="text-sm text-slate-500">Stock levels and alerts</p>
                </div>
            </div>
        </a>

        <a href="expiry_report.php" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3v6m-9 18h10a2 2 0 002 2h10a2 2 0 002 2h-10m-6 10v10a2 2 0 00-2-2h-2m-6 10v10a2 2 0 00-2-2h-2"></path></svg>
                </div>
                <div>
                    <h4 class="Expiry Report</h4>
                    <p class="text-sm text-slate-500">Medicine expiry tracking</p>
                </div>
            </div>
        </a>

        <a href="employee_performance.php" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2m2 10a2 2 0 002 2h2m-2 10a2 2 0 00-2-2h-2m-2 10a2 2 0 00-2-2h-2m-2 10a2 2 0 00-2-2h-2z"></path></svg>
                </div>
                <div>
                    <h4>Employee Performance</h4>
                    <p class="text-sm text-slate-500">Sales performance metrics</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Alerts -->
    <div class="bg-gradient-to-r from-red-50 to-orange-50 p-8 rounded-3xl border border-red-200 mb-8">
        <h3 class="text-xl font-bold text-slate-900">Recent Alerts</h3>
        <p class="text-slate-600 mt-2">Critical inventory and system alerts requiring attention</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Low Stock Alerts -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                <h4 class="text-lg font-bold text-red-600 mb-4">⚠️ Low Stock Alerts</h4>
                <?php if ($low_stock && $low_stock->num_rows > 0): ?>
                    <div class="space-y-2">
                        <?php while($item = $low_stock->fetch_assoc()): ?>
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-xl">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></div>
                                    <div class="text-xs text-slate-500">Stock: <?php echo $item['Med_Qty']; ?></div>
                                </div>
                                <div class="text-right">
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Critical</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-emerald-600 text-center py-8">✅ All stock levels are healthy</p>
                <?php endif; ?>
            </div>

            <!-- Expiry Alerts -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                <h4 class="text-lg font-bold text-amber-600 mb-4">⚠️ Expiry Alerts</h4>
                <?php if ($expiry_alerts && $expiry_alerts->num_rows > 0): ?>
                    <div class="space-y-2">
                        <?php while($item = $expiry_alerts->fetch_assoc()): ?>
                            <div class="flex justify-between items-center p-3 bg-amber-50 rounded-xl">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></div>
                                    <div class="text-xs text-slate-500">Expires: <?php echo date('M j, Y', strtotime($item['Exp_Date']); ?></div>
                                </div>
                                <div class="text-right">
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">
                                        <?php 
                                        $days = (strtotime($item['Exp_Date']) - time()) / (60 * 60 * 24);
                                        if ($days <= 0) echo 'EXPIRED';
                                        elseif ($days <= 7) echo 'EXPIRING SOON';
                                        else echo $days . ' days';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-emerald-600 text-center py-8">✅ No medicines expiring soon</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Top Selling Medicines -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top Selling Medicines</h3>
            <?php
            $top_medicines = $conn->query("
                SELECT m.Med_Name, SUM(si.Sale_Qty) as Total_Sold, SUM(si.Tot_price) as Total_Revenue
                FROM meds m
                JOIN sales_items si ON m.Med_ID = si.Med_ID
                JOIN sales s ON si.Sale_ID = s.Sale_ID
                WHERE s.Refunded = 0
                GROUP BY m.Med_ID, m.Med_Name
                ORDER BY Total_Sold DESC
                LIMIT 5
            ");
            ?>
            <div class="space-y-4">
                <?php if ($top_medicines && $top_medicines->num_rows > 0): ?>
                    <?php while($med = $top_medicines->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div class="flex-1">
                                <div class="font-medium text-slate-900"><?php echo htmlspecialchars($med['Med_Name']); ?></div>
                                <div class="text-sm text-slate-500"><?php echo $med['Total_Sold']; ?> units sold</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900">Rs. <?php echo number_format($med['Total_Revenue'], 0); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No sales data available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Employee Performance -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top Performers</h3>
            <?php
            $top_performers = $conn->query("
                SELECT e.E_Fname, e.E_Lname, COUNT(s.Sale_ID) as Total_Sales, SUM(s.Total_Amt) as Total_Sales_Amount
                FROM employee e
                LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0
                GROUP BY e.E_ID, e.E_Fname, e.E_Lname
                ORDER BY Total_Sales_Amount DESC
                LIMIT 5
            ");
            ?>
            <div class="space-y-4">
                <?php if ($top_performers && $top_performers->num_rows > 0): ?>
                    <?php while($emp = $top_performers->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold">
                                    <?php echo strtoupper(substr($emp['E_Fname'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($emp['E_Fname'] . ' ' . $emp['E_Lname']); ?></div>
                                    <div class="text-sm text-slate-500"><?php echo htmlspecialchars($emp['E_Type']); ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?php echo $emp['Total_Sales']; ?> sales</div>
                                <div class="text-sm text-slate-500">Rs. <?php echo number_format($emp['Total_Sales_Amount'], 0); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No sales data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Daily Sales Chart
        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailyData = <?php 
            $daily_data = [];
            while($row = $daily_sales->fetch_assoc()) {
                $daily_data[] = [
                    date('M j', strtotime($row['Sale_Date']),
                    $row['Total_Bills'],
                    $row['Total_Sales']
                ];
            }
            echo json_encode(['labels' => $daily_data.map(item => $item[0]), 'data' => $daily_data.map(item => $item[1]), 'bills' => $daily_data.map(item => $item[2])]);
        ?>;
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.labels,
                datasets: [{
                    label: 'Total Sales (Rs)',
                    data: dailyData.data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Total Bills',
                    data: dailyData.bills,
                    borderColor: 'rgb(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
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

        // Monthly Revenue Chart
        const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const monthlyData = <?php 
            $monthly_data = [];
            while($row = $monthly_revenue->fetch_assoc()) {
                $monthly_data[] = [
                    $row['Month'],
                    $row['Revenue']
                ];
            }
            echo json_encode(['labels' => $monthly_data.map(item => $item[0]), 'data' => $monthly_data.map(item => $item[1])]);
        ?>;
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Revenue (Rs)',
                    data: monthlyData.data,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
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
                            callback: function(value) {
                                return 'Rs ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        function exportPDF() {
            window.open('export_pdf.php', '_blank');
        }

        function exportExcel() {
            window.open('export_excel.php', '_blank');
        }
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
