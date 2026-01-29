<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE Sup_Name LIKE '%$search%' OR Sup_Add LIKE '%$search%'" : "";
$sql = "SELECT * FROM suppliers $where ORDER BY Sup_Name";
$suppliers = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Network Matrix - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header Section -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-amber-600 uppercase tracking-widest mb-1">Partner Network</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Suppliers Register</h1>
            <p class="text-slate-500 font-medium mt-1">Manage pharmaceutical supply chains and vendor relationships.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="relative group hidden sm:block">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-amber-600 transition-colors"></i>
                <input type="text" id="tableSearch" placeholder="Filter Partners..." 
                       class="bg-white border border-slate-200 pl-11 pr-4 py-3 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:outline-none transition-all w-64 shadow-sm">
            </div>
            <a href="add_new.php" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all flex items-center group">
                <i class="fas fa-handshake mr-3 transform group-hover:scale-110 transition-transform"></i> New Partnership
            </a>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Partner ID</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Corporate Entity</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Official Address</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Comm Channels</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Protocol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                        <?php while($row = $suppliers->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/80 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400">#SUP-<?php echo $row["Sup_ID"]; ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center font-black text-xs">
                                            <i class="fas fa-industry text-xs"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-900 group-hover:text-amber-600 transition-colors tracking-tight"><?php echo htmlspecialchars($row["Sup_Name"]); ?></span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Verified Vendor</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-xs font-bold text-slate-600 flex items-center">
                                        <i class="fas fa-location-dot text-[10px] text-slate-400 mr-2"></i> <?php echo htmlspecialchars($row["Sup_Add"]); ?>
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-xs font-bold text-slate-700 flex items-center">
                                            <i class="fas fa-phone-alt text-[10px] text-slate-400 mr-2"></i> <?php echo $row["Sup_Phno"]; ?>
                                        </span>
                                        <span class="text-[10px] font-medium text-slate-400">
                                            <i class="fas fa-at text-[10px] text-slate-300 mr-2"></i> <?php echo $row["Sup_Mail"]; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="update.php?id=<?php echo $row['Sup_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['Sup_ID']; ?>" onclick="return confirm('Terminate supplier partnership records?')" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-10 py-20 text-center text-slate-400 font-bold italic">No partner synchronization found.</td></tr>
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
