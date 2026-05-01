<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$prescription_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($prescription_id <= 0) {
    set_flash_message("Invalid prescription ID.", "error");
    header("Location: prescriptions.php");
    exit();
}

// Get prescription details
$stmt = $conn->prepare("
    SELECT p.*, c.C_Fname, c.C_Lname, c.C_Phno, c.C_Mail, c.C_Age, c.C_Sex
    FROM prescriptions p
    JOIN customer c ON p.customer_id = c.C_ID
    WHERE p.prescription_id = ?
");
$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$result = $stmt->get_result();
$prescription = $result->fetch_assoc();
$stmt->close();

if (!$prescription) {
    set_flash_message("Prescription not found.", "error");
    header("Location: prescriptions.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Prescription - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <p class="subheading-premium">Medical Records</p>
            <h1 class="heading-premium">Prescription Details</h1>
        </div>
        <div class="flex gap-3">
            <a href="add_prescription.php?id=<?php echo $prescription_id; ?>" class="btn-primary btn-amber">
                <i class="fas fa-pen-nib mr-2"></i> Edit
            </a>
            <a href="prescriptions.php" class="btn-primary btn-slate">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Prescription Info Card -->
            <div class="premium-card">
                <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-file-medical text-blue-600 mr-3"></i>
                    Prescription Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Prescription ID</p>
                        <p class="text-lg font-bold text-slate-900">#RX-<?php echo str_pad($prescription['prescription_id'], 5, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Status</p>
                        <?php 
                            $status_colors = [
                                'Active' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                'Completed' => 'text-blue-600 bg-blue-50 border-blue-100',
                                'Expired' => 'text-rose-600 bg-rose-50 border-rose-100',
                                'Archived' => 'text-slate-600 bg-slate-50 border-slate-100'
                            ];
                            $status_color = $status_colors[$prescription['status']] ?? 'text-slate-600 bg-slate-50 border-slate-100';
                        ?>
                        <span class="px-3 py-1 border <?php echo $status_color; ?> rounded-lg text-[9px] font-black tracking-widest uppercase inline-block">
                            <?php echo $prescription['status']; ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Prescription Date</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo date('M d, Y', strtotime($prescription['prescription_date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Doctor Name</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($prescription['doctor_name']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Patient Info Card -->
            <div class="premium-card">
                <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <i class="fas fa-user-circle text-emerald-600 mr-3"></i>
                    Patient Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Full Name</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($prescription['C_Fname'] . ' ' . $prescription['C_Lname']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Age / Gender</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo $prescription['C_Age'] ?? 'N/A'; ?> / <?php echo $prescription['C_Sex'] === 'M' ? 'Male' : 'Female'; ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($prescription['C_Phno']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($prescription['C_Mail']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if ($prescription['notes']): ?>
            <div class="premium-card">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-amber-600 mr-3"></i>
                    Notes
                </h2>
                <p class="text-slate-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($prescription['notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- File Section -->
            <?php if ($prescription['file_path']): ?>
            <div class="premium-card">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-file text-blue-600 mr-3"></i>
                    Prescription File
                </h2>
                <a href="../../<?php echo htmlspecialchars($prescription['file_path']); ?>" target="_blank" 
                   class="w-full bg-blue-50 border border-blue-200 rounded-2xl px-4 py-6 text-center hover:bg-blue-100 transition-all">
                    <i class="fas fa-download text-blue-600 text-2xl mb-2 block"></i>
                    <span class="text-sm font-bold text-blue-600">Download File</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div class="premium-card">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-slate-600 mr-3"></i>
                    Timeline
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Created</p>
                        <p class="text-sm font-semibold text-slate-700"><?php echo date('M d, Y H:i', strtotime($prescription['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Last Updated</p>
                        <p class="text-sm font-semibold text-slate-700"><?php echo date('M d, Y H:i', strtotime($prescription['updated_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="premium-card">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-cog text-slate-600 mr-3"></i>
                    Actions
                </h2>
                <div class="space-y-2">
                    <a href="add_prescription.php?id=<?php echo $prescription_id; ?>" class="w-full btn-primary btn-blue text-center">
                        <i class="fas fa-pen-nib mr-2"></i> Edit Prescription
                    </a>
                    <a href="delete_prescription.php?id=<?php echo $prescription_id; ?>" onclick="return confirm('Delete this prescription?')" class="w-full btn-primary btn-rose text-center">
                        <i class="fas fa-trash-alt mr-2"></i> Delete
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Closing Sidebar Tags -->
    </main>
    </div>
    </div>
</body>
</html>
