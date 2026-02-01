<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get customer statistics
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'];
$loyalty_customers = 0; // Default since column missing

// Get customers with sales history
$sql = "SELECT c.*, 
               COUNT(s.Sale_ID) as Total_Sales,
               COALESCE(SUM(s.Total_Amt), 0) as Total_Spent,
               MAX(s.S_Date) as Last_Purchase_Date,
               'Bronze' as Loyalty_Tier
        FROM customer c
        LEFT JOIN sales s ON c.C_ID = s.C_ID
        GROUP BY c.C_ID
        ORDER BY c.C_ID DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <p class="subheading-premium">Relationship Management</p>
            <h1 class="heading-premium">Customer Portfolio</h1>
            <p class="text-slate-500 mt-1 font-medium">Manage patients and track transactional history.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="loyalty_report.php" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-crown mr-2 text-amber-500"></i> Loyalty Analytics
            </a>
            <a href="add_new.php" class="btn-primary btn-blue !px-8">
                <i class="fas fa-user-plus mr-3 transform group-hover:scale-110 transition-transform"></i> Register Member
            </a>
        </div>
    </div>

    <!-- Analytics Hub -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
        <div class="premium-card p-8">
            <div class="stat-icon bg-blue-50 text-blue-600 mb-6">
                <i class="fas fa-users text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Base Size</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $total_customers; ?></h3>
            <p class="text-[10px] font-bold text-blue-500 mt-3 uppercase">Registered Members</p>
        </div>

        <div class="premium-card p-8 bg-slate-900 !border-slate-800 shadow-2xl">
            <div class="relative z-10">
                <div class="stat-icon bg-white/10 text-white backdrop-blur mb-6">
                    <i class="fas fa-gem text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Loyalty Tier</p>
                <h3 class="text-3xl font-black text-white leading-none"><?php echo $loyalty_customers; ?></h3>
                <p class="text-[10px] font-bold text-amber-400 mt-3 uppercase tracking-widest">Premium Members</p>
            </div>
        </div>

        <div class="premium-card p-8">
            <div class="stat-icon bg-emerald-50 text-emerald-600 mb-6">
                <i class="fas fa-pulse text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Retention</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php 
                $active_month = $conn->query("SELECT COUNT(DISTINCT C_ID) as count FROM sales WHERE MONTH(S_Date) = MONTH(CURDATE()) AND YEAR(S_Date) = YEAR(CURDATE())")->fetch_assoc()['count'];
                echo $active_month;
            ?></h3>
            <p class="text-[10px] font-bold text-emerald-500 mt-3 uppercase tracking-tighter">Active This Month</p>
        </div>

        <div class="premium-card p-8">
            <div class="stat-icon bg-purple-50 text-purple-600 mb-6">
                <i class="fas fa-coins text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Unit LTV</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none">Rs. <?php 
                $avg_value = $total_customers > 0 ? 
                    $conn->query("SELECT COALESCE(SUM(Total_Amt), 0) as total FROM sales")->fetch_assoc()['total'] / $total_customers : 0;
                echo number_format($avg_value, 0);
            ?></h3>
            <p class="text-[10px] font-bold text-purple-500 mt-3 uppercase tracking-tighter">Average Life Value</p>
        </div>
    </div>

    <!-- Technical Matrix Table -->
    <div class="premium-card overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900 uppercase italic">Customer Directory</h3>
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-blue-500 pulse"></span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Live Database</span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-white">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Signature</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Member Identity</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Communication</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Frequency</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Gross Volume</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Protocol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($customer = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-8 py-6">
                                    <span class="text-[10px] font-black text-slate-400 tracking-widest">#MBR-<?php echo str_pad($customer['C_ID'], 4, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center font-black text-xs text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            <?php echo strtoupper(substr($customer['C_Fname'], 0, 1)); ?>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors italic"><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic"><?php echo $customer['C_Age']; ?> YRS | <?php echo $customer['C_Sex']; ?> Classification</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-600"><?php echo htmlspecialchars($customer['C_Phno'] ?? 'NULL'); ?></span>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic"><?php echo htmlspecialchars($customer['C_Mail'] ?? 'NO_ANCHOR'); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-[10px] font-black uppercase tracking-widest italic border border-blue-100">
                                        <?php echo $customer['Total_Sales']; ?> PROTOCOLS
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right font-black text-slate-900 italic">Rs. <?php echo number_format($customer['Total_Spent'], 0); ?></td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="customer_history.php?id=<?php echo $customer['C_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-clock-rotate-left text-xs"></i>
                                        </a>
                                        <a href="edit_new.php?id=<?php echo $customer['C_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-user-pen text-xs"></i>
                                        </a>
                                        <a href="customer_invoices.php?id=<?php echo $customer['C_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-file-invoice-dollar text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="opacity-10 mb-4 text-5xl"><i class="fas fa-users-slash"></i></div>
                                <p class="text-slate-400 font-bold italic">No member synchronization found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
