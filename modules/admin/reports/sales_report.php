<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Reports - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Transaction Reports</h2>
            <p class="text-slate-500 mt-1 font-medium">Analyze financial performance and movement records.</p>
        </div>
    </div>

    <!-- Date Range Picker Card -->
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm mb-12 max-w-2xl">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">Select Date Range</h3>
        <form action="<?=$_SERVER['PHP_SELF']?>" method="post" class="flex flex-col sm:flex-row items-end space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="flex-grow w-full">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Start Date</label>
                <input type="date" name="start" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold" />
            </div>
            <div class="flex-grow w-full">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">End Date</label>
                <input type="date" name="end" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold" />
            </div>
            <button type="submit" name="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-black py-3.5 px-8 rounded-xl shadow-lg transition-all active:scale-95 whitespace-nowrap">
                Generate Analysis
            </button>
        </form>
    </div>

    <?php
    include "../../../config/config.php";
    if(isset($_POST['submit'])) {
        $start=$_POST['start'];
        $end=$_POST['end'];
        
        $res=mysqli_query($conn,"SELECT SUM(p_cost) AS PAMT FROM purchase WHERE pur_date >= '$start' AND pur_date <= '$end'") or die(mysqli_error($conn));
        $row=mysqli_fetch_array($res);
        $pamt=$row['PAMT'] ?? 0;

        $res=mysqli_query($conn,"SELECT SUM(total_amt) AS SAMT FROM sales WHERE s_date >= '$start' AND s_date <= '$end';") or die(mysqli_error($conn));
        $row=mysqli_fetch_array($res);
        $samt=$row['SAMT'] ?? 0;

        $profit = $samt - $pamt;
        $profitColor = $profit >= 0 ? 'text-emerald-600' : 'text-red-600';
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Total Purchases</p>
            <p class="text-3xl font-black text-slate-900">Rs. <?php echo number_format($pamt, 2); ?></p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Total Revenue</p>
            <p class="text-3xl font-black text-blue-600">Rs. <?php echo number_format($samt, 2); ?></p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Net Performance</p>
            <p class="text-3xl font-black <?php echo $profitColor; ?>">Rs. <?php echo number_format($profit, 2); ?></p>
        </div>
    </div>

    <div class="space-y-12">
        <!-- Purchases Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Purchase Ledger</h4>
                <span class="bg-slate-200 px-3 py-1 rounded-full text-[10px] font-black"><?php echo $start; ?> to <?php echo $end; ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-8 py-4">P-ID</th>
                            <th class="px-8 py-4">Med-ID</th>
                            <th class="px-8 py-4">Qty</th>
                            <th class="px-8 py-4 text-right">Cost (Rs)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        $sql = "SELECT p_id, sup_id, med_id, p_qty, p_cost, pur_date FROM purchase WHERE pur_date >= '$start' AND pur_date <= '$end';";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-slate-50/50 transition-colors'>";
                                echo "<td class='px-8 py-4 text-xs font-bold text-slate-400'>#".$row["p_id"]."</td>";
                                echo "<td class='px-8 py-4 text-xs font-bold text-slate-900'>#".$row["med_id"]."</td>";
                                echo "<td class='px-8 py-4 text-xs font-bold text-slate-600'>".$row["p_qty"]."</td>";
                                echo "<td class='px-8 py-4 text-xs font-black text-slate-900 text-right'>".number_format($row["p_cost"], 2)."</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='px-8 py-8 text-center text-slate-400 text-xs font-bold uppercase'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Sales Ledger</h4>
                <span class="bg-slate-200 px-3 py-1 rounded-full text-[10px] font-black">Settled Transactions</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-8 py-4">S-ID</th>
                            <th class="px-8 py-4">C-ID</th>
                            <th class="px-8 py-4 text-right">Amount (Rs)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        $sql = "SELECT sale_id, c_id, total_amt FROM sales WHERE s_date >= '$start' AND s_date <= '$end';";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-slate-50/50 transition-colors'>";
                                echo "<td class='px-8 py-4 text-xs font-bold text-slate-400'>#".$row["sale_id"]."</td>";
                                echo "<td class='px-8 py-4 text-xs font-bold text-slate-900'>#".$row["c_id"]."</td>";
                                echo "<td class='px-8 py-4 text-xs font-black text-blue-600 text-right'>".number_format($row["total_amt"], 2)."</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='px-8 py-8 text-center text-slate-400 text-xs font-bold uppercase'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php } // End if submit ?>

    <!-- closing sidebar tags -->
    </main>
    </div>
    </div>
</body>
</html>
