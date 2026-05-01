<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../config/config.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');

// Get pharmacist dashboard statistics
$today_sales = 0;
$available_stock = 0;
$low_stock_count = 0;

// Today's sales
$today = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) as count FROM sales WHERE S_Date = '$today'");
if ($result) {
    $row = $result->fetch_assoc();
    $today_sales = $row['count'];
}

// Available stock (total medicines)
$result = $conn->query("SELECT COUNT(*) as count FROM meds");
if ($result) {
    $row = $result->fetch_assoc();
    $available_stock = $row['count'];
}

// Low stock items
$result = $conn->query("SELECT COUNT(*) as count FROM meds WHERE Med_Qty <= 10");
if ($result) {
    $row = $result->fetch_assoc();
    $low_stock_count = $row['count'];
}

// Get sales data for chart (last 7 days)
$sales_chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $result = $conn->query("SELECT COUNT(*) as count FROM sales WHERE S_Date = '$date'");
    $count = 0;
    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['count'];
    }
    $sales_chart_data[] = [
        'date' => date('M j', strtotime($date)),
        'sales' => $count
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('./sidebar.php'); ?>

    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <p class="subheading-premium">Clinical Overview</p>
            <h1 class="heading-premium">Pharmacist Panel</h1>
            <p class="text-slate-500 mt-2 font-medium italic">Operations & Inventory Control</p>
        </div>
        <div class="flex items-center space-x-3">
             <a href="sales/pos1.php" class="btn-primary btn-emerald">
                <i class="fas fa-prescription mr-3 group-hover:rotate-90 transition-transform"></i> New Dispensation
            </a>
        </div>
    </div>

    <!-- KPI Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="premium-card p-8">
            <div class="stat-icon bg-blue-50 text-blue-600">
                <i class="fas fa-file-prescription text-xl"></i>
            </div>
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Today's Volume</p>
                <p class="text-3xl font-black text-slate-900 leading-none"><?php echo $today_sales; ?></p>
                <p class="text-xs text-slate-400 mt-3 font-medium">Orders completed</p>
            </div>
        </div>
        
        <div class="premium-card p-8">
            <div class="stat-icon bg-emerald-50 text-emerald-600">
                <i class="fas fa-boxes-stacked text-xl"></i>
            </div>
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Global Stock</p>
                <p class="text-3xl font-black text-slate-900 leading-none"><?php echo $available_stock; ?></p>
                <p class="text-xs text-slate-400 mt-3 font-medium">Medicine classifications</p>
            </div>
        </div>
        
        <div class="premium-card p-8 bg-rose-50/20 !border-rose-100/50">
            <div class="stat-icon bg-rose-50 text-rose-600">
                <i class="fas fa-biohazard text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Critical Alert</p>
                <p class="text-3xl font-black text-rose-900 leading-none"><?php echo $low_stock_count; ?></p>
                <p class="text-xs text-rose-500 mt-3 font-bold">Resupply necessitated</p>
            </div>
        </div>
    </div>

    <!-- Analytics & Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Chart -->
        <div class="lg:col-span-2 premium-card p-10">
            <h3 class="text-xl font-black text-slate-900 uppercase italic mb-8">Dispensing Velocity</h3>
            <div class="h-80">
                <canvas id="pharmacistSalesChart"></canvas>
            </div>
        </div>

        <!-- System Shortcuts -->
        <div class="space-y-6">
            <a href="sales/pos1.php" class="premium-card bg-emerald-600 p-8 shadow-xl shadow-emerald-200 flex flex-col justify-between group h-min">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white text-2xl backdrop-blur mb-6">
                    <i class="fas fa-cart-plus"></i>
                </div>
                <div>
                    <h4 class="text-xl font-black text-white uppercase italic">Launch POS</h4>
                    <p class="text-emerald-100 text-xs font-medium">Secure clinical terminal</p>
                </div>
            </a>

            <a href="inventory/view.php" class="premium-card p-8 flex flex-col justify-between group h-min">
                <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-2xl mb-6">
                    <i class="fas fa-search"></i>
                </div>
                <div>
                    <h4 class="text-xl font-black text-slate-900 uppercase italic">Stock Inquiry</h4>
                    <p class="text-slate-400 text-xs font-medium">Global database search</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Sidebar footer tags handled by include -->
    </main>
    </div>
    </div>
</body>
</html>