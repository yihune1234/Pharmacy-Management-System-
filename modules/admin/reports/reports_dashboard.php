<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// 1. Total Medicines and Low Stock (from meds table)
$meds_stats = $conn->query("
    SELECT 
        COUNT(*) as total_medicines,
        SUM(CASE WHEN Med_Qty <= 10 THEN 1 ELSE 0 END) as low_stock_count
    FROM meds
")->fetch_assoc();

// 2. Expired Count (from purchase table, where individual batches are tracked)
$expired_stats = $conn->query("
    SELECT COUNT(DISTINCT Med_ID) as expired_count 
    FROM purchase 
    WHERE exp_date <= CURDATE()
")->fetch_assoc();

$inventory_status = [
    'total_medicines' => $meds_stats['total_medicines'] ?? 0,
    'low_stock_count' => $meds_stats['low_stock_count'] ?? 0,
    'expired_count' => $expired_stats['expired_count'] ?? 0
];

// 3. Expiry Alerts (Join meds and purchase)
$expiry_alerts = $conn->query("
    SELECT m.Med_Name, p.exp_date as Exp_Date, m.Med_Qty, m.Location_Rack
    FROM meds m
    JOIN purchase p ON m.Med_ID = p.Med_ID
    WHERE p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    GROUP BY m.Med_ID, m.Med_Name, p.exp_date, m.Med_Qty, m.Location_Rack
    ORDER BY p.exp_date ASC
    LIMIT 5
");

// 4. Low Stock Alerts
$low_stock_alerts = $conn->query("
    SELECT Med_Name, Med_Qty, Location_Rack
    FROM meds
    WHERE Med_Qty <= 10
    ORDER BY Med_Qty ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Console - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <main class="flex-1 overflow-auto">
        <div class="p-8">
            <!-- Header Section -->
            <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-1">Visual Analysis</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Reports Dashboard</h1>
            <p class="text-slate-500 font-medium mt-1">Cross-sectional business intelligence and performance matrix.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-file-pdf mr-2 text-rose-500"></i> Export PDF
            </button>
            <button class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all flex items-center group">
                <i class="fas fa-file-excel mr-3 text-emerald-400"></i> Export Matrix
            </button>
        </div>
    </div>

    <!-- Core Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-sack-dollar text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Assets Vol.</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $inventory_status['total_medicines']; ?></h3>
        </div>
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-triangle-exclamation text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Critical Stock</p>
            <h3 class="text-3xl font-black text-rose-600 leading-none"><?php echo $inventory_status['low_stock_count']; ?></h3>
        </div>
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-calendar-xmark text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Expired Batch</p>
            <h3 class="text-3xl font-black text-amber-600 leading-none"><?php echo $inventory_status['expired_count']; ?></h3>
        </div>
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg Efficiency</p>
            <h3 class="text-3xl font-black text-indigo-600 leading-none">94.2%</h3>
        </div>
    </div>

    <!-- Alert Console -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-slate-900 uppercase italic">Stock Depletion Alerts</h3>
                <span class="px-3 py-1 bg-rose-100 text-rose-600 text-[9px] font-black rounded-lg uppercase tracking-widest">High Priority</span>
            </div>
            <div class="space-y-4">
                <?php if ($low_stock_alerts && $low_stock_alerts->num_rows > 0): ?>
                    <?php while($item = $low_stock_alerts->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-slate-100 group hover:border-rose-200 transition-all">
                            <div class="flex items-center space-x-4">
                                <div class="w-1.5 h-8 bg-rose-500 rounded-full"></div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase"><?php echo htmlspecialchars($item['Location_Rack']); ?></span>
                                </div>
                            </div>
                            <span class="text-sm font-black text-rose-600"><?php echo $item['Med_Qty']; ?> Left</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-400 font-bold italic py-10 text-center">No critical stock levels detected.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-slate-900 uppercase italic">Expiry Watchdog</h3>
                <span class="px-3 py-1 bg-amber-100 text-amber-600 text-[9px] font-black rounded-lg uppercase tracking-widest">Schedule Log</span>
            </div>
            <div class="space-y-4">
                <?php if ($expiry_alerts && $expiry_alerts->num_rows > 0): ?>
                    <?php while($item = $expiry_alerts->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-slate-100 group hover:border-amber-200 transition-all">
                            <div class="flex items-center space-x-4">
                                <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($item['Med_Name']); ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase"><?php echo date('M Y', strtotime($item['Exp_Date'])); ?> Status</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-amber-600 tracking-widest uppercase">Approaching</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-400 font-bold italic py-10 text-center">No expiry risks synchronized.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Matrix -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <a href="sales_report.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 hover:-translate-y-2 transition-all">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-all">
                <i class="fas fa-chart-simple text-xl"></i>
            </div>
            <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2">Sales Analytics</h4>
            <p class="text-xs text-slate-500 font-medium">Deep dive into transactional high-frequency data logs.</p>
        </a>
        <a href="stock_report.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 hover:-translate-y-2 transition-all">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-all">
                <i class="fas fa-boxes-packing text-xl"></i>
            </div>
            <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2">Stock Fidelity</h4>
            <p class="text-xs text-slate-500 font-medium">Verify asset counts and storage location precision matrix.</p>
        </a>
        <a href="../employees/view_new.php" class="group bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 hover:-translate-y-2 transition-all">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-all">
                <i class="fas fa-user-gear text-xl"></i>
            </div>
            <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2">Force Dynamics</h4>
            <p class="text-xs text-slate-500 font-medium">Analyze personnel contribution and operational efficiency ratio.</p>
        </a>
    </div>

    <!-- Closing Sidebar Tags -->
        </div>
    </main>
    </div>
    </div>
</body>
</html>
