<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get loyalty statistics (Mocked as columns missing)
$loyalty_stats = false; 

// Get top customers by spend (Used as proxy for loyalty)
$top_customers = $conn->query("
    SELECT C_ID, C_Fname, C_Lname, 0 as Loyalty_Points, 'Bronze' as Loyalty_Tier,
           (SELECT COALESCE(SUM(Total_Amt), 0) FROM sales WHERE C_ID = c.C_ID) as Total_Spent
    FROM customer c
    ORDER BY Total_Spent DESC
    LIMIT 10
");

// Get monthly loyalty trends
$monthly_trends = $conn->query("
    SELECT DATE_FORMAT(Created_At, '%Y-%m') as Month,
           COUNT(*) as New_Customers,
           0 as Loyalty_Customers
    FROM customer
    WHERE Created_At >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(Created_At, '%Y-%m')
    ORDER BY Month DESC
");

// Get loyalty tier distribution (Mocked)
$tier_distribution = false;

// Get recent loyalty activity (Mocked as recent sales)
$recent_activity = $conn->query("
    SELECT c.C_Fname, c.C_Lname, 0 as Loyalty_Points, 'Bronze' as Loyalty_Tier, c.Updated_At,
           s.Sale_ID, s.Total_Amt
    FROM customer c
    LEFT JOIN sales s ON c.C_ID = s.C_ID AND DATE(s.S_Date) = DATE(c.Updated_At)
    WHERE c.Updated_At >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY c.Updated_At DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Report - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <p class="subheading-premium">Loyalty Intelligence</p>
            <h1 class="heading-premium">Reward Protocol</h1>
            <p class="text-slate-500 mt-1 font-medium">Customer retention analytics and value distribution.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-print mr-2 text-slate-400"></i> Generate PDF
            </button>
            <a href="view_new.php" class="btn-primary btn-blue !px-8">
                <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform"></i> Return to Portfolio
            </a>
        </div>
    </div>

    <!-- Analytics Hub -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
        <div class="premium-card p-8">
            <div class="stat-icon bg-amber-50 text-amber-600 mb-6">
                <i class="fas fa-crown text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Members</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none">0</h3>
            <p class="text-[10px] font-bold text-amber-500 mt-3 uppercase tracking-widest">Active Loyalty Base</p>
        </div>

        <div class="premium-card p-8 bg-slate-900 !border-slate-800 shadow-2xl">
            <div class="relative z-10">
                <div class="stat-icon bg-white/10 text-white backdrop-blur mb-6">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">High Value</p>
                <h3 class="text-3xl font-black text-white leading-none">0</h3>
                <p class="text-[10px] font-bold text-amber-400 mt-3 uppercase tracking-widest">Elite Tier Count</p>
            </div>
        </div>

        <div class="premium-card p-8">
            <div class="stat-icon bg-emerald-50 text-emerald-600 mb-6">
                <i class="fas fa-coins text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Points</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none">0</h3>
            <p class="text-[10px] font-bold text-emerald-500 mt-3 uppercase tracking-widest">Global Rewards Pool</p>
        </div>

        <div class="premium-card p-8">
            <div class="stat-icon bg-blue-50 text-blue-600 mb-6">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg Points</p>
            <h3 class="text-3xl font-black text-slate-900 leading-none">0</h3>
            <p class="text-[10px] font-bold text-blue-500 mt-3 uppercase tracking-widest">Per Capita Level</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-12">
        <div class="premium-card p-10">
            <h3 class="text-lg font-black text-slate-900 uppercase italic mb-8">Tier Distribution</h3>
            <div class="h-64 relative">
                <canvas id="tierChart"></canvas>
            </div>
        </div>

        <div class="premium-card p-10">
            <h3 class="text-lg font-black text-slate-900 uppercase italic mb-8">Acquisition Velocity</h3>
            <div class="h-64 relative">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Top Customers -->
        <div class="premium-card overflow-hidden !p-0">
            <div class="p-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-900 uppercase italic">Key Loyalty Assets</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">High Frequency Partners</p>
            </div>
            
            <div class="p-8 space-y-6">
                <?php if ($top_customers && $top_customers->num_rows > 0): ?>
                    <?php while($customer = $top_customers->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-5 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-blue-200 transition-all hover:translate-x-1 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center font-black text-xs text-slate-400 group-hover:bg-blue-600 group-hover:text-white shadow-sm transition-all border border-slate-100">
                                    <?php echo strtoupper(substr($customer['C_Fname'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors uppercase italic"><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></div>
                                    <div class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter italic"><?php echo $customer['Loyalty_Tier']; ?> Protocol</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-slate-900 italic"><?php echo number_format($customer['Loyalty_Points']); ?> PTS</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic">Rs. <?php echo number_format($customer['Total_Spent'], 0); ?> TOTAL</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-400 font-bold italic text-center py-8">No loyalty synchronization.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="premium-card overflow-hidden !p-0">
            <div class="p-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-900 uppercase italic">Recent Operations</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Live Program Flux</p>
            </div>
            
            <div class="p-8 space-y-6">
                <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                    <?php while($activity = $recent_activity->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-5 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-emerald-200 transition-all group">
                            <div>
                                <div class="text-sm font-black text-slate-900 group-hover:text-emerald-600 transition-colors uppercase italic"><?php echo htmlspecialchars($activity['C_Fname'] . ' ' . $activity['C_Lname']); ?></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter italic">
                                    <?php if ($activity['Total_Amt'] > 0): ?>
                                        TRANSACTION: RS. <?php echo number_format($activity['Total_Amt'], 0); ?>
                                    <?php else: ?>
                                        SYSTEM REVALUATION
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-slate-900 italic">+<?php echo $activity['Loyalty_Points']; ?> PTS</div>
                                <div class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]"><?php echo date('d M Y', strtotime($activity['Updated_At'])); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-slate-400 font-bold italic text-center py-8">No recent log entries.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tier Distribution Chart
        const tierCtx = document.getElementById('tierChart').getContext('2d');
        const tierData = <?php 
            $tiers = [];
            $counts = [];
            if ($tier_distribution) {
                while($row = $tier_distribution->fetch_assoc()) {
                    $tiers[] = $row['Loyalty_Tier'];
                    $counts[] = $row['count'];
                }
            }
            echo json_encode(['labels' => $tiers, 'data' => $counts]);
        ?>;
        
        new Chart(tierCtx, {
            type: 'doughnut',
            data: {
                labels: tierData.labels,
                datasets: [{
                    data: tierData.data,
                    backgroundColor: [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(156, 163, 175, 0.8)',
                        'rgba(205, 127, 50, 0.8)'
                    ],
                    borderColor: [
                        'rgb(251, 191, 36)',
                        'rgb(156, 163, 175)',
                        'rgb(205, 127, 50)'
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

        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = <?php 
            $months = [];
            $new_customers = [];
            $loyalty_customers = [];
            if ($monthly_trends) {
                while($row = $monthly_trends->fetch_assoc()) {
                    $months[] = date('M Y', strtotime($row['Month'] . '-01'));
                    $new_customers[] = $row['New_Customers'];
                    $loyalty_customers[] = $row['Loyalty_Customers'];
                }
            }
            echo json_encode(['labels' => $months, 'new_customers' => $new_customers, 'loyalty_customers' => $loyalty_customers]);
        ?>;
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'New Customers',
                    data: monthlyData.new_customers,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Loyalty Customers',
                    data: monthlyData.loyalty_customers,
                    borderColor: 'rgb(251, 191, 36)',
                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
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
