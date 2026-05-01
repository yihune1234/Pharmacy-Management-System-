<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE s.Sale_ID LIKE '%$search%' OR c.C_Fname LIKE '%$search%' OR c.C_Lname LIKE '%$search%' OR e.E_Fname LIKE '%$search%'" : "";

// Get sales with customer and employee details
$sql = "SELECT s.Sale_ID, s.S_Date, s.Total_Amt, c.C_Fname, c.C_Lname, e.E_Fname as emp_name 
        FROM sales s 
        LEFT JOIN customer c ON s.C_ID = c.C_ID 
        LEFT JOIN employee e ON s.E_ID = e.E_ID 
        $where
        ORDER BY s.Sale_ID DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactional Log - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header Section -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Intelligence Audit</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Transactional Log</h1>
            <p class="text-slate-500 font-medium mt-1">Reviewing comprehensive system sales and entity signatures.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="relative group hidden sm:block">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                <input type="text" id="tableSearch" placeholder="Filter Protocols..." 
                       class="bg-white border border-slate-200 pl-11 pr-4 py-3 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all w-64 shadow-sm">
            </div>
            <a href="pos_new.php" class="bg-blue-600 text-white px-8 py-3.5 rounded-2xl text-sm font-black shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all flex items-center group">
                <i class="fas fa-plus mr-3 transform group-hover:scale-110 transition-transform"></i> New Transaction
            </a>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Protocol ID</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Entity Signature</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Authorized By</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Volume (Rs)</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($sale = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-[10px] font-black tracking-widest">#S-<?php echo $sale['Sale_ID']; ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight"><?php echo htmlspecialchars($sale['C_Fname'] . ' ' . $sale['C_Lname']); ?></span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight"><?php echo date('H:i | d M Y', strtotime($sale['S_Date'])); ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                        <span class="text-xs font-black text-slate-600"><?php echo htmlspecialchars($sale['emp_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-right">
                                    <span class="text-sm font-black text-slate-900 tracking-tight">Rs. <?php echo number_format($sale['Total_Amt'], 2); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="receipt.php?id=<?php echo $sale['Sale_ID']; ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-print text-xs"></i>
                                        </a>
                                        <a href="items_view.php?id=<?php echo $sale['Sale_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-10 py-20 text-center text-slate-400 font-bold italic">No transactional protocols identified.</td></tr>
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
        document.getElementById('tableSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
