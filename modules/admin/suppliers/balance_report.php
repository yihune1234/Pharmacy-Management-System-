<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$supplier_id = $_GET['id'] ?? 0;

// Get all suppliers for dropdown
$suppliers = $conn->query("SELECT Sup_ID, Sup_Name FROM suppliers ORDER BY Sup_Name");

// Get balance report data
if ($supplier_id > 0) {
    $sql = "SELECT s.*, 
                   COALESCE(SUM(p.P_Qty * p.P_Cost), 0) as Total_Purchases,
                   COALESCE(SUM(CASE WHEN p.Payment_Status = 'Pending' THEN p.P_Qty * p.P_Cost ELSE 0 END), 0) as Pending_Amount,
                   COALESCE(SUM(CASE WHEN p.Payment_Status = 'Paid' THEN p.P_Qty * p.P_Cost ELSE 0 END), 0) as Paid_Amount,
                   COUNT(p.P_ID) as Total_Transactions,
                   MAX(p.Pur_Date) as Last_Purchase_Date,
                   s.Credit_Limit
            FROM suppliers s
            LEFT JOIN purchase p ON s.Sup_ID = p.Sup_ID
            WHERE s.Sup_ID = $supplier_id
            GROUP BY s.Sup_ID";
} else {
    $sql = "SELECT s.*, 
                   COALESCE(SUM(p.P_Qty * p.P_Cost), 0) as Total_Purchases,
                   COALESCE(SUM(CASE WHEN p.Payment_Status = 'Pending' THEN p.P_Qty * p.P_Cost ELSE 0 END), 0) as Pending_Amount,
                   COALESCE(SUM(CASE WHEN p.Payment_Status = 'Paid' THEN p.P_Qty * p.P_Cost ELSE 0 END), 0) as Paid_Amount,
                   COUNT(p.P_ID) as Total_Transactions,
                   MAX(p.Pur_Date) as Last_Purchase_Date,
                   s.Credit_Limit
            FROM suppliers s
            LEFT JOIN purchase p ON s.Sup_ID = p.Sup_ID
            GROUP BY s.Sup_ID
            ORDER BY s.Sup_Name";
}

$result = $conn->query($sql);

// Calculate summary statistics
$total_balance = 0;
$total_pending = 0;
$total_credit_limit = 0;

if ($result) {
    while($row = $result->fetch_assoc()) {
        $total_balance += ($row['Total_Purchases'] - $row['Paid_Amount']);
        $total_pending += $row['Pending_Amount'];
        $total_credit_limit += $row['Credit_Limit'];
    }
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Balance Report - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Supplier Balance Report</h2>
            <p class="text-slate-500 mt-1 font-medium">Track supplier payments and outstanding balances</p>
        </div>
        <div class="flex space-x-3">
            <form method="get" class="flex items-center space-x-3">
                <select name="id" onchange="this.form.submit()" 
                        class="bg-white border border-slate-200 rounded-2xl px-5 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                    <option value="0">All Suppliers</option>
                    <?php while($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['Sup_ID']; ?>" <?php echo ($supplier_id == $supplier['Sup_ID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['Sup_Name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
            <button onclick="window.print()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Report
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Balance</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($total_balance, 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Pending Payments</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($total_pending, 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Credit Limit</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($total_credit_limit, 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Credit Utilization</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $utilization = $total_credit_limit > 0 ? ($total_balance / $total_credit_limit) * 100 : 0;
                        echo number_format($utilization, 1); ?>%
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Supplier Balance Details</h3>
            <p class="text-slate-600 mt-2">Outstanding payments and credit utilization</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Purchases</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Paid Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Credit Limit</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Available Credit</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($supplier = $result->fetch_assoc()): 
                            $available_credit = $supplier['Credit_Limit'] - ($supplier['Total_Purchases'] - $supplier['Paid_Amount']);
                            $credit_utilization = $supplier['Credit_Limit'] > 0 ? (($supplier['Total_Purchases'] - $supplier['Paid_Amount']) / $supplier['Credit_Limit'] * 100 : 0;
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($supplier['Sup_Name']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($supplier['Contact_Person'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($supplier['Total_Purchases'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-emerald-600 font-bold">Rs. <?php echo number_format($supplier['Paid_Amount'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-amber-600 font-bold">Rs. <?php echo number_format($supplier['Pending_Amount'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600">Rs. <?php echo number_format($supplier['Credit_Limit'], 2); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="<?php echo $available_credit >= 0 ? 'text-emerald-600' : 'text-red-600'; ?> font-bold">
                                        Rs. <?php echo number_format($available_credit, 2); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($credit_utilization >= 90): ?>
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Critical</span>
                                    <?php elseif ($credit_utilization >= 70): ?>
                                        <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">Warning</span>
                                    <?php else: ?>
                                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Good</span>
                                    <?php endif; ?>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo number_format($credit_utilization, 1); ?>% used</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button onclick="recordPayment(<?php echo $supplier['Sup_ID']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        Record Payment
                                    </button>
                                    <a href="supplier_purchases.php?id=<?php echo $supplier['Sup_ID']; ?>" 
                                       class="text-emerald-600 hover:text-emerald-800 font-bold text-sm">
                                        View Purchases
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                No supplier data available
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Credit Utilization Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Credit Utilization Overview</h3>
            <canvas id="creditChart" width="400" height="200"></canvas>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Payment Status Distribution</h3>
            <canvas id="paymentChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Record Payment</h3>
            <form action="record_payment.php" method="post">
                <input type="hidden" name="supplier_id" id="payment_supplier_id">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment Amount</label>
                    <input type="number" name="payment_amount" step="0.01" min="0" required
                           placeholder="0.00" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment Date</label>
                    <input type="date" name="payment_date" required
                           value="<?php echo date('Y-m-d'); ?>"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method</label>
                    <select name="payment_method" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Credit Card">Credit Card</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                    <textarea name="payment_notes" rows="3"
                              placeholder="Payment reference or notes..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all">
                        Record Payment
                    </button>
                    <button type="button" onclick="closePaymentModal()" 
                            class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 rounded-xl transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function recordPayment(supplierId) {
            document.getElementById('payment_supplier_id').value = supplierId;
            document.getElementById('paymentModal').classList.remove('hidden');
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        // Credit Utilization Chart
        const creditCtx = document.getElementById('creditChart').getContext('2d');
        const creditData = <?php 
            $result->data_seek(0);
            $suppliers = [];
            $utilization = [];
            while($row = $result->fetch_assoc()) {
                $suppliers[] = substr($row['Sup_Name'], 0, 20);
                $utilization = $row['Credit_Limit'] > 0 ? (($row['Total_Purchases'] - $row['Paid_Amount']) / $row['Credit_Limit'] * 100 : 0;
                $utilization[] = round($utilization, 1);
            }
            echo json_encode(['labels' => $suppliers, 'data' => $utilization]);
        ?>;
        
        new Chart(creditCtx, {
            type: 'bar',
            data: {
                labels: creditData.labels,
                datasets: [{
                    label: 'Credit Utilization (%)',
                    data: creditData.data,
                    backgroundColor: creditData.data.map(val => val >= 90 ? 'rgba(239, 68, 68, 0.8)' : (val >= 70 ? 'rgba(245, 158, 11, 0.8)' : 'rgba(34, 197, 94, 0.8)')),
                    borderColor: creditData.data.map(val => val >= 90 ? 'rgb(239, 68, 68)' : (val >= 70 ? 'rgb(245, 158, 11)' : 'rgb(34, 197, 94)')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Payment Status Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentData = <?php 
            $result->data_seek(0);
            $totalPaid = 0;
            $totalPending = 0;
            while($row = $result->fetch_assoc()) {
                $totalPaid += $row['Paid_Amount'];
                $totalPending += $row['Pending_Amount'];
            }
            echo json_encode(['paid' => $totalPaid, 'pending' => $totalPending]);
        ?>;
        
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid Amount', 'Pending Amount'],
                datasets: [{
                    data: [paymentData.paid, paymentData.pending],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(245, 158, 11)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
