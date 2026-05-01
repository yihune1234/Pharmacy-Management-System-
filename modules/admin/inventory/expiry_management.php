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
        mb.Batch_ID,
        mb.Batch_Number,
        mb.Batch_Qty,
        mb.Exp_Date,
        mb.Mfg_Date,
        DATEDIFF(mb.Exp_Date, CURDATE()) as days_to_expiry,
        CASE 
            WHEN mb.Exp_Date < CURDATE() THEN 'Expired'
            WHEN DATEDIFF(mb.Exp_Date, CURDATE()) <= 30 THEN 'Expiring Soon'
            ELSE 'Valid'
        END as expiry_status,
        s.Sup_Name,
        mb.is_fifo_compliant
    FROM meds m
    JOIN medicine_batches mb ON m.Med_ID = mb.Med_ID
    LEFT JOIN suppliers s ON mb.Supplier_ID = s.Sup_ID
    ORDER BY mb.Exp_Date ASC
");

// Get summary statistics
$expired_count = $conn->query("SELECT COUNT(*) as count FROM medicine_batches WHERE Exp_Date < CURDATE()")->fetch_assoc()['count'] ?? 0;
$expiring_soon = $conn->query("SELECT COUNT(*) as count FROM medicine_batches WHERE Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND Exp_Date > CURDATE()")->fetch_assoc()['count'] ?? 0;
$valid_count = $conn->query("SELECT COUNT(*) as count FROM medicine_batches WHERE Exp_Date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['count'] ?? 0;

// Handle batch status update
if (isset($_POST['update_batch_status'])) {
    $batch_id = (int)$_POST['batch_id'];
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE medicine_batches SET expiry_status = ? WHERE Batch_ID = ?");
    $stmt->bind_param("si", $new_status, $batch_id);
    
    if ($stmt->execute()) {
        set_flash_message("Batch status updated successfully!", "success");
    } else {
        set_flash_message("Error updating batch status: " . $stmt->error, "error");
    }
    $stmt->close();
    header("Location: expiry_management.php");
    exit();
}

// Handle FIFO compliance check
if (isset($_POST['check_fifo'])) {
    $med_id = (int)$_POST['med_id'];
    
    // Get batches ordered by manufacturing date (FIFO)
    $batches = $conn->query("
        SELECT Batch_ID, Batch_Number, Mfg_Date, Exp_Date, Batch_Qty
        FROM medicine_batches
        WHERE Med_ID = $med_id
        ORDER BY Mfg_Date ASC
    ");
    
    $fifo_compliant = true;
    $prev_exp_date = null;
    
    while ($batch = $batches->fetch_assoc()) {
        if ($prev_exp_date && $batch['Exp_Date'] < $prev_exp_date) {
            $fifo_compliant = false;
            break;
        }
        $prev_exp_date = $batch['Exp_Date'];
    }
    
    // Update FIFO compliance status
    $compliance_status = $fifo_compliant ? 1 : 0;
    $conn->query("UPDATE medicine_batches SET is_fifo_compliant = $compliance_status WHERE Med_ID = $med_id");
    
    set_flash_message($fifo_compliant ? "FIFO compliance verified!" : "FIFO compliance issue detected!", $fifo_compliant ? "success" : "warning");
    header("Location: expiry_management.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiry Management - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="subheading-premium">Inventory Control</p>
            <h1 class="heading-premium">Expiry Management & FIFO</h1>
            <p class="text-slate-500 font-medium mt-1">Track medicine expiry dates and ensure FIFO/FEFO compliance</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Valid Stock</p>
                    <p class="text-3xl font-black text-emerald-600"><?php echo $valid_count; ?></p>
                </div>
                <i class="fas fa-check-circle text-4xl text-emerald-100"></i>
            </div>
        </div>

        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Expiring Soon</p>
                    <p class="text-3xl font-black text-amber-600"><?php echo $expiring_soon; ?></p>
                </div>
                <i class="fas fa-exclamation-triangle text-4xl text-amber-100"></i>
            </div>
        </div>

        <div class="premium-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Expired</p>
                    <p class="text-3xl font-black text-rose-600"><?php echo $expired_count; ?></p>
                </div>
                <i class="fas fa-times-circle text-4xl text-rose-100"></i>
            </div>
        </div>

        <div class="premium-card">
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
    <div class="premium-card overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-900 flex items-center">
                <i class="fas fa-calendar-times text-rose-600 mr-3"></i>
                Batch Expiry Status
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Medicine</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Batch #</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Qty</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Mfg Date</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Exp Date</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Days Left</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">FIFO</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($expiry_data && $expiry_data->num_rows > 0): ?>
                        <?php while($row = $expiry_data->fetch_assoc()): ?>
                            <?php 
                                $status_colors = [
                                    'Valid' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                    'Expiring Soon' => 'text-amber-600 bg-amber-50 border-amber-100',
                                    'Expired' => 'text-rose-600 bg-rose-50 border-rose-100'
                                ];
                                $status_color = $status_colors[$row['expiry_status']] ?? 'text-slate-600 bg-slate-50 border-slate-100';
                                
                                $fifo_status = $row['is_fifo_compliant'] ? 'text-emerald-600' : 'text-rose-600';
                                $fifo_icon = $row['is_fifo_compliant'] ? 'fa-check' : 'fa-times';
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['Med_Name']); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400"><?php echo htmlspecialchars($row['Batch_Number']); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold text-slate-900"><?php echo $row['Batch_Qty']; ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo date('M d, Y', strtotime($row['Mfg_Date'])); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo date('M d, Y', strtotime($row['Exp_Date'])); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold <?php echo $row['days_to_expiry'] < 0 ? 'text-rose-600' : ($row['days_to_expiry'] <= 30 ? 'text-amber-600' : 'text-emerald-600'); ?>">
                                        <?php echo $row['days_to_expiry']; ?> days
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-3 py-1 border <?php echo $status_color; ?> rounded-lg text-[9px] font-black tracking-widest uppercase">
                                        <?php echo $row['expiry_status']; ?>
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <i class="fas <?php echo $fifo_icon; ?> text-lg <?php echo $fifo_status; ?>"></i>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-2">
                                        <?php if ($row['expiry_status'] === 'Expired'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="batch_id" value="<?php echo $row['Batch_ID']; ?>">
                                                <input type="hidden" name="status" value="Archived">
                                                <button type="submit" name="update_batch_status" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-600 hover:text-white transition-all shadow-sm" title="Archive">
                                                    <i class="fas fa-archive text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="px-10 py-24 text-center">
                            <div class="opacity-10 mb-4 text-5xl"><i class="fas fa-calendar"></i></div>
                            <p class="text-slate-400 font-bold italic">No batches found.</p>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FIFO/FEFO Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <div class="premium-card">
            <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                FIFO/FEFO Implementation
            </h2>
            <div class="space-y-3 text-sm text-slate-600">
                <p><strong>FIFO (First In, First Out):</strong> Oldest batches are sold first based on manufacturing date.</p>
                <p><strong>FEFO (First Expired, First Out):</strong> Batches closest to expiry are prioritized for sale.</p>
                <p><strong>Compliance Check:</strong> System automatically verifies FIFO/FEFO order to prevent expired medicines from being sold.</p>
                <p><strong>POS Integration:</strong> Point of Sale system highlights near-expiry stock for priority sales.</p>
            </div>
        </div>

        <div class="premium-card">
            <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                <i class="fas fa-shield-alt text-emerald-600 mr-3"></i>
                Expiry Prevention Features
            </h2>
            <div class="space-y-3 text-sm text-slate-600">
                <p><i class="fas fa-check text-emerald-600 mr-2"></i> Automatic expiry status tracking</p>
                <p><i class="fas fa-check text-emerald-600 mr-2"></i> Prevents selling expired medicines</p>
                <p><i class="fas fa-check text-emerald-600 mr-2"></i> 30-day expiry alerts</p>
                <p><i class="fas fa-check text-emerald-600 mr-2"></i> Batch-level expiry management</p>
                <p><i class="fas fa-check text-emerald-600 mr-2"></i> Automated archival of expired stock</p>
            </div>
        </div>
    </div>

    <!-- Closing Sidebar Tags -->
    </main>
    </div>
    </div>
</body>
</html>
