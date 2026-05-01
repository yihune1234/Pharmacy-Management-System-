<?php 
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/session_check.php';
require_once __DIR__ . '/../../../includes/alerts.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');

$search = $_POST['valuetosearch'] ?? '';
$query = "SELECT C_ID as cid, C_Fname as fname, C_Lname as lname, C_Phno as phno, Loyalty_Tier as tier, Loyalty_Points as points FROM customer";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE C_Fname LIKE '%$search%' OR C_Lname LIKE '%$search%' OR C_Phno LIKE '%$search%' OR C_ID LIKE '%$search%'";
}

$search_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registry - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Patient Registry</h2>
            <p class="text-slate-500 mt-1 font-medium">Verified medical profiles and loyalty status.</p>
        </div>
        
        <form method="post" class="flex items-center space-x-2 w-full md:w-auto">
            <div class="relative flex-grow md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="valuetosearch" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search patients..." 
                    class="w-full bg-white border border-slate-200 rounded-2xl py-3 pl-10 pr-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-medium">
            </div>
            <button type="submit" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg active:scale-95">
                Filter
            </button>
        </form>
    </div>

    <!-- Patient Table Card -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient Profile</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Contact</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Loyalty Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Rewards</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($search_result && $search_result->num_rows > 0): ?>
                        <?php while($row = $search_result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                            <i class="fas fa-user-injured"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900"><?php echo htmlspecialchars($row["fname"] . ' ' . $row["lname"]); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">UID: #P-<?php echo str_pad($row["cid"], 5, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-600">
                                    <?php echo htmlspecialchars($row["phno"]); ?>
                                </td>
                                <td class="px-6 py-5">
                                    <?php 
                                    $tier = $row["tier"] ?? 'Bronze';
                                    $colors = [
                                        'Gold' => 'bg-amber-100 text-amber-600 border-amber-200',
                                        'Silver' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'Bronze' => 'bg-orange-100 text-orange-600 border-orange-200'
                                    ];
                                    $colorClass = $colors[$tier] ?? $colors['Bronze'];
                                    ?>
                                    <span class="px-3 py-1 <?php echo $colorClass; ?> border rounded-full text-[10px] font-black uppercase tracking-wider">
                                        <?php echo $tier; ?> Member
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900 font-black"><?php echo number_format($row["points"] ?? 0); ?> <span class="text-[10px] text-slate-400">PTS</span></p>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <button class="text-slate-400 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                        <i class="fas fa-search-minus text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900">No matching patients</h3>
                                    <p class="text-slate-500 max-w-xs mx-auto mt-2">Try searching by Name, Phone, or UID.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>
