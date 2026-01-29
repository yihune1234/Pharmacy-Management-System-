<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get filter parameters
$action_filter = $_GET['action'] ?? '';
$user_filter = $_GET['user'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build base query
$sql = "SELECT al.*, e.E_Fname, e.E_Lname, r.role_name
        FROM activity_logs al
        LEFT JOIN employee e ON al.user_id = e.E_ID
        LEFT JOIN roles r ON e.role_id = r.role_id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($action_filter)) {
    $sql .= " AND al.action = ?";
    $params[] = $action_filter;
    $types .= 's';
}

if (!empty($user_filter)) {
    $sql .= " AND al.user_id = ?";
    $params[] = $user_filter;
    $types .= 'i';
}

if (!empty($date_from)) {
    $sql .= " AND DATE(al.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $sql .= " AND DATE(al.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$sql .= " ORDER BY al.created_at DESC LIMIT 100";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Get unique actions for filter dropdown
$actions = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");

// Get users for filter dropdown
$users = $conn->query("SELECT e.E_ID, e.E_Fname, e.E_Lname, r.role_name FROM employee e LEFT JOIN roles r ON e.role_id = r.role_id ORDER BY e.E_Fname, e.E_Lname");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Activity Logs</h2>
            <p class="text-slate-500 mt-1 font-medium">Monitor system activity and user actions</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="exportLogs()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3 3m3 0l-3-3m3 0V7a3 3 0 003-3h3a3 3 0 003 3v3m0 0h-3m-3 6h3m-3-6h3m6 6v-3m-3 3h3"></path></svg>
                Export Logs
            </button>
            <a href="view_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Employees
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-8">
        <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Action Filter</label>
                <select name="action" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">All Actions</option>
                    <?php while($action = $actions->fetch_assoc()): ?>
                        <option value="<?php echo $action['action']; ?>" <?php echo ($action_filter == $action['action']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($action['action']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">User Filter</label>
                <select name="user" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">All Users</option>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo $user['E_ID']; ?>" <?php echo ($user_filter == $user['E_ID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['E_Fname'] . ' ' . $user['E_Lname'] . ' (' . $user['role_name'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Date From</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Date To</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            
            <div class="md:col-span-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all">
                    Apply Filters
                </button>
                <a href="activity_logs.php" class="ml-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-6 rounded-xl transition-all">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Activities</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $total_activities = $conn->query("SELECT COUNT(*) as count FROM activity_logs")->fetch_assoc()['count'];
                        echo number_format($total_activities);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 9l2 2 4-4m0 0l-4 4m0 0l-4 4"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today's Activities</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $today_activities = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
                        echo number_format($today_activities);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Users</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $active_users = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM activity_logs WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
                        echo number_format($active_users);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Security Alerts</p>
                    <p class="text-2xl font-black text-slate-900"><?php 
                        $security_alerts = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE action IN ('LOGIN_FAILED', 'ACCESS_DENIED', 'SECURITY_BREACH')")->fetch_assoc()['count'];
                        echo number_format($security_alerts);
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 1.667 1.732 3L13.732 20c.77 1.333 2.694 1.333 3.464 0l6.938-4c.77-1.333.192-1.667-1.732-3L13.732 4z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Recent Activities</h3>
            <p class="text-slate-600 mt-2">Latest system activities and user actions</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($log = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M j, Y h:i A', strtotime($log['created_at'])); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-slate-900"><?php echo htmlspecialchars($log['E_Fname'] . ' . $log['E_Lname']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($log['role_name'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold <?php 
                                        $action_colors = [
                                            'LOGIN' => 'bg-green-100 text-green-700',
                                            'LOGOUT' => 'bg-gray-100 text-gray-700',
                                            'ADD_EMPLOYEE' => 'bg-blue-100 text-blue-700',
                                            'EDIT_EMPLOYEE' => 'bg-amber-100 text-amber-700',
                                            'DELETE_EMPLOYEE' => 'bg-red-100 text-red-700',
                                            'LOGIN_FAILED' => 'bg-red-100 text-red-700',
                                            'ACCESS_DENIED' => 'bg-red-100 text-red-700',
                                            'SECURITY_BREACH' => 'bg-red-100 text-red-700'
                                        ];
                                        $action_color = $action_colors[$log['action']] ?? 'bg-slate-100 text-slate-700';
                                        echo $action_color;
                                    ?>">
                                        <?php echo ucfirst($log['action']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($log['description']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No activity logs found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Activity Trend (Last 7 Days)</h3>
            <canvas id="activityChart" width="400" height="200"></canvas>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Action Distribution</h3>
            <canvas id="actionChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        // Activity Trend Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityData = <?php 
            $trend_data = $conn->query("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM activity_logs 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            $dates = [];
            $counts = [];
            while($row = $trend_data->fetch_assoc()) {
                $dates[] = date('M j', strtotime($row['date']));
                $counts[] = $row['count'];
            }
            echo json_encode(['labels' => $dates, 'data' => $counts]);
        ?>;
        
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: activityData.labels,
                datasets: [{
                    label: 'Activities',
                    data: activityData.data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        });

        // Action Distribution Chart
        const actionCtx = document.getElementById('actionChart').getContext('2d');
        const actionData = <?php 
            $action_stats = $conn->query("
                SELECT action, COUNT(*) as count
                FROM activity_logs
                GROUP BY action
                ORDER BY count DESC
                LIMIT 10
            ");
            $labels = [];
            $counts = [];
            while($row = $action_stats->fetch_assoc()) {
                $labels[] = ucfirst($row['action']);
                $counts[] = $row['count'];
            }
            echo json_encode(['labels' => $labels, 'data' => $counts]);
        ?>;
        
        new Chart(actionCtx, {
            type: 'bar',
            data: {
                labels: actionData.labels,
                datasets: [{
                    label: 'Count',
                    data: actionData.data,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
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
                        stepSize: 1
                    }
                }
            }
        });

        function exportLogs() {
            window.open('export_logs.php?' + new URLSearchParams({
                action: '<?php echo $action_filter; ?>',
                user: '<?php echo $user_filter; ?>',
                date_from: '<?php echo $date_from; ?>',
                date_to: '<?php echo $date_to; ?>'
            }), '_blank');
        }
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
