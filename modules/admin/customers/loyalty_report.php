<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get loyalty statistics
$loyalty_stats = $conn->query("
    SELECT 
        Loyalty_Tier,
        COUNT(*) as Customer_Count,
        COALESCE(SUM(Loyalty_Points), 0) as Total_Points,
        COALESCE(AVG(Loyalty_Points), 0) as Avg_Points
    FROM customer 
    GROUP BY Loyalty_Tier
    ORDER BY 
        CASE Loyalty_Tier 
            WHEN 'Gold' THEN 1 
            WHEN 'Silver' THEN 2 
            WHEN 'Bronze' THEN 3 
        END
");

// Get top customers by points
$top_customers = $conn->query("
    SELECT C_ID, C_Fname, C_Lname, Loyalty_Points, Loyalty_Tier,
           (SELECT COALESCE(SUM(Total_Amt), 0) FROM sales WHERE C_ID = c.C_ID AND Refunded = 0) as Total_Spent
    FROM customer c
    WHERE Loyalty_Points > 0
    ORDER BY Loyalty_Points DESC
    LIMIT 10
");

// Get monthly loyalty trends
$monthly_trends = $conn->query("
    SELECT DATE_FORMAT(Created_At, '%Y-%m') as Month,
           COUNT(*) as New_Customers,
           SUM(CASE WHEN Loyalty_Points > 0 THEN 1 ELSE 0 END) as Loyalty_Customers
    FROM customer
    WHERE Created_At >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(Created_At, '%Y-%m')
    ORDER BY Month DESC
");

// Get loyalty tier distribution
$tier_distribution = $conn->query("
    SELECT 
        Loyalty_Tier,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM customer), 2) as percentage
    FROM customer
    GROUP BY Loyalty_Tier
    ORDER BY 
        CASE Loyalty_Tier 
            WHEN 'Gold' THEN 1 
            WHEN 'Silver' THEN 2 
            WHEN 'Bronze' THEN 3 
        END
");

// Get recent loyalty activity
$recent_activity = $conn->query("
    SELECT c.C_Fname, c.C_Lname, c.Loyalty_Points, c.Loyalty_Tier, c.Updated_At,
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

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Loyalty Program Report</h2>
            <p class="text-slate-500 mt-1 font-medium">Customer loyalty analytics and performance metrics</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Report
            </button>
            <a href="view_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Manage Customers
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Members</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $total_members = $conn->query("SELECT COUNT(*) as count FROM customer WHERE Loyalty_Points > 0")->fetch_assoc()['count'];
                        echo $total_members;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Gold Members</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $gold_members = $conn->query("SELECT COUNT(*) as count FROM customer WHERE Loyalty_Tier = 'Gold'")->fetch_assoc()['count'];
                        echo $gold_members;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Points</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $total_points = $conn->query("SELECT SUM(Loyalty_Points) as total FROM customer")->fetch_assoc()['total'];
                        echo number_format($total_points, 0);
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
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Avg Points</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $avg_points = $conn->query("SELECT AVG(Loyalty_Points) as avg FROM customer WHERE Loyalty_Points > 0")->fetch_assoc()['avg'];
                        echo number_format($avg_points, 0);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Tier Distribution Chart -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Tier Distribution</h3>
            <canvas id="tierChart" width="400" height="200"></canvas>
        </div>

        <!-- Monthly Trends Chart -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Monthly Customer Acquisition</h3>
            <canvas id="monthlyChart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Customers -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-8 py-6 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900">Top Loyalty Customers</h3>
                <p class="text-slate-600 mt-2">Customers with highest loyalty points</p>
            </div>
            
            <div class="p-6">
                <?php if ($top_customers && $top_customers->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($customer = $top_customers->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center font-bold">
                                        <?php echo strtoupper(substr($customer['C_Fname'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900"><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></div>
                                        <div class="text-sm text-slate-500"><?php echo $customer['Loyalty_Tier']; ?> Tier</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-900"><?php echo number_format($customer['Loyalty_Points']); ?> pts</div>
                                    <div class="text-sm text-slate-500">Rs. <?php echo number_format($customer['Total_Spent'], 0); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No loyalty customers found</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900">Recent Activity</h3>
                <p class="text-slate-600 mt-2">Latest loyalty program updates</p>
            </div>
            
            <div class="p-6">
                <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($activity = $recent_activity->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div>
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($activity['C_Fname'] . ' ' . $activity['C_Lname']); ?></div>
                                    <div class="text-sm text-slate-500">
                                        <?php if ($activity['Sale_ID']): ?>
                                            Purchase of Rs. <?php echo number_format($activity['Total_Amt'], 0); ?>
                                        <?php else: ?>
                                            Points updated
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-900"><?php echo $activity['Loyalty_Points']; ?> pts</div>
                                    <div class="text-xs text-slate-400"><?php echo date('M j, Y', strtotime($activity['Updated_At'])); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 text-center py-8">No recent activity</p>
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
