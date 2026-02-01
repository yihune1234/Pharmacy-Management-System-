<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../config/config.php';

// Validate cashier access
require_cashier();
validate_role_area('cashier');

// Get cashier dashboard statistics
$today_sales_count = 0;
$today_revenue = 0;
$today_customers = 0;

$today = date('Y-m-d');

// Today's sales count
$result = $conn->query("SELECT COUNT(*) as count, SUM(Total_Amt) as revenue FROM sales WHERE S_Date = '$today' AND E_ID = '{$_SESSION['user']}'");
if ($result) {
    $row = $result->fetch_assoc();
    $today_sales_count = $row['count'] ?? 0;
    $today_revenue = $row['revenue'] ?? 0;
}

// Today's customers served by this cashier
$result = $conn->query("SELECT COUNT(DISTINCT C_ID) as count FROM sales WHERE S_Date = '$today' AND E_ID = '{$_SESSION['user']}'");
if ($result) {
    $row = $result->fetch_assoc();
    $today_customers = $row['count'] ?? 0;
}

// Recent transactions for this cashier
$recent_transactions = $conn->query("
    SELECT s.Sale_ID, s.S_Time, s.Total_Amt, c.C_Fname, c.C_Lname 
    FROM sales s 
    LEFT JOIN customer c ON s.C_ID = c.C_ID 
    WHERE s.S_Date = '$today' AND s.E_ID = '{$_SESSION['user']}'
    ORDER BY s.Sale_ID DESC LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Terminal - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('./sidebar.php'); ?>

    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Session Protocol</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Cashier Terminal</h1>
            <p class="text-slate-500 font-medium mt-1">Authorized access for <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
        </div>
        <div class="flex items-center space-x-3">
             <a href="sales/pos1.php" class="bg-blue-600 text-white px-8 py-4 rounded-2xl text-sm font-black shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all flex items-center group">
                <i class="fas fa-plus mr-3 group-hover:rotate-90 transition-transform"></i> New Transaction
            </a>
        </div>
    </div>

    <!-- Quick Stats Hub -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-receipt text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Today's Tickets</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $today_sales_count; ?></h3>
        </div>
        
        <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 backdrop-blur">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Terminal Revenue</p>
                <h3 class="text-3xl font-black text-white leading-none">Rs. <?php echo number_format($today_revenue, 2); ?></h3>
            </div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-blue-600/20 rounded-full blur-2xl"></div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                <i class="fas fa-user-check text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Patients Served</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $today_customers; ?></h3>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Recent Activity -->
        <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40">
            <h3 class="text-xl font-black text-slate-900 uppercase italic mb-8">Recent Transactions</h3>
            <div class="space-y-4">
                <?php if ($recent_transactions && $recent_transactions->num_rows > 0): ?>
                    <?php while($row = $recent_transactions->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-transparent hover:border-blue-200 transition-all">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-slate-100">
                                <i class="fas fa-file-invoice text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($row['C_Fname'] . ' ' . $row['C_Lname']); ?></p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase"><?php echo date('h:i A', strtotime($row['S_Time'])); ?></p>
                            </div>
                        </div>
                        <p class="text-sm font-black text-slate-900">Rs. <?php echo number_format($row['Total_Amt'], 2); ?></p>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="py-10 text-center">
                        <p class="text-slate-400 font-bold italic">No transactions processed in this session.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Shortcuts -->
        <div class="grid grid-cols-1 gap-6">
            <a href="sales/pos1.php" class="bg-blue-600 p-8 rounded-[2.5rem] shadow-xl shadow-blue-200 flex items-center justify-between group">
                <div class="flex items-center space-x-6">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white text-2xl backdrop-blur">
                        <i class="fas fa-cart-plus"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-white uppercase italic">Launch POS</h4>
                        <p class="text-blue-100 text-xs font-medium">Process new bills immediately</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-white/50 group-hover:translate-x-2 transition-transform"></i>
            </a>

            <a href="customers/view.php" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center justify-between group">
                <div class="flex items-center space-x-6">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 text-2xl">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-slate-900 uppercase italic">Patient Lookup</h4>
                        <p class="text-slate-400 text-xs font-medium">Verify profiles and loyalty points</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-slate-200 group-hover:translate-x-2 transition-transform"></i>
            </a>
        </div>
    </div>

    <!-- Layout Tags Closes -->
    </main>
    </div>
    </div>
</body>
</html>
