<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get inventory data with advanced filtering (optional enhancement)
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE Med_Name LIKE '%$search%' OR Category LIKE '%$search%'" : "";
$sql = "SELECT * FROM meds $where ORDER BY Med_Name";
$medicines = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Inventory Matrix - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header section -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="subheading-premium">Operational Matrix</p>
            <h1 class="heading-premium">Medicine Inventory</h1>
            <p class="text-slate-500 font-medium mt-1">Real-time status of pharmaceutical stock assets.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="relative group hidden sm:block">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                <input type="text" id="tableSearch" placeholder="Filter inventory..." 
                       class="bg-white border border-slate-200 pl-11 pr-4 py-3 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all w-64 shadow-sm">
            </div>
            <a href="add.php" class="btn-primary btn-slate !px-8">
                <i class="fas fa-plus-circle mr-3 transform group-hover:rotate-90 transition-transform"></i> Register Asset
            </a>
        </div>
    </div>

    <!-- Inventory Matrix Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Asset Signature</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Medicine Name</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Classification</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Quantum Status</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Valuation</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Protocol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($medicines && $medicines->num_rows > 0): ?>
                        <?php while($row = $medicines->fetch_assoc()): ?>
                            <?php 
                                $isLow = $row["Med_Qty"] <= 10;
                                $statusTxt = $isLow ? 'CRITICAL' : 'OPTIMAL';
                                $statusColor = $isLow ? 'text-rose-600 bg-rose-50 border-rose-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100';
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400">#MED-<?php echo $row["Med_ID"]; ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($row["Med_Name"]); ?></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic">Loc: <?php echo htmlspecialchars($row["Location_Rack"]); ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-4 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black tracking-widest uppercase italic"><?php echo htmlspecialchars($row["Category"]); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xl font-black text-slate-900"><?php echo $row["Med_Qty"]; ?></span>
                                        <span class="px-3 py-1 border <?php echo $statusColor; ?> rounded-lg text-[9px] font-black tracking-widest <?php echo ($isLow ? 'alert-pulse' : ''); ?>">
                                            <?php echo $statusTxt; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-sm font-black text-slate-900 tracking-tight italic">Rs. <?php echo number_format($row["Med_Price"], 0); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-3">
                                        <a href="update.php?id=<?php echo $row["Med_ID"]; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-pen-nib text-xs"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row["Med_ID"]; ?>" onclick="return confirm('Initiate asset removal protocol?')" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-10 py-24 text-center">
                            <div class="opacity-10 mb-4 text-5xl"><i class="fas fa-box-open"></i></div>
                            <p class="text-slate-400 font-bold italic">No asset synchronization found in database.</p>
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

    <script>
        // Simple client-side search/filter
        document.getElementById('tableSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
