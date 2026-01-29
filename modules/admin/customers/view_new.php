<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get customer statistics
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'];
$loyalty_customers = $conn->query("SELECT COUNT(*) as count FROM customer WHERE Loyalty_Points > 0")->fetch_assoc()['count'];

// Get customers with sales history
$sql = "SELECT c.*, 
               COUNT(s.Sale_ID) as Total_Sales,
               COALESCE(SUM(s.Total_Amt), 0) as Total_Spent,
               MAX(s.S_Date) as Last_Purchase_Date,
               CASE 
                   WHEN c.Loyalty_Points >= 1000 THEN 'Gold'
                   WHEN c.Loyalty_Points >= 500 THEN 'Silver'
                   ELSE 'Bronze'
               END as Loyalty_Tier
        FROM customer c
        LEFT JOIN sales s ON c.C_ID = s.C_ID AND s.Refunded = 0
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

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Customer Management</h2>
            <p class="text-slate-500 mt-1 font-medium">Manage customers and track purchase history</p>
        </div>
        <div class="flex space-x-3">
            <a href="loyalty_report.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Loyalty Report
            </a>
            <a href="add_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Customer
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Customers</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $total_customers; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Loyalty Members</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $loyalty_customers; ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active This Month</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $active_month = $conn->query("SELECT COUNT(DISTINCT C_ID) as count FROM sales WHERE MONTH(S_Date) = MONTH(CURDATE()) AND YEAR(S_Date) = YEAR(CURDATE()) AND Refunded = 0")->fetch_assoc()['count'];
                        echo $active_month;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg. Customer Value</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $avg_value = $total_customers > 0 ? 
                            $conn->query("SELECT COALESCE(SUM(Total_Amt), 0) as total FROM sales WHERE Refunded = 0")->fetch_assoc()['total'] / $total_customers : 0;
                        echo number_format($avg_value, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Customer Directory</h3>
            <p class="text-slate-600 mt-2">Complete customer database with purchase history and loyalty status</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Loyalty Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Last Purchase</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($customer = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo str_pad($customer['C_ID'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></div>
                                    <div class="text-xs text-slate-500">Age: <?php echo $customer['C_Age']; ?> | <?php echo $customer['C_Sex']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="text-xs"><?php echo htmlspecialchars($customer['C_Phno'] ?? 'N/A'); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($customer['C_Mail'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo $customer['Total_Sales']; ?> sales
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($customer['Total_Spent'], 2); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $tier_color = ['Bronze' => 'bg-slate-100 text-slate-700', 'Silver' => 'bg-gray-100 text-gray-700', 'Gold' => 'bg-amber-100 text-amber-700'];
                                    $tier = $customer['Loyalty_Tier'];
                                    ?>
                                    <span class="<?php echo $tier_color[$tier]; ?> px-3 py-1 rounded-full text-xs font-bold">
                                        <?php echo $tier; ?> (<?php echo $customer['Loyalty_Points'] ?? 0; ?> pts)
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if ($customer['Last_Purchase_Date']): ?>
                                        <?php echo date('M j, Y', strtotime($customer['Last_Purchase_Date'])); ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">No purchases</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="customer_history.php?id=<?php echo $customer['C_ID']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        History
                                    </a>
                                    <a href="edit_new.php?id=<?php echo $customer['C_ID']; ?>" 
                                       class="text-emerald-600 hover:text-emerald-800 font-bold text-sm mr-3">
                                        Edit
                                    </a>
                                    <a href="customer_invoices.php?id=<?php echo $customer['C_ID']; ?>" 
                                       class="text-purple-600 hover:text-purple-800 font-bold text-sm">
                                        Invoices
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                No customers found
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
