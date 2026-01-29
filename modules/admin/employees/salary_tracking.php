<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get salary statistics
$sql = "SELECT e.*, r.role_name,
               COALESCE(SUM(s.Total_Amt), 0) as Total_Sales,
               e.E_Sal as Base_Salary,
               (SELECT COALESCE(SUM(b.Bonus_Amount, 0) FROM bonuses b WHERE b.E_ID = e.E_ID AND b.Month = MONTH(CURDATE()) AND b.Year = YEAR(CURDATE())) as Current_Month_Bonus,
               (SELECT COALESCE(SUM(b.Bonus_Amount, 0) FROM bonuses b WHERE b.E_ID = e.E_ID AND b.Month = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND b.Year = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) as Last_Month_Bonus
        FROM employee e
        LEFT JOIN roles r ON e.role_id = r.role_id
        LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0
        GROUP BY e.E_ID
        ORDER BY e.E_Fname, e.Lname";

$result = $this->conn->query($sql);

// Get monthly salary expenses
$monthly_expenses = $conn->query("
    SELECT 
        MONTH(Payment_Date) as month,
        YEAR(Payment_Date) as year,
        SUM(Amount) as total_paid,
        COUNT(*) as payment_count
    FROM salary_payments
    GROUP BY YEAR(Payment_Date), MONTH(Payment_Date)
    ORDER BY year DESC, month DESC
    LIMIT 12
");

// Get bonus statistics
$bonus_stats = $conn->query("
    SELECT 
        MONTH(Payment_Date) as month,
        YEAR(Payment_Date) as year,
        SUM(Bonus_Amount) as total_bonus,
        COUNT(*) as bonus_count
    FROM bonuses
    GROUP BY YEAR(Payment_Date), MONTH(Payment_Date)
    ORDER BY year DESC, month DESC
    LIMIT 12
");

// Get department-wise salary distribution
$dept_stats = $conn->query("
    SELECT 
        r.role_name as department,
        COUNT(*) as employee_count,
        SUM(e.E_Sal) as total_salary,
        AVG(e.E_Sal) as avg_salary
    FROM employee e
    LEFT JOIN roles r ON e.role_id = r.role_id
    GROUP BY r.role_name
    ORDER BY total_salary DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Tracking - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Salary Tracking</h2>
            <p class="text-slate-500 mt-1 font-medium">Monitor payroll, bonuses, and compensation</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="processPayroll()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-emerald-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2m2 10a2 2 0 002 2v-10a2 2 0 00-2-2H9a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                Process Payroll
            </button>
            <a href="add_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m8-8v8m0 0l-8 8m8-8H4m0 0l-8 8"></path></svg>
                Add Employee
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Payroll</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $total_payroll = $conn->query("SELECT SUM(E_Sal) as total FROM employee")->fetch_assoc()['total'];
                        echo number_format($total_payroll, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2h2m2 10a2 2 0 002 2v-10a2 2 0 00-2-2H9a2 2 0 00-2-2v-10a2 2 0 00-2-2h-2m2 10a2 2 0 002 2v-10a2 2 0 00-2-2H9a2 2 0 00-2-2v-10a2 2 0 00-2-2h-2z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">This Month</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $this_month = $conn->query("SELECT SUM(E_Sal) as total FROM employee")->fetch_assoc()['total'];
                        echo number_format($this_month, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Bonus Paid</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $total_bonus = $conn->query("SELECT SUM(Bonus_Amount) as total FROM bonuses")->fetch_assoc()['total'];
                        echo number_format($total_bonus, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0 0h-8m-9-4h.01M9 16h.01"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg Salary</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php 
                        $avg_salary = $conn->query("SELECT AVG(E_Sal) as avg FROM employee")->fetch_assoc()['avg'];
                        echo number_format($avg_salary, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2m2 10a2 2 0 002 2v-10a2 2 0 00-2-2H9a2 2 0 00-2-2v-10a2 2 0 00-2-2h-2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-50 to-blue-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-sale-900">Salary Overview</h3>
            <p class="text-slate-600 mt-2">Employee compensation and performance metrics</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Employee ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Base Salary</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Current Bonus</th>
                        <th class="px-6 total-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Compensation</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($employee = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-400">#<?php echo str_pad($employee['E_ID'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($employee['E_Fname'] . ' ' . $employee['E_Lname']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($employee['E_Phno'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($employee['E_Type']); ?></td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($employee['Base_Salary'], 0); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-900"><?php echo $employee['Total_Sales']; ?> sales</div>
                                    <div class="text-xs text-slate-500">Rs. <?php echo number_format($employee['Total_Sales'], 0); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-emerald-600 font-bold">Rs. <?php echo number_format($employee['Current_Month_Bonus'] ?? 0, 0); ?></td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php 
                                    $total_compensation = $employee['Base_Salary'] + ($employee['Current_Month_Bonus'] ?? 0);
                                    echo number_format($total_compensation, 0);
                                ?></td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="salary_details.php?id=<?php echo $employee['E_ID']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        Details
                                    </a>
                                    <button onclick="adjustSalary(<?php echo $employee['E_ID']; ?>)" 
                                            class="text-amber-600 hover:text-amber-800 font-bold text-sm">
                                        Adjust Salary
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                No employee data available
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Monthly Salary Expenses -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Monthly Salary Expenses</h3>
            <canvas id="salaryChart" width="400" height="200"></canvas>
        </div>

        <!-- Department Distribution -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slim-900 mb-6">Department Salary Distribution</h3>
            <canvas id="deptChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Payroll Processing Modal -->
    <div id="payrollModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 max-w-2xl w-full mx-4">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Process Payroll</h3>
            <form action="process_payroll.php" method="post">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payroll Month</label>
                    <input type="month" name="payroll_month" required
                           value="<?php echo date('Y-m'); ?>"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment Date</label>
                    <input type="date" name="payment_date" required
                           value="<?php echo date('Y-m-d'); ?>"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                    <textarea name="payroll_notes" rows="3"
                              placeholder="Payroll processing notes..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition-all">
                        Process Payroll
                    </button>
                    <button type="button" onclick="closePayrollModal()" 
                            class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 rounded-xl transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function processPayroll() {
            document.getElementById('payrollModal').classList.remove('hidden');
        }
        
        function closePayrollModal() {
            document.getElementById('payrollModal').classList.add('hidden');
        }

        function adjustSalary(employeeId) {
            // Open salary adjustment modal
            alert('Salary adjustment functionality would be implemented here for employee ID: ' + employeeId);
        }

        // Monthly Salary Chart
        const salaryCtx = document.getElementById('salaryChart').getContext('2d');
        const salaryData = <?php 
            $monthly_expenses_data = [];
            while($row = $monthly_expenses->fetch_assoc()) {
                $monthly_expenses_data[] = [
                    date('M Y', strtotime($row['year'] . '-' . $row['month'] . '-01')),
                    $row['total_paid'],
                    $row['payment_count']
                ];
            }
            echo json_encode($monthly_expenses_data);
        ?>;
        
        new Chart(salaryCtx, {
            type: 'bar',
            data: {
                labels: salaryData.map(item => item[0]),
                datasets: [{
                    label: 'Salary Paid',
                    data: salaryData.map(item => item[1]),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs ' + value.toLocaleString();
                        }
                    }
                }
            }
        });

        // Department Distribution Chart
        const deptCtx = document.getElementById('deptChart').getContext('2d');
        const deptData = <?php 
            $dept_data = [];
            while($row = $dept_stats->fetch_assoc()) {
                $dept_data[] = [
                    $row['department'],
                    $row['employee_count'],
                    $row['total_salary'],
                    $row['avg_salary']
                ];
            }
            echo json_encode($dept_data);
        ?>;
        
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: deptData.map(item => item[0]),
                datasets: [{
                    label: 'Total Salary',
                    data: deptData.map(item => item[2]),
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)',
                        'rgb(239, 68, 68)'
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
