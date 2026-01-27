<?php
// load central config (creates $conn)
require_once __DIR__ . '/../../../config/config.php';

// if no working DB connection, send user to installer
if (!isset($conn) || !($conn instanceof mysqli) || ($conn instanceof mysqli && $conn->connect_error)) {
    header('Location: /Pharmacy-Management-System/database/install.php');
    exit();
}

$search = $_POST['valuetosearch'] ?? '';
$query = "SELECT med_id as medid, med_name as medname, med_qty as medqty, category as medcategory, med_price as medprice, location_rack as medlocation FROM meds";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE med_name LIKE '%$search%' OR med_id LIKE '%$search%'";
}

$search_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Tracking - PHARMACIA</title>
</head>
<body class="bg-slate-50">

    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Medicine Inventory</h2>
            <p class="text-slate-500 mt-1 font-medium">Monitor stock levels and storage locations.</p>
        </div>
        
        <form method="post" class="flex items-center space-x-2 w-full md:w-auto">
            <div class="relative flex-grow md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="valuetosearch" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search medicine..." 
                    class="w-full bg-white border border-slate-200 rounded-2xl py-3 pl-10 pr-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium">
            </div>
            <button type="submit" name="search" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-all shadow-lg active:scale-95">
                Search
            </button>
        </form>
    </div>

    <!-- Inventory Table Card -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Product Name</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Category</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Stock Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Unit Price</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Storage Location</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($search_result && $search_result->num_rows > 0): ?>
                        <?php while($row = $search_result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900"><?php echo htmlspecialchars($row["medname"]); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">SKU: <?php echo str_pad($row["medid"], 5, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider">
                                        <?php echo htmlspecialchars($row["medcategory"]); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <?php 
                                    $qty = $row["medqty"];
                                    if ($qty <= 10) {
                                        echo "<span class='flex items-center text-rose-600 font-bold text-sm'><div class='w-1.5 h-1.5 rounded-full bg-rose-600 mr-2 animate-ping'></div>Low Stock ($qty)</span>";
                                    } else {
                                        echo "<span class='text-emerald-600 font-bold text-sm'>$qty available</span>";
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900 font-black">Rs. <?php echo number_format($row["medprice"], 2); ?></p>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center text-slate-500 font-medium text-sm">
                                        <svg class="w-4 h-4 mr-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <?php echo htmlspecialchars($row["medlocation"]); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <button class="text-slate-400 hover:text-emerald-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900">No matching medicines found</h3>
                                    <p class="text-slate-500 max-w-xs mx-auto mt-2">We couldn't find any products matching your search term. Please try another one.</p>
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

