<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE m.Med_Name LIKE '%$search%' OR p.P_ID LIKE '%$search%'" : "";
$sql = "SELECT p.*, m.Med_Name 
        FROM purchase p 
        LEFT JOIN meds m ON p.Med_ID = m.Med_ID 
        $where 
        ORDER BY p.Pur_Date DESC";
$purchases = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Logs - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header Section -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-1">Procurement Logic</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Purchase History</h1>
            <p class="text-slate-500 font-medium mt-1">Audit stock acquisition protocols and vendor fulfillment records.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="relative group hidden sm:block">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                <input type="text" id="tableSearch" placeholder="Filter Purchases..." 
                       class="bg-white border border-slate-200 pl-11 pr-4 py-3 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:outline-none transition-all w-64 shadow-sm">
            </div>
            <a href="add.php" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all flex items-center group">
                <i class="fas fa-cart-plus mr-3 transform group-hover:scale-110 transition-transform"></i> New Order
            </a>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Procurement ID</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Asset Specs</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Volume & Cost</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Timeline</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Protocol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($purchases && $purchases->num_rows > 0): ?>
                        <?php while($row = $purchases->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/80 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400">#ACQ-<?php echo $row["P_ID"]; ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors tracking-tight"><?php echo htmlspecialchars($row["Med_Name"]); ?></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Mfg: <?php echo isset($row["Mfg_Date"]) ? date('M Y', strtotime($row["Mfg_Date"])) : 'N/A'; ?> | Exp: <?php echo isset($row["Exp_Date"]) ? date('M Y', strtotime($row["Exp_Date"])) : 'N/A'; ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs font-black text-slate-700 bg-indigo-50 px-3 py-1 rounded-lg"><?php echo $row["P_Qty"]; ?> Units</span>
                                        <span class="text-sm font-black text-slate-900 tracking-tight">Rs. <?php echo number_format($row["P_Cost"], 2); ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-600"><?php echo date('D, j M Y', strtotime($row["Pur_Date"])); ?></span>
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Entry Logged</span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="update.php?pid=<?php echo $row['P_ID']; ?>&sid=<?php echo $row['Sup_ID']; ?>&mid=<?php echo $row['Med_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-file-pen text-xs"></i>
                                        </a>
                                        <a href="delete.php?pid=<?php echo $row['P_ID']; ?>&sid=<?php echo $row['Sup_ID']; ?>&mid=<?php echo $row['Med_ID']; ?>" onclick="return confirm('Purge acquisition record?')" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-10 py-20 text-center text-slate-400 font-bold italic">No procurement synchronization found.</td></tr>
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
