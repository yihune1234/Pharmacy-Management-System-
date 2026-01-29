<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Enhanced alert system with sound notifications
class AlertSystem {
    private static $alerts = [];
    private static $persistent_log_file = __DIR__ . '/../../../logs/alerts.log';
    
    public static function addAlert($type, $message, $data = []) {
        $alert = [
            'id' => uniqid(),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];
        
        self::$alerts[] = $alert;
        self::logAlert($alert);
        self::triggerNotification($alert);
    }
    
    private static function logAlert($alert) {
        // Create logs directory if it doesn't exist
        $log_dir = dirname(self::$persistent_log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_entry = json_encode($alert) . "\n";
        file_put_contents(self::$persistent_log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    private static function triggerNotification($alert) {
        // Flash message for immediate display
        $_SESSION['alerts'][] = $alert;
        
        // Sound notification for critical alerts
        if (in_array($alert['type'], ['error', 'warning', 'critical'])) {
            $_SESSION['play_sound'] = $alert['type'];
        }
        
        // Email notification for critical alerts
        if ($alert['type'] === 'critical') {
            self::sendEmailNotification($alert);
        }
    }
    
    private static function sendEmailNotification($alert) {
        $to = 'admin@pharmacia.com'; // Configure admin email
        $subject = 'CRITICAL ALERT: ' . $alert['message'];
        $body = "A critical alert has been triggered:\n\n";
        $body .= "Message: " . $alert['message'] . "\n";
        $body .= "Time: " . $alert['timestamp'] . "\n";
        $body .= "User: " . ($alert['user_id'] ?? 'Unknown') . "\n";
        $body .= "IP: " . $alert['ip_address'] . "\n";
        
        if (!empty($alert['data'])) {
            $body .= "\nAdditional Data:\n" . json_encode($alert['data'], JSON_PRETTY_PRINT);
        }
        
        // Send email (configure SMTP settings)
        // mail($to, $subject, $body);
    }
    
    public static function getAlerts($limit = 50) {
        $alerts = [];
        if (file_exists(self::$persistent_log_file)) {
            $lines = array_slice(file(self::$persistent_log_file, FILE_IGNORE_NEW_LINES), -$limit);
            foreach ($lines as $line) {
                $alerts[] = json_decode($line, true);
            }
        }
        return array_reverse($alerts);
    }
    
    public static function checkLowStock() {
        global $conn;
        
        $low_stock = $conn->query("
            SELECT Med_ID, Med_Name, Med_Qty, Location_Rack
            FROM meds
            WHERE Med_Qty <= 10
            ORDER BY Med_Qty ASC
        ");
        
        if ($low_stock && $low_stock->num_rows > 0) {
            while($med = $low_stock->fetch_assoc()) {
                self::addAlert('warning', 'Low Stock Alert: ' . $med['Med_Name'], [
                    'medicine_id' => $med['Med_ID'],
                    'current_stock' => $med['Med_Qty'],
                    'location' => $med['Location_Rack'],
                    'threshold' => 10
                ]);
            }
        }
    }
    
    public static function checkExpiryAlerts() {
        global $conn;
        
        try {
            // Use purchase table for expiry dates since meds table doesn't have Exp_Date
            $expiry_alerts = $conn->query("
                SELECT DISTINCT p.Med_ID, m.Med_Name, p.Exp_Date, p.P_Qty as Med_Qty
                FROM purchase p
                JOIN meds m ON p.Med_ID = m.Med_ID
                WHERE p.Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND p.Exp_Date >= CURDATE()
                ORDER BY p.Exp_Date ASC
            ");
            
            if ($expiry_alerts && $expiry_alerts->num_rows > 0) {
                while($med = $expiry_alerts->fetch_assoc()) {
                    $days_to_expiry = (strtotime($med['Exp_Date']) - time()) / (60 * 60 * 24);
                    $severity = $days_to_expiry <= 0 ? 'critical' : ($days_to_expiry <= 7 ? 'error' : 'warning');
                    
                    self::addAlert($severity, 'Expiry Alert: ' . $med['Med_Name'], [
                        'medicine_id' => $med['Med_ID'],
                        'expiry_date' => $med['Exp_Date'],
                        'days_to_expiry' => round($days_to_expiry),
                        'quantity' => $med['Med_Qty']
                    ]);
                }
            }
        } catch (Exception $e) {
            self::addAlert('error', 'Unable to check expiry alerts: ' . $e->getMessage(), [
                'error_type' => 'database_error',
                'query' => 'expiry_alerts_check'
            ]);
        }
    }
    
    public static function checkSystemHealth() {
        // Check database connection
        global $conn;
        
        if (!$conn) {
            self::addAlert('critical', 'Database Connection Failed', [
                'error' => 'Unable to connect to database',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Check disk space
        $total_space = disk_total_space('/');
        $free_space = disk_free_space('/');
        $used_space = $total_space - $free_space;
        $usage_percent = ($used_space / $total_space) * 100;
        
        if ($usage_percent > 90) {
            self::addAlert('critical', 'Disk Space Critical', [
                'usage_percent' => round($usage_percent, 2),
                'free_space' => round($free_space / 1024 / 1024 / 1024, 2) . ' GB'
            ]);
        } elseif ($usage_percent > 80) {
            self::addAlert('warning', 'Disk Space Warning', [
                'usage_percent' => round($usage_percent, 2),
                'free_space' => round($free_space / 1024 / 1024 / 1024, 2) . ' GB'
            ]);
        }
        
        // Check memory usage
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        $memory_percent = ($memory_usage / ($memory_limit * 1024 * 1024)) * 100;
        
        if ($memory_percent > 90) {
            self::addAlert('warning', 'High Memory Usage', [
                'usage_percent' => round($memory_percent, 2),
                'current_usage' => round($memory_usage / 1024 / 1024, 2) . ' MB',
                'limit' => $memory_limit
            ]);
        }
    }
}

// Check for alerts on page load
AlertSystem::checkLowStock();
AlertSystem::checkExpiryAlerts();
AlertSystem::checkSystemHealth();

// Handle manual alert creation
if (isset($_POST['create_alert'])) {
    $type = $_POST['alert_type'] ?? 'info';
    $message = $_POST['alert_message'] ?? '';
    $data = $_POST['alert_data'] ?? [];
    
    if (!empty($message)) {
        AlertSystem::addAlert($type, $message, $data);
        set_flash_message("Alert created successfully!", "success");
        header("Location: alerts.php");
        exit();
    }
}

// Get alerts for display
$alerts = AlertSystem::getAlerts(100);
$flash_alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);

// Sound notification
$sound_to_play = $_SESSION['play_sound'] ?? null;
unset($_SESSION['play_sound']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts & Notifications - PHARMACIA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <audio id="alertSound" style="display: none;">
        <source src="/assets/sounds/alert.mp3" type="audio/mpeg">
        <source src="/assets/sounds/alert.wav" type="audio/wav">
    </audio>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Alerts & Notifications</h2>
            <p class="text-slate-500 mt-1 font-medium">System alerts and notification management</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="clearAllAlerts()" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Clear All
            </button>
            <button onclick="testAlert()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-amber-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5m0 0l-5 5m5-5v10m9 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Test Alert
            </button>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if (!empty($flash_alerts)): ?>
        <div class="mb-6 space-y-2">
            <?php foreach ($flash_alerts as $alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?> p-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php if ($alert['type'] === 'success'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <?php elseif ($alert['type'] === 'error'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <?php elseif ($alert['type'] === 'warning'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M3 20a6 6 0 0112 0v-6a6 6 0 00-12 0v6a6 6 0 00-6-6h-2m0 10a6 6 0 006 6h10a6 6 0 006-6v-10a6 6 0 00-6-6h-2"></path>
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <?php endif; ?>
                        </svg>
                        <div>
                            <div class="font-bold text-slate-900"><?php echo htmlspecialchars($alert['message']); ?></div>
                            <div class="text-xs text-slate-500"><?php echo $alert['timestamp']; ?></div>
                        </div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Alert Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Alerts</p>
                    <p class="text-2xl font-black text-slate-900"><?php echo count($alerts); ?></p>
                </div>
                <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Critical</p>
                    <p class="text-2xl font-black text-red-600"><?php 
                        $critical = count(array_filter($alerts, fn($a) => $a['type'] === 'critical'));
                        echo $critical;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M3 20a6 6 0 0112 0v-6a6 6 0 00-12 0v6a6 6 0 00-6-6h-2m0 10a6 6 0 006 6h10a6 6 0 006-6v-10a6 6 0 00-6-6h-2"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Warnings</p>
                    <p class="text-2xl font-black text-amber-600"><?php 
                        $warnings = count(array_filter($alerts, fn($a) => $a['type'] === 'warning'));
                        echo $warnings;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M3 20a6 6 0 0112 0v-6a6 6 0 00-12 0v6a6 6 0 00-6-6h-2m0 10a6 6 0 006 6h10a6 6 0 006-6v-10a6 6 0 00-6-6h-2"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today</p>
                    <p class="text-2xl font-black text-blue-600"><?php 
                        $today = count(array_filter($alerts, fn($a) => date('Y-m-d', strtotime($a['timestamp'])) === date('Y-m-d')));
                        echo $today;
                    ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert History -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-red-50 to-amber-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Alert History</h3>
            <p class="text-slate-600 mt-2">Recent system alerts and notifications</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($alerts)): ?>
                        <?php foreach ($alerts as $alert): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo $alert['timestamp']; ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold <?php 
                                        $type_colors = [
                                            'critical' => 'bg-red-100 text-red-700',
                                            'error' => 'bg-red-100 text-red-700',
                                            'warning' => 'bg-amber-100 text-amber-700',
                                            'success' => 'bg-emerald-100 text-emerald-700',
                                            'info' => 'bg-blue-100 text-blue-700'
                                        ];
                                        echo $type_colors[$alert['type']] ?? 'bg-slate-100 text-slate-700';
                                    ?>">
                                        <?php echo ucfirst($alert['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900"><?php echo htmlspecialchars($alert['message']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo $alert['user_id'] ?? 'System'; ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?php echo $alert['ip_address']; ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <button onclick="viewAlertDetails(<?php echo htmlspecialchars(json_encode($alert)); ?>)" 
                                            class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                        Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No alerts found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alert Chart -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mt-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Alert Distribution</h3>
        <canvas id="alertChart" width="400" height="200"></canvas>
    </div>

    <!-- Alert Details Modal -->
    <div id="alertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 max-w-2xl w-full mx-4">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Alert Details</h3>
            <div id="alertDetails"></div>
            <button onclick="closeAlertModal()" class="mt-6 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-6 rounded-xl transition-all">
                Close
            </button>
        </div>
    </div>

    <script>
        // Play sound notification
        <?php if ($sound_to_play): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const audio = document.getElementById('alertSound');
                audio.play().catch(e => console.log('Audio play failed:', e));
            });
        <?php endif; ?>

        // Alert Chart
        const alertCtx = document.getElementById('alertChart').getContext('2d');
        const alertData = <?php 
            $alert_stats = [];
            $types = ['critical', 'error', 'warning', 'success', 'info'];
            foreach ($types as $type) {
                $count = count(array_filter($alerts, fn($a) => $a['type'] === $type));
                $alert_stats[] = [
                    'type' => ucfirst($type),
                    'count' => $count
                ];
            }
            echo json_encode($alert_stats);
        ?>;
        
        new Chart(alertCtx, {
            type: 'bar',
            data: {
                labels: alertData.map(item => item.type),
                datasets: [{
                    label: 'Alert Count',
                    data: alertData.map(item => item.count),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(239, 68, 68)',
                        'rgb(239, 68, 68)',
                        'rgb(245, 158, 11)',
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)'
                    ],
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
            }
        });

        function viewAlertDetails(alert) {
            const details = document.getElementById('alertDetails');
            details.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <strong>Type:</strong> <span class="px-2 py-1 rounded-full text-xs font-bold bg-${alert.type}-100 text-${alert.type}-700">${alert.type}</span>
                    </div>
                    <div>
                        <strong>Message:</strong> ${alert.message}
                    </div>
                    <div>
                        <strong>Timestamp:</strong> ${alert.timestamp}
                    </div>
                    <div>
                        <strong>User:</strong> ${alert.user_id || 'System'}
                    </div>
                    <div>
                        <strong>IP Address:</strong> ${alert.ip_address}
                    </div>
                    ${alert.data ? `
                        <div>
                            <strong>Additional Data:</strong>
                            <pre class="bg-slate-50 p-4 rounded-xl mt-2">${JSON.stringify(alert.data, null, 2)}</pre>
                        </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('alertModal').classList.remove('hidden');
        }

        function closeAlertModal() {
            document.getElementById('alertModal').classList.add('hidden');
        }

        function clearAllAlerts() {
            if (confirm('Are you sure you want to clear all alerts?')) {
                // Clear persistent log
                fetch('clear_alerts.php', {method: 'POST'})
                    .then(() => location.reload());
            }
        }

        function testAlert() {
            fetch('test_alert.php', {method: 'POST'})
                .then(() => location.reload());
        }
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
