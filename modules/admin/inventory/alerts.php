<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

// Get expiry alerts
$expiry_sql = "SELECT m.Med_Name, mb.Batch_Number, mb.Exp_Date, mb.Batch_Qty,
                     DATEDIFF(mb.Exp_Date, CURDATE()) as Days_To_Expiry,
                     CASE 
                         WHEN DATEDIFF(mb.Exp_Date, CURDATE()) <= 0 THEN 'Expired'
                         WHEN DATEDIFF(mb.Exp_Date, CURDATE()) <= 30 THEN 'Expiring Soon'
                         ELSE 'Valid'
                     END as Status
              FROM meds m
              LEFT JOIN medicine_batches mb ON m.Med_ID = mb.Med_ID
              WHERE mb.Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              ORDER BY mb.Exp_Date ASC";

$expiry_result = $conn->query($expiry_sql);

// Get low stock alerts
$low_stock_sql = "SELECT m.Med_ID, m.Med_Name, m.Med_Qty, m.Location_Rack, m.Min_Stock_Level
                 FROM meds m 
                 WHERE m.Med_Qty <= m.Min_Stock_Level
                 ORDER BY m.Med_Qty ASC";

$low_stock_result = $conn->query($low_stock_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Alerts - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Inventory Alerts</h2>
        <p class="text-slate-500 mt-1">Monitor stock levels and medicine expiry dates.</p>
    </div>

    <!-- Expiry Alerts Section -->
    <div class="mb-12">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-red-50 to-orange-50 px-8 py-6 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900 flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Expiry Alerts
                    <span class="ml-3 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">
                        <?php echo $expiry_result ? $expiry_result->num_rows : 0; ?> items
                    </span>
                </h3>
                <p class="text-slate-600 mt-2">Medicines expiring within 30 days</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Batch Number</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Days Left</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($expiry_result && $expiry_result->num_rows > 0): ?>
                            <?php while($row = $expiry_result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['Med_Name']); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($row['Batch_Number'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M j, Y', strtotime($row['Exp_Date'])); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($row['Days_To_Expiry'] <= 0): ?>
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">Expired</span>
                                        <?php elseif ($row['Days_To_Expiry'] <= 7): ?>
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold"><?php echo $row['Days_To_Expiry']; ?> days</span>
                                        <?php else: ?>
                                            <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded-full text-xs font-bold"><?php echo $row['Days_To_Expiry']; ?> days</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo $row['Batch_Qty']; ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($row['Status'] == 'Expired'): ?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Expired</span>
                                        <?php else: ?>
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">Expiring Soon</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                    <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    No medicines expiring within 30 days
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts Section -->
    <div class="mb-12">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-8 py-6 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900 flex items-center">
                    <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    Low Stock Alerts
                    <span class="ml-3 bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">
                        <?php echo $low_stock_result ? $low_stock_result->num_rows : 0; ?> items
                    </span>
                </h3>
                <p class="text-slate-600 mt-2">Medicines below minimum stock level</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Min Level</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($low_stock_result && $low_stock_result->num_rows > 0): ?>
                            <?php while($row = $low_stock_result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['Med_Name']); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold"><?php echo $row['Med_Qty']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo $row['Min_Stock_Level']; ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($row['Location_Rack']); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Critical</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="edit.php?id=<?php echo $row['Med_ID']; ?>" class="text-blue-600 hover:text-blue-800 font-bold text-sm">Update Stock</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                    <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    All medicines are above minimum stock level
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="add.php" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">Add New Medicine</h4>
                    <p class="text-sm text-slate-500">Register new pharmaceutical items</p>
                </div>
            </div>
        </a>

        <a href="view.php" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900">View All Inventory</h4>
                    <p class="text-sm text-slate-500">Browse complete medicine list</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
