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

    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pharmacist Dashboard</h2>
        <p class="text-slate-500 mt-2 font-medium">Daily Operations & Sales</p>
    </div>

    <!-- KPI Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-6">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Today's Sales</p>
                <p class="text-3xl font-black text-slate-900"><?php echo $today_sales; ?></p>
                <p class="text-xs text-slate-400 mt-1">Transactions completed</p>
            </div>
        </div>
        
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-6">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Available Stock</p>
                <p class="text-3xl font-black text-slate-900"><?php echo $available_stock; ?></p>
                <p class="text-xs text-slate-400 mt-1">Medicine types</p>
            </div>
        </div>
        
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-6">
            <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Low Stock Alert</p>
                <p class="text-3xl font-black text-slate-900"><?php echo $low_stock_count; ?></p>
                <p class="text-xs text-amber-400 mt-1">Items need restocking</p>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm mb-12">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Sales Performance (Last 7 Days)</h3>
        <canvas id="pharmacistSalesChart" width="400" height="150"></canvas>
        <script>
            const ctx = document.getElementById('pharmacistSalesChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($sales_chart_data, 'date')); ?>,
                    datasets: [{
                        label: 'Sales',
                        data: <?php echo json_encode(array_column($sales_chart_data, 'sales')); ?>,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>

    <!-- Actions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <a href="sales/pos1.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2 tracking-tight">Point of Sale</h3>
            <p class="text-slate-500 mb-6 leading-relaxed">Start a new transaction, scan medications, and generate digital receipts for walk-in customers.</p>
            <div class="flex items-center text-blue-600 font-bold uppercase text-xs tracking-widest">
                <span>Start Selling</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <a href="inventory/view.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-20 h-20 bg-slate-800 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2 tracking-tight">Stock Search</h3>
            <p class="text-slate-500 mb-6 leading-relaxed">Instantly check medication availability, pricing, shelf localization and expiry dates in the database.</p>
            <div class="flex items-center text-slate-900 font-bold uppercase text-xs tracking-widest">
                <span>Check Records</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>
    </div>

    <!-- Sidebar footer tags handled by include -->
    </main>
    </div>
    </div>
</body>
</html>