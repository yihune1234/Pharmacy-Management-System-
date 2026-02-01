<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get employees with sales information
$sql = "SELECT e.*,
               COUNT(s.Sale_ID) as Total_Sales,
               COALESCE(SUM(s.Total_Amt), 0) as Total_Sales_Amount,
               MAX(s.S_Date) as Last_Sale_Date
        FROM employee e
        LEFT JOIN sales s ON e.E_ID = s.E_ID
        GROUP BY e.E_ID
        ORDER BY e.E_Fname, e.E_Lname";

$result = $conn->query($sql);

// Summary statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'] ?? 0;
$total_salary = $conn->query("SELECT SUM(E_Sal) as total FROM employee")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Human Capital Console - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header Section -->
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="subheading-premium">Human Resources</p>
            <h1 class="heading-premium">Staff Management</h1>
            <p class="text-slate-500 font-medium mt-1">Monitor operational workforce performance and system access.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="salary_tracking.php" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-wallet mr-2 text-blue-600"></i> Payroll Status
            </a>
            <a href="add_new.php" class="btn-primary btn-blue !px-8">
                <i class="fas fa-user-plus mr-3 transform group-hover:scale-110 transition-transform"></i> Recruit Staff
            </a>
        </div>
    </div>

    <!-- Quick Stats Hub -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="premium-card p-8">
            <div class="stat-icon bg-blue-50 text-blue-600 mb-6">
                <i class="fas fa-users-gear text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Assets</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none"><?php echo $total_employees; ?> Employees</h3>
            <p class="text-[10px] font-bold text-emerald-500 mt-4 uppercase tracking-tighter">Verified Professionals</p>
        </div>
        
        <div class="premium-card p-8 bg-slate-900 !border-slate-800 shadow-2xl">
            <div class="relative z-10">
                <div class="stat-icon bg-white/10 text-white backdrop-blur mb-6">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Monthly Payroll</p>
                <h3 class="text-3xl font-black text-white leading-none">Rs. <?php echo number_format($total_salary, 0); ?></h3>
                <p class="text-[10px] font-bold text-blue-400 mt-4 uppercase tracking-tighter">Gross Expenditure</p>
            </div>
        </div>

        <div class="premium-card p-8">
            <div class="stat-icon bg-purple-50 text-purple-600 mb-6">
                <i class="fas fa-trophy text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Efficiency Level</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none">Optimal</h3>
            <p class="text-[10px] font-bold text-purple-500 mt-4 uppercase tracking-tighter">High Output Protocol</p>
        </div>
    </div>

    <!-- Staff Directory Matrix -->
    <div class="premium-card overflow-hidden mb-12">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900 uppercase italic">Active Staff Registry</h3>
            <span class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-blue-100 italic">Personnel Index</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-white">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Matrix ID</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Personnel</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Assignment</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Valuation</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Activity</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Protocol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($employee = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-6">
                                    <span class="text-[10px] font-black text-slate-400 tracking-widest">#EMP-<?php echo $employee['E_ID']; ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-black text-xs group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            <?php echo strtoupper(substr($employee['E_Fname'], 0, 1)); ?>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight italic"><?php echo htmlspecialchars($employee['E_Fname'] . ' ' . $employee['E_Lname']); ?></span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic">Verified Professional</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="px-4 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black tracking-widest uppercase italic"><?php echo htmlspecialchars($employee['E_Type']); ?></span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 tracking-tight italic">Rs. <?php echo number_format($employee['E_Sal'], 0); ?></span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter italic">Payout</span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-1.5 h-1.5 rounded-full <?php echo $employee['Last_Sale_Date'] ? 'bg-emerald-500' : 'bg-slate-300'; ?>"></div>
                                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest italic">
                                            <?php echo $employee['Last_Sale_Date'] ? date('j M Y', strtotime($employee['Last_Sale_Date'])) : 'Inactive'; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="employee_performance.php?id=<?php echo $employee['E_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-chart-line text-xs"></i>
                                        </a>
                                        <a href="edit_new.php?id=<?php echo $employee['E_ID']; ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-id-card-clip text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Insights Hub -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div class="premium-card p-10 bg-white/50 backdrop-blur-sm">
            <div class="flex items-center space-x-3 mb-8">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-ranking-star"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 uppercase italic tracking-tight">Top Performance</h3>
            </div>
            <div class="space-y-4">
                <?php
                $leaderboard = $conn->query("SELECT e.E_Fname, COALESCE(SUM(s.Total_Amt), 0) as total FROM employee e LEFT JOIN sales s ON e.E_ID = s.E_ID GROUP BY e.E_ID ORDER BY total DESC LIMIT 3");
                while($top = $leaderboard->fetch_assoc()):
                ?>
                <div class="flex items-center justify-between p-5 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-blue-200 transition-colors">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic"><?php echo $top['E_Fname']; ?></span>
                    <span class="text-sm font-black text-blue-600 tracking-tight italic">Rs. <?php echo number_format($top['total'], 0); ?></span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="premium-card p-10 flex flex-col items-center">
            <div class="flex items-center space-x-3 mb-8 w-full text-left">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 uppercase italic tracking-tight">Staff Composition</h3>
            </div>
            <div class="h-48 w-48 relative">
                <canvas id="compositionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Closing Sidebar Tags -->
    </main>
    </div>
    </div>

    <script>
        const compCtx = document.getElementById('compositionChart').getContext('2d');
        new Chart(compCtx, {
            type: 'doughnut',
            data: {
                labels: ['Admin', 'Manager', 'Pharmacist'],
                datasets: [{
                    data: [1, 2, 7], // Example static data for aesthetic
                    backgroundColor: ['#2563eb', '#10b981', '#fbbf24'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>
