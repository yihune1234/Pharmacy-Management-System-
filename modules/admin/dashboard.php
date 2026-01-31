<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get dashboard statistics
$total_meds = $conn->query("SELECT COUNT(*) as count FROM meds")->fetch_assoc()['count'] ?? 0;
$low_stock = $conn->query("SELECT COUNT(*) as count FROM meds WHERE Med_Qty <= 10")->fetch_assoc()['count'] ?? 0;
$today_sales = $conn->query("SELECT COUNT(*) as count FROM sales WHERE S_Date = CURDATE()")->fetch_assoc()['count'] ?? 0;
$today_revenue = $conn->query("SELECT COALESCE(SUM(Total_Amt), 0) as total FROM sales WHERE S_Date = CURDATE()")->fetch_assoc()['total'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'] ?? 0;
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'] ?? 0;
$total_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'] ?? 0;

// Recent sales
$recent_sales = $conn->query("SELECT s.Sale_ID, s.S_Date, s.Total_Amt, c.C_Fname, c.C_Lname, e.E_Fname as emp_name 
                             FROM sales s 
                             LEFT JOIN customer c ON s.C_ID = c.C_ID 
                             LEFT JOIN employee e ON s.E_ID = e.E_ID 
                             ORDER BY s.Sale_ID DESC LIMIT 10");

// Top selling medicines
$top_meds = $conn->query("SELECT m.Med_Name, SUM(si.Sale_Qty) as total_sold, SUM(si.Tot_Price) as revenue 
                         FROM meds m 
                         JOIN sales_items si ON m.Med_ID = si.Med_ID 
                         JOIN sales s ON si.Sale_ID = s.Sale_ID 
                         WHERE s.S_Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                         GROUP BY m.Med_ID, m.Med_Name 
                         ORDER BY total_sold DESC LIMIT 5");

// Monthly trend data
$monthly_sales = $conn->query("SELECT DATE_FORMAT(S_Date, '%Y-%m') as month, COUNT(*) as sales_count, SUM(Total_Amt) as revenue 
                              FROM sales 
                              WHERE S_Date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                              GROUP BY month ORDER BY month ASC");

$chart_labels = [];
$chart_revenue = [];
while($row = $monthly_sales->fetch_assoc()) {
    $chart_labels[] = date('M', strtotime($row['month']."-01"));
    $chart_revenue[] = $row['revenue'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Console - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .vibrant-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-[#f8fafc]">
    <?php require('sidebar.php'); ?>

    <!-- Welcome Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between space-y-4 md:space-y-0">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Intelligence Overview</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">System Dashboard</h1>
            <p class="text-slate-500 font-medium mt-1">Operational status for <?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="flex items-center space-x-3">
            <button class="bg-white border border-slate-200 px-5 py-2.5 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-calendar-alt mr-2"></i> Monthly Range
            </button>
            <button class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center">
                <i class="fas fa-file-export mr-2"></i> Export Data
            </button>
        </div>
    </div>

    <!-- Analytics Pulse -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <!-- Card 1 -->
        <div class="stat-card bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Current Volume</p>
                <div class="flex items-end space-x-2">
                    <h3 class="text-4xl font-black text-slate-900 leading-none"><?php echo $today_sales; ?></h3>
                    <span class="text-emerald-500 text-xs font-bold pb-1 flex items-center">
                        <i class="fas fa-arrow-up mr-1"></i> 12%
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-3 italic">Today's system transactions</p>
            </div>
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-blue-50/30 rounded-full blur-2xl"></div>
        </div>

        <!-- Card 2 -->
        <div class="stat-card bg-slate-900 p-8 rounded-[2rem] shadow-2xl relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 backdrop-blur">
                    <i class="fas fa-vault text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Financial Liquid</p>
                <div class="flex items-end space-x-2">
                    <h3 class="text-3xl font-black text-white leading-none">Rs. <?php echo number_format($today_revenue, 0); ?></h3>
                </div>
                <p class="text-xs text-slate-400 font-medium mt-4">Real-time revenue stream</p>
            </div>
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-600/20 rounded-full blur-3xl"></div>
        </div>

        <!-- Card 3 -->
        <div class="stat-card bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-microscope text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Global Inventory</p>
            <h3 class="text-4xl font-black text-slate-900 leading-none"><?php echo $total_meds; ?></h3>
            <p class="text-xs text-slate-500 font-medium mt-3 italic">Active stock items</p>
        </div>

        <!-- Card 4 -->
        <div class="stat-card bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-biohazard text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Critical Stock</p>
            <h3 class="text-4xl font-black <?php echo ($low_stock > 0) ? 'text-rose-600' : 'text-slate-300'; ?> leading-none"><?php echo $low_stock; ?></h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-rose-100 text-rose-700 text-[10px] font-black rounded-lg uppercase tracking-wider <?php echo ($low_stock > 0) ? 'alert-pulse' : 'hidden'; ?>">High Priority</span>
            </div>
        </div>
    </div>

    <!-- Trends & Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="lg:col-span-2 bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900 uppercase italic">Revenue Velocity</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Growth progression chart</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 bg-blue-600 rounded-full"></span>
                    <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Performance</span>
                </div>
            </div>
            <div class="h-80">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <h3 class="text-xl font-black text-slate-900 uppercase italic mb-8">Asset Ranking</h3>
            <div class="space-y-6">
                <?php while($med = $top_meds->fetch_assoc()): ?>
                <div class="group cursor-pointer">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black text-slate-800 uppercase tracking-tight group-hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($med['Med_Name']); ?></span>
                        <span class="text-[10px] font-black text-slate-400 italic">Rs. <?php echo number_format($med['revenue'], 0); ?></span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all duration-1000" style="width: <?php echo rand(60, 95); ?>%"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <button class="w-full mt-10 py-4 bg-slate-50 text-slate-500 font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-100 transition-all border border-slate-200 hover:border-slate-300">
                View detailed inventory matrix
            </button>
        </div>
    </div>

    <!-- Recent System Activity -->
    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden mb-12">
        <div class="p-10 border-b border-slate-50 flex items-center justify-between">
            <h3 class="text-xl font-black text-slate-900 uppercase italic">Transactional Log</h3>
            <a href="sales/view_new.php" class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:tracking-tight transition-all">Audit full logs &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Protocol ID</th>
                        <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Entity Signature</th>
                        <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Authorized By</th>
                        <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Volume (Rs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($recent_sales && $recent_sales->num_rows > 0): ?>
                        <?php while($sale = $recent_sales->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-10 py-6">
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-[10px] font-black tracking-widest">#S-<?php echo $sale['Sale_ID']; ?></span>
                            </td>
                            <td class="px-10 py-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($sale['C_Fname'] . ' ' . $sale['C_Lname']); ?></span>
                                    <span class="text-[10px] text-slate-400 font-medium tracking-tight"><?php echo date('H:i | d M Y', strtotime($sale['S_Date'])); ?></span>
                                </div>
                            </td>
                            <td class="px-10 py-6">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                    <span class="text-xs font-bold text-slate-600"><?php echo htmlspecialchars($sale['emp_name']); ?></span>
                                </div>
                            </td>
                            <td class="px-10 py-6 text-right">
                                <span class="text-sm font-black text-slate-900 tracking-tight">Rs. <?php echo number_format($sale['Total_Amt'], 2); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Close Sidebar Tags -->
    </main>
    </div>
    </div>

    <script>
        const ctxVelocity = document.getElementById('velocityChart').getContext('2d');
        new Chart(ctxVelocity, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($chart_revenue); ?>,
                    borderColor: '#2563eb',
                    borderWidth: 5,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 4,
                    tension: 0.5,
                    fill: true,
                    backgroundColor: (context) => {
                        const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.1)');
                        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');
                        return gradient;
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { border: { display: false }, grid: { borderDash: [5, 5], color: '#e2e8f0' }, ticks: { font: { weight: '800', size: 10 }, color: '#94a3b8' } },
                    x: { border: { display: false }, grid: { display: false }, ticks: { font: { weight: '800', size: 10 }, color: '#94a3b8' } }
                }
            }
        });
    </script>
</body>
</html>
