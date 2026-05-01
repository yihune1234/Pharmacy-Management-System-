<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get prescriptions with customer info
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (c.C_Fname LIKE '%$search%' OR c.C_Lname LIKE '%$search%' OR p.doctor_name LIKE '%$search%')";
}
if ($status_filter) {
    $where .= " AND p.status = '$status_filter'";
}

$sql = "
SELECT p.*, c.C_Fname, c.C_Lname, c.C_Phno, c.C_Mail
FROM prescriptions p
JOIN customer c ON p.customer_id = c.C_ID
$where
ORDER BY p.prescription_date DESC
";

$prescriptions = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions Management - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="subheading-premium">Medical Records</p>
            <h1 class="heading-premium">Prescriptions Management</h1>
            <p class="text-slate-500 font-medium mt-1">Track and manage patient prescriptions</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="add_prescription.php" class="btn-primary btn-slate !px-8">
                <i class="fas fa-plus-circle mr-3"></i> Add Prescription
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="premium-card mb-8">
        <form method="GET" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search by patient name or doctor..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
            </div>
            <select name="status" class="bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                <option value="">All Status</option>
                <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="Expired" <?php echo $status_filter === 'Expired' ? 'selected' : ''; ?>>Expired</option>
                <option value="Archived" <?php echo $status_filter === 'Archived' ? 'selected' : ''; ?>>Archived</option>
            </select>
            <button type="submit" class="btn-primary btn-slate">
                <i class="fas fa-search mr-2"></i> Filter
            </button>
        </form>
    </div>

    <!-- Prescriptions Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Prescription ID</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Doctor</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Date</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($prescriptions && $prescriptions->num_rows > 0): ?>
                        <?php while($row = $prescriptions->fetch_assoc()): ?>
                            <?php 
                                $status_colors = [
                                    'Active' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                    'Completed' => 'text-blue-600 bg-blue-50 border-blue-100',
                                    'Expired' => 'text-rose-600 bg-rose-50 border-rose-100',
                                    'Archived' => 'text-slate-600 bg-slate-50 border-slate-100'
                                ];
                                $status_color = $status_colors[$row['status']] ?? 'text-slate-600 bg-slate-50 border-slate-100';
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400">#RX-<?php echo str_pad($row['prescription_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($row['C_Fname'] . ' ' . $row['C_Lname']); ?></span>
                                        <span class="text-[10px] font-bold text-slate-400"><?php echo htmlspecialchars($row['C_Phno']); ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($row['doctor_name']); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-semibold text-slate-700"><?php echo date('M d, Y', strtotime($row['prescription_date'])); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-3 py-1 border <?php echo $status_color; ?> rounded-lg text-[9px] font-black tracking-widest uppercase">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-3">
                                        <a href="view_prescription.php?id=<?php echo $row['prescription_id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="View">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="add_prescription.php?id=<?php echo $row['prescription_id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Edit">
                                            <i class="fas fa-pen-nib text-xs"></i>
                                        </a>
                                        <a href="delete_prescription.php?id=<?php echo $row['prescription_id']; ?>" onclick="return confirm('Delete this prescription?')" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Delete">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-10 py-24 text-center">
                            <div class="opacity-10 mb-4 text-5xl"><i class="fas fa-file-medical"></i></div>
                            <p class="text-slate-400 font-bold italic">No prescriptions found.</p>
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
