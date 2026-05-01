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
            <p class="subheading-premium">Session Protocol</p>
            <h1 class="heading-premium">Cashier Terminal</h1>
            <p class="text-slate-500 font-medium mt-1">Authorized access for <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
        </div>
        <div class="flex items-center space-x-3">
             <a href="sales/pos1.php" class="btn-primary btn-blue">
                <i class="fas fa-plus mr-3 group-hover:rotate-90 transition-transform"></i> New Transaction
            </a>
        </div>
    </div>

    <!-- Quick Stats Hub -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="premium-card p-8">
            <div class="stat-icon bg-blue-50 text-blue-600">
                <i class="fas fa-receipt text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Today's Tickets</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $today_sales_count; ?></h3>
        </div>
        
        <div class="premium-card p-8 bg-slate-900 !border-slate-800 shadow-2xl">
            <div class="relative z-10">
                <div class="stat-icon bg-white/10 text-white backdrop-blur">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Terminal Revenue</p>
                <h3 class="text-3xl font-black text-white leading-none">Rs. <?php echo number_format($today_revenue, 2); ?></h3>
            </div>
        </div>

        <div class="premium-card p-8">
            <div class="stat-icon bg-emerald-50 text-emerald-600">
                <i class="fas fa-user-check text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Patients Served</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $today_customers; ?></h3>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Recent Activity -->
        <div class="premium-card p-10">
            <h3 class="text-xl font-black text-slate-900 uppercase italic mb-8">Recent Transactions</h3>
            <div class="space-y-4">
                <?php if ($recent_transactions && $recent_transactions->num_rows > 0): ?>
                    <?php while($row = $recent_transactions->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-5 bg-slate-50/50 rounded-2xl border border-transparent hover:border-blue-200 transition-all group">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white shadow-sm transition-all">
                                <i class="fas fa-file-invoice text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($row['C_Fname'] . ' ' . $row['C_Lname']); ?></p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase"><?php echo date('h:i A', strtotime($row['S_Time'])); ?></p>
                            </div>
                        </div>
                        <p class="text-sm font-black text-slate-900">Rs. <?php echo number_format($row['Total_Amt'], 0); ?></p>
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
            <a href="sales/pos1.php" class="premium-card bg-blue-600 p-8 shadow-xl shadow-blue-200 flex items-center justify-between group !rounded-[2rem]">
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

            <a href="customers/view.php" class="premium-card p-8 flex items-center justify-between group !rounded-[2rem]">
                <div class="flex items-center space-x-6">
                    <div class="stat-icon !mb-0 bg-emerald-50 text-emerald-600 text-2xl">
                        <i class="fas fa-id-badge text-xl"></i>
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
