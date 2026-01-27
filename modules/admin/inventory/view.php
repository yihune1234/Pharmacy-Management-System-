<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Medicine Inventory</h2>
            <p class="text-slate-500 mt-1">View and manage all pharmaceutical products in stock.</p>
        </div>
        <a href="add.php" class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Medicine
        </a>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Price (Rs)</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    include "../../../config/config.php";
                    $sql = "SELECT med_id, med_name, med_qty, category, med_price, location_rack FROM meds";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $stockColor = $row["med_qty"] < 20 ? 'text-red-600 font-bold' : 'text-slate-600';
                            echo "<tr class='hover:bg-slate-50 transition-colors'>";
                            echo "<td class='px-6 py-4 text-sm font-medium text-slate-400'>#" . $row["med_id"]. "</td>";
                            echo "<td class='px-6 py-4 text-sm font-bold text-slate-900'>" . $row["med_name"] . "</td>";
                            echo "<td class='px-6 py-4 text-sm $stockColor'>" . $row["med_qty"]. "</td>";
                            echo "<td class='px-6 py-4 text-sm text-slate-600'><span class='bg-slate-100 px-3 py-1 rounded-full text-xs font-bold uppercase'>" . $row["category"]. "</span></td>";
                            echo "<td class='px-6 py-4 text-sm font-black text-slate-900'>Rs. " . number_format($row["med_price"], 2) . "</td>";
                            echo "<td class='px-6 py-4 text-sm text-slate-500'>" . $row["location_rack"]. "</td>";
                            echo "<td class='px-6 py-4 text-sm text-center'>";
                            echo "<div class='flex items-center justify-center space-x-2'>";
                            echo "<a class='bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white px-4 py-2 rounded-xl text-xs font-bold transition-all' href='update.php?id=".$row['med_id']."'>Edit</a>";
                            echo "<a class='bg-red-50 text-red-600 hover:bg-red-600 hover:text-white px-4 py-2 rounded-xl text-xs font-bold transition-all' href='delete.php?id=".$row['med_id']."'>Delete</a>";
                            echo "</div></td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='px-6 py-12 text-center text-slate-400 font-medium'>No inventory records found.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
