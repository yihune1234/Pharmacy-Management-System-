<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';
require_once 'interaction_database.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get all medicines for dropdown
$medicines = $conn->query("SELECT Med_ID, Med_Name FROM meds ORDER BY Med_Name");

// Get all interactions
$interactions = $conn->query("SELECT * FROM view_drug_interaction_alerts ORDER BY severity DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Interaction Checker - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="subheading-premium">Safety Management</p>
            <h1 class="heading-premium">Drug Interaction Checker</h1>
            <p class="text-slate-500 font-medium mt-1">Check for potential drug interactions and contraindications</p>
        </div>
        <a href="check_interaction.php" class="btn-primary btn-slate !px-8">
            <i class="fas fa-plus-circle mr-3"></i> Add Interaction
        </a>
    </div>

    <!-- Checker Tool -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Interaction Checker -->
        <div class="lg:col-span-2 premium-card">
            <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                <i class="fas fa-flask-vial text-blue-600 mr-3"></i>
                Check Medicines for Interactions
            </h2>
            
            <form method="POST" action="check_interaction.php" class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Medicine 1 <span class="text-rose-500">*</span></label>
                    <select name="med_id_1" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        <option value="">Select First Medicine</option>
                        <?php 
                        $medicines->data_seek(0);
                        while($med = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $med['Med_ID']; ?>"><?php echo htmlspecialchars($med['Med_Name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Medicine 2 <span class="text-rose-500">*</span></label>
                    <select name="med_id_2" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        <option value="">Select Second Medicine</option>
                        <?php 
                        $medicines->data_seek(0);
                        while($med = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $med['Med_ID']; ?>"><?php echo htmlspecialchars($med['Med_Name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="w-full btn-primary btn-blue">
                    <i class="fas fa-search mr-2"></i> Check for Interactions
                </button>
            </form>
        </div>

        <!-- Quick Stats -->
        <div class="premium-card">
            <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                <i class="fas fa-chart-pie text-emerald-600 mr-3"></i>
                Interaction Statistics
            </h2>
            
            <?php
            $critical = $conn->query("SELECT COUNT(*) as count FROM drug_interactions WHERE severity = 'Critical'")->fetch_assoc()['count'] ?? 0;
            $high = $conn->query("SELECT COUNT(*) as count FROM drug_interactions WHERE severity = 'High'")->fetch_assoc()['count'] ?? 0;
            $moderate = $conn->query("SELECT COUNT(*) as count FROM drug_interactions WHERE severity = 'Moderate'")->fetch_assoc()['count'] ?? 0;
            $low = $conn->query("SELECT COUNT(*) as count FROM drug_interactions WHERE severity = 'Low'")->fetch_assoc()['count'] ?? 0;
            $total = $critical + $high + $moderate + $low;
            ?>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-rose-50 rounded-xl border border-rose-100">
                    <span class="text-sm font-bold text-rose-600">Critical</span>
                    <span class="text-2xl font-black text-rose-600"><?php echo $critical; ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-xl border border-orange-100">
                    <span class="text-sm font-bold text-orange-600">High</span>
                    <span class="text-2xl font-black text-orange-600"><?php echo $high; ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-100">
                    <span class="text-sm font-bold text-amber-600">Moderate</span>
                    <span class="text-2xl font-black text-amber-600"><?php echo $moderate; ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <span class="text-sm font-bold text-blue-600">Low</span>
                    <span class="text-2xl font-black text-blue-600"><?php echo $low; ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-slate-100 rounded-xl border border-slate-200 font-bold">
                    <span class="text-sm text-slate-700">Total</span>
                    <span class="text-2xl text-slate-900"><?php echo $total; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- All Interactions Table -->
    <div class="premium-card overflow-hidden">
        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center px-10 pt-8">
            <i class="fas fa-list text-slate-600 mr-3"></i>
            All Registered Interactions
        </h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Medicine 1</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Medicine 2</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Severity</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Description</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($interactions && $interactions->num_rows > 0): ?>
                        <?php while($row = $interactions->fetch_assoc()): ?>
                            <?php 
                                $severity_colors = [
                                    'Critical' => 'text-rose-600 bg-rose-50 border-rose-100',
                                    'High' => 'text-orange-600 bg-orange-50 border-orange-100',
                                    'Moderate' => 'text-amber-600 bg-amber-50 border-amber-100',
                                    'Low' => 'text-blue-600 bg-blue-50 border-blue-100'
                                ];
                                $severity_color = $severity_colors[$row['severity']] ?? 'text-slate-600 bg-slate-50 border-slate-100';
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['medicine_1']); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['medicine_2']); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-3 py-1 border <?php echo $severity_color; ?> rounded-lg text-[9px] font-black tracking-widest uppercase">
                                        <?php echo $row['severity']; ?>
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm text-slate-600 line-clamp-2"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-3">
                                        <a href="check_interaction.php?id=<?php echo $row['interaction_id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="View">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="check_interaction.php?id=<?php echo $row['interaction_id']; ?>&edit=1" class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Edit">
                                            <i class="fas fa-pen-nib text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-10 py-24 text-center">
                            <div class="opacity-10 mb-4 text-5xl"><i class="fas fa-flask-vial"></i></div>
                            <p class="text-slate-400 font-bold italic">No interactions registered yet.</p>
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
