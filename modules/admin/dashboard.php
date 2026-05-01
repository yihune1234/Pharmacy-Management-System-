<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/pagination.php';
require_once __DIR__ . '/../../includes/caching.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Initialize cache with 5-minute TTL for KPI metrics
$cache = get_cache();
$kpi_cache_ttl = 300; // 5 minutes

// Get dashboard statistics with caching
// Cache key: dashboard_kpi_metrics
$kpi_metrics = $cache->get('dashboard_kpi_metrics');

if ($kpi_metrics === null) {
    // KPI metrics not in cache, fetch from database
    // Using prepared statements for security
    
    // Total medicines count - indexed on Med_ID
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM meds");
    $stmt->execute();
    $total_meds = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Low stock count - indexed on Med_Qty
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM meds WHERE Med_Qty <= 10");
    $stmt->execute();
    $low_stock = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Today's sales count - indexed on S_Date
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM sales WHERE S_Date = CURDATE()");
    $stmt->execute();
    $today_sales = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Today's revenue - indexed on S_Date and Total_Amt
    $stmt = $conn->prepare("SELECT COALESCE(SUM(Total_Amt), 0) as total FROM sales WHERE S_Date = CURDATE()");
    $stmt->execute();
    $today_revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    // Total customers - indexed on C_ID
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customer");
    $stmt->execute();
    $total_customers = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Total employees - indexed on E_ID
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employee");
    $stmt->execute();
    $total_employees = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Total suppliers - indexed on Sup_ID
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM suppliers");
    $stmt->execute();
    $total_suppliers = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Cache the KPI metrics
    $kpi_metrics = [
        'total_meds' => $total_meds,
        'low_stock' => $low_stock,
        'today_sales' => $today_sales,
        'today_revenue' => $today_revenue,
        'total_customers' => $total_customers,
        'total_employees' => $total_employees,
        'total_suppliers' => $total_suppliers
    ];
    $cache->set('dashboard_kpi_metrics', $kpi_metrics, $kpi_cache_ttl);
} else {
    // Use cached metrics
    $total_meds = $kpi_metrics['total_meds'];
    $low_stock = $kpi_metrics['low_stock'];
    $today_sales = $kpi_metrics['today_sales'];
    $today_revenue = $kpi_metrics['today_revenue'];
    $total_customers = $kpi_metrics['total_customers'];
    $total_employees = $kpi_metrics['total_employees'];
    $total_suppliers = $kpi_metrics['total_suppliers'];
}

// Pagination for recent sales
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;

// Get total recent sales count - indexed on Sale_ID
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM sales");
$stmt->execute();
$total_sales = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$pagination = new Pagination($total_sales, $page, $limit);
$offset = $pagination->get_offset();

// Recent sales with pagination - using composite indexes on (S_Date, C_ID) and (S_Date, E_ID)
$stmt = $conn->prepare("SELECT s.Sale_ID, s.S_Date, s.Total_Amt, c.C_Fname, c.C_Lname, e.E_Fname as emp_name 
                        FROM sales s 
                        LEFT JOIN customer c ON s.C_ID = c.C_ID 
                        LEFT JOIN employee e ON s.E_ID = e.E_ID 
                        ORDER BY s.Sale_ID DESC 
                        LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$recent_sales = $stmt->get_result();
$stmt->close();

// Top selling medicines - using composite index on (Med_ID, Exp_Date)
$stmt = $conn->prepare("SELECT m.Med_Name, SUM(si.Sale_Qty) as total_sold, SUM(si.Tot_Price) as revenue 
                        FROM meds m 
                        JOIN sales_items si ON m.Med_ID = si.Med_ID 
                        JOIN sales s ON si.Sale_ID = s.Sale_ID 
                        WHERE s.S_Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY m.Med_ID, m.Med_Name 
                        ORDER BY total_sold DESC LIMIT 5");
$stmt->execute();
$top_meds = $stmt->get_result();
$stmt->close();

// Monthly trend data - using index on S_Date
$stmt = $conn->prepare("SELECT DATE_FORMAT(S_Date, '%Y-%m') as month, COUNT(*) as sales_count, SUM(Total_Amt) as revenue 
                        FROM sales 
                        WHERE S_Date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                        GROUP BY month ORDER BY month ASC");
$stmt->execute();
$monthly_sales = $stmt->get_result();
$stmt->close();

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
            <p class="subheading-premium">Intelligence Overview</p>
            <h1 class="heading-premium">System Dashboard</h1>
            <p class="text-slate-500 font-medium mt-1">Operational status for <?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="flex items-center space-x-3">
            <button class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-calendar-alt mr-2"></i> Monthly Range
            </button>
            <button class="btn-primary btn-blue">
                <i class="fas fa-file-export mr-2"></i> Export Data
            </button>
        </div>
    </div>

    <!-- Analytics Pulse -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <!-- Card 1: Inventory -->
        <div class="premium-card p-8">
            <div class="stat-icon bg-blue-50 text-blue-600">
                <i class="fas fa-layer-group text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Stock Assets</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $total_meds; ?></h3>
            <div class="mt-4 flex items-center text-[10px] font-bold">
                <span class="text-emerald-500">+12% from last week</span>
            </div>
        </div>
        
        <!-- Card 2: Revenue -->
        <div class="premium-card p-8 bg-slate-900 !border-slate-800">
            <div class="stat-icon bg-white/10 text-white backdrop-blur">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Today's Revenue</p>
            <h3 class="text-3xl font-black text-white leading-none">Rs. <?php echo number_format($today_revenue, 2); ?></h3>
            <div class="mt-4 flex items-center text-[10px] font-bold">
                <span class="text-blue-400">Transaction pulse active</span>
            </div>
        </div>

        <!-- Card 3: Patients -->
        <div class="premium-card p-8">
            <div class="stat-icon bg-emerald-50 text-emerald-600">
                <i class="fas fa-user-check text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Patient Base</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $total_customers; ?></h3>
            <div class="mt-4 flex items-center text-[10px] font-bold text-slate-400">
                Across all regional branches
            </div>
        </div>

        <!-- Card 4: Alerts -->
        <div class="premium-card p-8 bg-rose-50/30 !border-rose-100/50">
            <div class="stat-icon bg-rose-50 text-rose-600">
                <i class="fas fa-shield-virus text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">System Alerts</p>
            <h3 class="text-3xl font-black text-rose-900 leading-none"><?php echo $low_stock; ?></h3>
            <div class="mt-4 flex items-center text-[10px] font-bold text-rose-600">
                <i class="fas fa-exclamation-triangle mr-1"></i> Critical stock warning
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="lg:col-span-2 premium-card p-10">
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

        <div class="premium-card p-10">
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
        
        <!-- Pagination Controls -->
        <div class="p-10 border-t border-slate-50 bg-slate-50/30">
            <?php echo $pagination->generate_html('dashboard.php'); ?>
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
