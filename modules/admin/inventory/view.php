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
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Barcode</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    include "../../../config/config.php";
                    $sql = "SELECT Med_ID, Med_Name, Med_Qty, Category, Med_Price, Location_Rack, Barcode, Min_Stock_Level FROM meds ORDER BY Med_Name";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $stockColor = $row["Med_Qty"] <= $row["Min_Stock_Level"] ? 'text-red-600 font-bold' : 'text-slate-600';
                            $stockBg = $row["Med_Qty"] <= $row["Min_Stock_Level"] ? 'bg-red-50' : 'bg-slate-50';
                            echo "<tr class='hover:bg-slate-50 transition-colors'>";
                            echo "<td class='px-6 py-4 text-sm font-medium text-slate-400'>#" . $row["Med_ID"]. "</td>";
                            echo "<td class='px-6 py-4 text-sm font-bold text-slate-900'>" . $row["Med_Name"] . "</td>";
                            echo "<td class='px-6 py-4 text-sm $stockColor'>";
                            echo "<span class='$stockBg px-2 py-1 rounded-full text-xs'>" . $row["Med_Qty"]. "</span>";
                            if ($row["Med_Qty"] <= $row["Min_Stock_Level"]) {
                                echo " <span class='text-xs text-red-500'>⚠️ Low</span>";
                            }
                            echo "</td>";
                            echo "<td class='px-6 py-4 text-sm text-slate-600'><span class='bg-slate-100 px-3 py-1 rounded-full text-xs font-bold uppercase'>" . $row["Category"]. "</span></td>";
                            echo "<td class='px-6 py-4 text-sm font-black text-slate-900'>Rs. " . number_format($row["Med_Price"], 2) . "</td>";
                            echo "<td class='px-6 py-4 text-sm text-slate-500'>" . $row["Location_Rack"]. "</td>";
                            echo "<td class='px-6 py-4 text-sm text-slate-400'>" . ($row["Barcode"] ? $row["Barcode"] : '-') . "</td>";
                            echo "<td class='px-6 py-4 text-sm text-center'>";
                            echo "<a href='edit.php?id=" . $row["Med_ID"] . "' class='text-blue-600 hover:text-blue-800 font-bold text-sm mr-3'>Edit</a>";
                            echo "<a href='delete.php?id=" . $row["Med_ID"] . "' onclick='return confirm(\"Are you sure you want to delete this medicine?\")' class='text-red-600 hover:text-red-800 font-bold text-sm'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='px-6 py-8 text-center text-slate-500'>No medicines found in inventory.</td></tr>";
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
