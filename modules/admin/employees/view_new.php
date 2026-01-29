<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get employees with role information
$sql = "SELECT e.*, r.role_name, r.description,
               COUNT(s.Sale_ID) as Total_Sales,
               COALESCE(SUM(s.Total_Amt), 0) as Total_Sales_Amount,
               MAX(s.S_Date) as Last_Sale_Date
        FROM employee e
        LEFT JOIN roles r ON e.role_id = r.role_id
        LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0
        GROUP BY e.E_ID
        ORDER BY e.E_Fname, e.E_Lname";

$result = $conn->query($sql);

// Get summary statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'];
$active_employees = $conn->query("SELECT COUNT(*) as count FROM employee WHERE E_Jdate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$total_salary = $conn->query("SELECT SUM(E_Sal) as total FROM employee")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Employee Management</h2>
            <p class="text-slate-500 mt-1 font-medium">Manage staff, track performance, and monitor activity</p>
        </div>
        <div class="flex space-x-3">
            <a href="activity_logs.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Activity Logs
            </a>
            <a href="salary_tracking.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Salary Tracking
            </a>
            <a href="add_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Employee
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Employees</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $total_employees; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 006-6h.645a4 4 0 00.083-.732z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active This Month</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo $active_employees; ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Payroll</p>
                    <p class="text-2xl font-black text-slate-900">Rs. <?php echo number_format($total_salary, 0); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg Sales/Employee</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $avg_sales = $total_employees > 0 ? 
                            $conn->query("SELECT AVG(Total_Sales_Amount) as avg FROM (SELECT e.E_ID, COALESCEUM(s.Total_Amt), 0) as Total_Sales_Amount FROM employee e LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0 GROUP BY e.E_ID) as t")->fetch_assoc()['avg'] : 0;
                        echo number_format($avg_sales, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8-8-8 8v8M8 15V7m0 8H8m0 0H8"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Employee Directory</h3>
            <p class="text-slate-600 mt-2">Complete staff database with performance metrics</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Employee ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Salary</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Last Sale</th>
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
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo htmlspecialchars($employee['role_name'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($employee['E_Sal'], 0); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-900"><?php echo $employee['Total_Sales']; ?> sales</div>
                                    <div class="text-xs text-slate-500">Rs. <?php echo number_format($employee['Total_Sales_Amount'], 0); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php echo $employee['Last_Sale_Date'] ? date('M j, Y', strtotime($employee['Last_Sale_Date'])) : 'Never'; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="employee_performance.php?id=<?php echo $employee['E_ID']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-bold text-sm mr-3">
                                        Performance
                                    </a>
                                    <a href="edit_new.php?id=<?php echo $employee['E_ID']; ?>" 
                                       class="text-emerald-600 hover:text-emerald-800 font-bold text-sm">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 006-6h.645a4 4 0 00.083-.732z"></path>
                                </svg>
                                No employees found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Top Performers -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Top Sales Performers</h3>
            <?php
            $top_performers = $conn->query("
                SELECT e.E_ID, e.E_Fname, e.E_Lname, e.E_Type, 
                       COUNT(s.Sale_ID) as Total_Sales,
                       COALESCE(SUM(s.Total_Amt), 0) as Total_Sales_Amount
                FROM employee e
                LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0
                GROUP BY e.E_ID, e.E_Fname, e.E_Lname, e.E_Type
                HAVING Total_Sales > 0
                ORDER BY Total_Sales_Amount DESC
                LIMIT 5
            ");
            ?>
            <div class="space-y-4">
                <?php if ($top_performers && $top_performers->num_rows > 0): ?>
                    <?php while($emp = $top_performers->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold">
                                    <?php echo strtoupper(substr($emp['E_Fname'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($emp['E_Fname'] . ' ' . $emp['E_Lname']); ?></div>
                                    <div class="text-sm text-slate-500"><?php echo htmlspecialchars($emp['E_Type']); ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?php echo $emp['Total_Sales']; ?> sales</div>
                                <div class="text-sm text-slate-500">Rs. <?php echo number_format($emp['Total_Sales_Amount'], 0); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No sales data available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Role Distribution</h3>
            <canvas id="roleChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        // Role Distribution Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        const roleData = <?php 
            $role_stats = $conn->query("
                SELECT r.role_name, COUNT(e.E_ID) as count 
                FROM roles r
                LEFT JOIN employee e ON r.role_id = e.role_id
                GROUP BY r.role_name
                ORDER BY count DESC
            ");
            $labels = [];
            $counts = [];
            while($row = $role_stats->fetch_assoc()) {
                $labels[] = $row['role_name'];
                $counts[] = $row['count'];
            }
            echo json_encode(['labels' => $labels, 'data' => $counts]);
        ?>;
        
        new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: roleData.labels,
                datasets: [{
                    data: roleData.data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
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
