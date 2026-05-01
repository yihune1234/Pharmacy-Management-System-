<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get expiry data with FIFO/FEFO implementation
$expiry_data = $conn->query("
    SELECT 
        m.Med_ID,
        m.Med_Name,
        p.P_ID as Batch_ID,
        p.P_ID as Batch_Number,
        p.P_Qty as Batch_Qty,
        p.Exp_Date,
        p.Mfg_Date,
        DATEDIFF(p.Exp_Date, CURDATE()) as days_to_expiry,
        CASE 
            WHEN p.Exp_Date < CURDATE() THEN 'Expired'
            WHEN DATEDIFF(p.Exp_Date, CURDATE()) <= 30 THEN 'Expiring Soon'
            ELSE 'Valid'
        END as expiry_status,
        s.Sup_Name
    FROM meds m
    JOIN purchase p ON m.Med_ID = p.Med_ID
    LEFT JOIN suppliers s ON p.Sup_ID = s.Sup_ID
    ORDER BY p.Exp_Date ASC
");

// Get summary statistics
$expired_count = $conn->query("SELECT COUNT(*) as count FROM purchase WHERE Exp_Date < CURDATE()")->fetch_assoc()['count'] ?? 0;
$expiring_soon = $conn->query("SELECT COUNT(*) as count FROM purchase WHERE Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND Exp_Date > CURDATE()")->fetch_assoc()['count'] ?? 0;
$valid_count = $conn->query("SELECT COUNT(*) as count FROM purchase WHERE Exp_Date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiry Management - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <main class="flex-1 overflow-auto ml-72">
        <!-- Header -->
        <header class="h-20 bg-white/70 backdrop-blur border-b border-slate-200/60 flex items-center justify-between px-8 sticky top-0 z-40">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Inventory Control</h2>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight leading-none">Expiry Management</h1>
            </div>
        </header>

        <!-- Content -->
        <div class="p-8">
            <!-- Page Header -->
            <div class="mb-10">
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Inventory Control</h2>
                <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">Expiry Management & FIFO</h1>
                <p class="text-slate-500 font-medium">Track medicine expiry dates and ensure FIFO/FEFO compliance</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Valid Stock</p>
                            <p class="text-3xl font-black text-emerald-600"><?php echo $valid_count; ?></p>
                        </div>
                        <i class="fas fa-check-circle text-4xl text-emerald-100"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Expiring Soon</p>
                            <p class="text-3xl font-black text-amber-600"><?php echo $expiring_soon; ?></p>
                        </div>
                        <i class="fas fa-exclamation-triangle text-4xl text-amber-100"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Expired</p>
                            <p class="text-3xl font-black text-rose-600"><?php echo $expired_count; ?></p>
                        </div>
                        <i class="fas fa-times-circle text-4xl text-rose-100"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Total Batches</p>
                            <p class="text-3xl font-black text-blue-600"><?php echo $valid_count + $expiring_soon + $expired_count; ?></p>
                        </div>
                        <i class="fas fa-boxes-stacked text-4xl text-blue-100"></i>
                    </div>
                </div>
            </div>

            <!-- Expiry Details Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div class="px-8 py-6 border-b border-slate-200 flex items-center gap-3">
                    <i class="fas fa-calendar-times text-rose-600 text-lg"></i>
                    <h2 class="text-lg font-bold text-slate-900">Batch Expiry Status</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Medicine</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Batch #</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Qty</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Mfg Date</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Exp Date</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Days Left</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Supplier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($expiry_data && $expiry_data->num_rows > 0): ?>
                                <?php while($row = $expiry_data->fetch_assoc()): ?>
                                    <?php 
                                        $status_class = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                        if ($row['expiry_status'] === 'Expired') {
                                            $status_class = 'bg-rose-50 text-rose-700 border-rose-100';
                                        } elseif ($row['expiry_status'] === 'Expiring Soon') {
                                            $status_class = 'bg-amber-50 text-amber-700 border-amber-100';
                                        }
                                        
                                        $days_class = 'text-emerald-600';
                                        if ($row['days_to_expiry'] < 0) {
                                            $days_class = 'text-rose-600';
                                        } elseif ($row['days_to_expiry'] <= 30) {
                                            $days_class = 'text-amber-600';
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-4"><strong class="text-slate-900"><?php echo htmlspecialchars($row['Med_Name']); ?></strong></td>
                                        <td class="px-8 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($row['Batch_Number']); ?></td>
                                        <td class="px-8 py-4 text-sm font-semibold text-slate-900"><?php echo $row['Batch_Qty']; ?></td>
                                        <td class="px-8 py-4 text-sm text-slate-600"><?php echo date('M d, Y', strtotime($row['Mfg_Date'])); ?></td>
                                        <td class="px-8 py-4 text-sm text-slate-600"><?php echo date('M d, Y', strtotime($row['Exp_Date'])); ?></td>
                                        <td class="px-8 py-4 text-sm font-bold <?php echo $days_class; ?>"><?php echo $row['days_to_expiry']; ?> days</td>
                                        <td class="px-8 py-4">
                                            <span class="px-3 py-1 border rounded-lg text-xs font-black tracking-widest uppercase <?php echo $status_class; ?>">
                                                <?php echo $row['expiry_status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($row['Sup_Name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-8 py-16 text-center">
                                        <i class="fas fa-calendar text-5xl text-slate-200 mb-4 block"></i>
                                        <p class="text-slate-400 font-bold italic">No batches found.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FIFO/FEFO Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        FIFO/FEFO Implementation
                    </h3>
                    <div class="space-y-3 text-sm text-slate-600">
                        <p><strong class="text-slate-900">FIFO (First In, First Out):</strong> Oldest batches are sold first based on manufacturing date.</p>
                        <p><strong class="text-slate-900">FEFO (First Expired, First Out):</strong> Batches closest to expiry are prioritized for sale.</p>
                        <p><strong class="text-slate-900">Compliance Check:</strong> System automatically verifies FIFO/FEFO order to prevent expired medicines from being sold.</p>
                        <p><strong class="text-slate-900">POS Integration:</strong> Point of Sale system highlights near-expiry stock for priority sales.</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <i class="fas fa-shield-alt text-emerald-600"></i>
                        Expiry Prevention Features
                    </h3>
                    <div class="space-y-3 text-sm text-slate-600">
                        <p><i class="fas fa-check text-emerald-600 mr-2"></i> Automatic expiry status tracking</p>
                        <p><i class="fas fa-check text-emerald-600 mr-2"></i> Prevents selling expired medicines</p>
                        <p><i class="fas fa-check text-emerald-600 mr-2"></i> 30-day expiry alerts</p>
                        <p><i class="fas fa-check text-emerald-600 mr-2"></i> Batch-level expiry management</p>
                        <p><i class="fas fa-check text-emerald-600 mr-2"></i> Automated archival of expired stock</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
