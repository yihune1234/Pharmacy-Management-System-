<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get payment data with reconciliation
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : date('Y-m-d');
$method_filter = isset($_GET['method']) ? $conn->real_escape_string($_GET['method']) : '';

$where = "WHERE pm.created_at >= '$date_from' AND pm.created_at <= '$date_to'";
if ($method_filter) {
    $where .= " AND pm.method = '$method_filter'";
}

// Get payment reconciliation data
$payments = $conn->query("
    SELECT pm.*, s.S_Date, s.Total_Amt, c.C_Fname, c.C_Lname, e.E_Fname, e.E_Lname
    FROM payment_methods pm
    JOIN sales s ON pm.sale_id = s.Sale_ID
    LEFT JOIN customer c ON s.C_ID = c.C_ID
    LEFT JOIN employee e ON s.E_ID = e.E_ID
    $where
    ORDER BY pm.created_at DESC
");

// Get payment summary by method
$summary = $conn->query("
    SELECT 
        method,
        COUNT(*) as transaction_count,
        SUM(amount) as total_amount,
        AVG(amount) as avg_amount
    FROM payment_methods
    $where
    GROUP BY method
    ORDER BY total_amount DESC
");

// Get daily payment summary
$daily_summary = $conn->query("
    SELECT 
        DATE(pm.created_at) as payment_date,
        COUNT(*) as transaction_count,
        SUM(pm.amount) as daily_total
    FROM payment_methods pm
    $where
    GROUP BY DATE(pm.created_at)
    ORDER BY payment_date DESC
");

// Calculate totals
$total_result = $conn->query("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(amount) as total_amount,
        COUNT(DISTINCT sale_id) as unique_sales
    FROM payment_methods
    $where
");
$totals = $total_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods & Reconciliation - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="subheading-premium">Financial Management</p>
            <h1 class="heading-premium">Payment Methods & Reconciliation</h1>
            <p class="text-slate-500 font-medium mt-1">Track and reconcile payments across all methods</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Total Transactions</p>
                    <p class="text-3xl font-black text-blue-600"><?php echo $totals['total_transactions'] ?? 0; ?></p>
                </div>
                <i class="fas fa-receipt text-4xl text-blue-100"></i>
            </div>
        </div>

        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Total Amount</p>
                    <p class="text-3xl font-black text-emerald-600">Rs. <?php echo number_format($totals['total_amount'] ?? 0, 2); ?></p>
                </div>
                <i class="fas fa-money-bill-wave text-4xl text-emerald-100"></i>
            </div>
        </div>

        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Unique Sales</p>
                    <p class="text-3xl font-black text-purple-600"><?php echo $totals['unique_sales'] ?? 0; ?></p>
                </div>
                <i class="fas fa-shopping-cart text-4xl text-purple-100"></i>
            </div>
        </div>

        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Avg Transaction</p>
                    <p class="text-3xl font-black text-amber-600">Rs. <?php echo number_format(($totals['total_transactions'] > 0 ? $totals['total_amount'] / $totals['total_transactions'] : 0), 2); ?></p>
                </div>
                <i class="fas fa-chart-line text-4xl text-amber-100"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="premium-card mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">From Date</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">To Date</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Payment Method</label>
                <select name="method" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    <option value="">All Methods</option>
                    <option value="Cash" <?php echo $method_filter === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="Card" <?php echo $method_filter === 'Card' ? 'selected' : ''; ?>>Card</option>
                    <option value="Mobile Money" <?php echo $method_filter === 'Mobile Money' ? 'selected' : ''; ?>>Mobile Money</option>
                    <option value="Check" <?php echo $method_filter === 'Check' ? 'selected' : ''; ?>>Check</option>
                    <option value="Credit" <?php echo $method_filter === 'Credit' ? 'selected' : ''; ?>>Credit</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full btn-primary btn-blue">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Payment Summary by Method -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Summary Table -->
        <div class="premium-card overflow-hidden">
            <h2 class="text-lg font-bold text-slate-900 mb-6 px-10 pt-8 flex items-center">
                <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                Summary by Payment Method
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Method</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Transactions</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Amount</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Average</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($summary && $summary->num_rows > 0): ?>
                            <?php while($row = $summary->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50/30 transition-all">
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['method']); ?></span>
                                    </td>
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-slate-900"><?php echo $row['transaction_count']; ?></span>
                                    </td>
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-emerald-600">Rs. <?php echo number_format($row['total_amount'], 2); ?></span>
                                    </td>
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-slate-700">Rs. <?php echo number_format($row['avg_amount'], 2); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="px-10 py-8 text-center text-slate-400">No payment data found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Summary -->
        <div class="premium-card overflow-hidden">
            <h2 class="text-lg font-bold text-slate-900 mb-6 px-10 pt-8 flex items-center">
                <i class="fas fa-calendar-alt text-amber-600 mr-3"></i>
                Daily Payment Summary
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Date</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Transactions</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Daily Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($daily_summary && $daily_summary->num_rows > 0): ?>
                            <?php while($row = $daily_summary->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50/30 transition-all">
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-slate-900"><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></span>
                                    </td>
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-slate-900"><?php echo $row['transaction_count']; ?></span>
                                    </td>
                                    <td class="px-10 py-6">
                                        <span class="text-sm font-bold text-emerald-600">Rs. <?php echo number_format($row['daily_total'], 2); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="px-10 py-8 text-center text-slate-400">No payment data found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Payment Transactions -->
    <div class="premium-card overflow-hidden">
        <h2 class="text-lg font-bold text-slate-900 mb-6 px-10 pt-8 flex items-center">
            <i class="fas fa-list text-slate-600 mr-3"></i>
            Payment Transactions
        </h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Payment ID</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Sale Date</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Customer</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Method</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Amount</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reference</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($payments && $payments->num_rows > 0): ?>
                        <?php while($row = $payments->fetch_assoc()): ?>
                            <?php 
                                $status_colors = [
                                    'Completed' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                    'Pending' => 'text-amber-600 bg-amber-50 border-amber-100',
                                    'Failed' => 'text-rose-600 bg-rose-50 border-rose-100',
                                    'Refunded' => 'text-slate-600 bg-slate-50 border-slate-100'
                                ];
                                $status_color = $status_colors[$row['status']] ?? 'text-slate-600 bg-slate-50 border-slate-100';
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400">#PAY-<?php echo str_pad($row['payment_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo date('M d, Y', strtotime($row['S_Date'])); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(($row['C_Fname'] ?? 'N/A') . ' ' . ($row['C_Lname'] ?? '')); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-3 py-1 bg-blue-50 border border-blue-100 rounded-lg text-[9px] font-black tracking-widest uppercase text-blue-600">
                                        <?php echo htmlspecialchars($row['method']); ?>
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold text-emerald-600">Rs. <?php echo number_format($row['amount'], 2); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-semibold text-slate-600"><?php echo htmlspecialchars($row['reference'] ?? 'N/A'); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-3 py-1 border <?php echo $status_color; ?> rounded-lg text-[9px] font-black tracking-widest uppercase">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="px-10 py-24 text-center">
                            <div class="opacity-10 mb-4 text-5xl"><i class="fas fa-receipt"></i></div>
                            <p class="text-slate-400 font-bold italic">No payment transactions found.</p>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Closing Sidebar Tags -->
    </main>
    </div>
    </div>
</body>
</html>
