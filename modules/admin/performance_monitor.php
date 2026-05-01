<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/caching.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Initialize cache
$cache = get_cache();

// Get database performance metrics
$db_metrics = [];

// Query execution time tracking
$start_time = microtime(true);

// Get table sizes and row counts
$stmt = $conn->prepare("
    SELECT 
        TABLE_NAME,
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
        TABLE_ROWS
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY (data_length + index_length) DESC
");
$stmt->execute();
$table_stats = $stmt->get_result();
$stmt->close();

$tables_data = [];
$total_db_size = 0;
while ($row = $table_stats->fetch_assoc()) {
    $tables_data[] = $row;
    $total_db_size += $row['size_mb'];
}

$query_time_1 = microtime(true) - $start_time;

// Get index information
$start_time = microtime(true);

$stmt = $conn->prepare("
    SELECT 
        TABLE_NAME,
        INDEX_NAME,
        COLUMN_NAME,
        SEQ_IN_INDEX,
        CARDINALITY
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY TABLE_NAME, INDEX_NAME
");
$stmt->execute();
$index_stats = $stmt->get_result();
$stmt->close();

$indexes_data = [];
while ($row = $index_stats->fetch_assoc()) {
    $key = $row['TABLE_NAME'] . '.' . $row['INDEX_NAME'];
    if (!isset($indexes_data[$key])) {
        $indexes_data[$key] = [
            'table' => $row['TABLE_NAME'],
            'index' => $row['INDEX_NAME'],
            'columns' => [],
            'cardinality' => $row['CARDINALITY']
        ];
    }
    $indexes_data[$key]['columns'][] = $row['COLUMN_NAME'];
}

$query_time_2 = microtime(true) - $start_time;

// Get cache statistics
$cache_stats = $cache->get_stats();

// Calculate cache hit rate (simulated based on cache files)
$cache_hit_rate = 0;
if ($cache_stats['total_files'] > 0) {
    $cache_hit_rate = ($cache_stats['valid_files'] / $cache_stats['total_files']) * 100;
}

// Get system resource usage
$memory_usage = memory_get_usage(true);
$memory_peak = memory_get_peak_usage(true);
$memory_limit = ini_get('memory_limit');

// Convert memory values to MB
$memory_usage_mb = round($memory_usage / 1024 / 1024, 2);
$memory_peak_mb = round($memory_peak / 1024 / 1024, 2);

// Get PHP version and database version
$php_version = phpversion();
$db_version = $conn->get_server_info();

// Get slow query log status (if available)
$slow_query_threshold = 0;
$stmt = $conn->prepare("SHOW VARIABLES LIKE 'long_query_time'");
$stmt->execute();
$slow_query_result = $stmt->get_result();
if ($row = $slow_query_result->fetch_assoc()) {
    $slow_query_threshold = $row['Value'];
}
$stmt->close();

// Get current connections
$stmt = $conn->prepare("SHOW STATUS LIKE 'Threads_connected'");
$stmt->execute();
$threads_result = $stmt->get_result();
$threads_connected = 0;
if ($row = $threads_result->fetch_assoc()) {
    $threads_connected = $row['Value'];
}
$stmt->close();

// Get query cache status
$stmt = $conn->prepare("SHOW STATUS LIKE 'Qcache%'");
$stmt->execute();
$qcache_result = $stmt->get_result();
$qcache_stats = [];
while ($row = $qcache_result->fetch_assoc()) {
    $qcache_stats[$row['Variable_name']] = $row['Value'];
}
$stmt->close();

// Calculate query cache hit rate
$qcache_hits = isset($qcache_stats['Qcache_hits']) ? (int)$qcache_stats['Qcache_hits'] : 0;
$qcache_inserts = isset($qcache_stats['Qcache_inserts']) ? (int)$qcache_stats['Qcache_inserts'] : 0;
$qcache_hit_rate = 0;
if (($qcache_hits + $qcache_inserts) > 0) {
    $qcache_hit_rate = ($qcache_hits / ($qcache_hits + $qcache_inserts)) * 100;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Monitor - PHARMACIA</title>
    <style>
        .metric-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 1.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .metric-value {
            font-size: 1.875rem;
            font-weight: 900;
            color: #1e293b;
            margin: 0.5rem 0;
        }
        .metric-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .metric-unit {
            font-size: 0.875rem;
            color: #64748b;
            margin-left: 0.5rem;
        }
        .progress-bar {
            width: 100%;
            height: 0.5rem;
            background: #e2e8f0;
            border-radius: 0.25rem;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            transition: width 0.3s ease;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        th {
            background: #f1f5f9;
            padding: 0.75rem;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:hover {
            background: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-good {
            background: #dcfce7;
            color: #166534;
        }
        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .status-critical {
            background: #fee2e2;
            color: #991b1b;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-slate-50">
    <?php require('sidebar.php'); ?>

    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between space-y-4 md:space-y-0">
        <div>
            <p class="subheading-premium">System Diagnostics</p>
            <h1 class="heading-premium">Performance Monitor</h1>
            <p class="text-slate-500 font-medium mt-1">Real-time database and system metrics</p>
        </div>
        <button class="btn-primary btn-blue" onclick="location.reload()">
            <i class="fas fa-sync-alt mr-2"></i> Refresh Metrics
        </button>
    </div>

    <!-- System Overview -->
    <div class="grid-2">
        <div class="metric-card">
            <p class="metric-label">PHP Version</p>
            <p class="metric-value"><?php echo htmlspecialchars($php_version); ?></p>
        </div>
        <div class="metric-card">
            <p class="metric-label">MySQL Version</p>
            <p class="metric-value"><?php echo htmlspecialchars($db_version); ?></p>
        </div>
        <div class="metric-card">
            <p class="metric-label">Database Size</p>
            <p class="metric-value"><?php echo number_format($total_db_size, 2); ?><span class="metric-unit">MB</span></p>
        </div>
        <div class="metric-card">
            <p class="metric-label">Active Connections</p>
            <p class="metric-value"><?php echo $threads_connected; ?></p>
        </div>
    </div>

    <!-- Memory Usage -->
    <div class="metric-card">
        <p class="metric-label">Memory Usage</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <p class="metric-value"><?php echo $memory_usage_mb; ?><span class="metric-unit">MB</span></p>
                <p class="metric-label">Current Usage</p>
            </div>
            <div>
                <p class="metric-value"><?php echo $memory_peak_mb; ?><span class="metric-unit">MB</span></p>
                <p class="metric-label">Peak Usage</p>
            </div>
            <div>
                <p class="metric-value"><?php echo htmlspecialchars($memory_limit); ?></p>
                <p class="metric-label">Limit</p>
            </div>
        </div>
    </div>

    <!-- Cache Performance -->
    <div class="metric-card">
        <p class="metric-label">File Cache Statistics</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div>
                <p class="metric-value"><?php echo $cache_stats['total_files']; ?></p>
                <p class="metric-label">Total Cache Files</p>
            </div>
            <div>
                <p class="metric-value"><?php echo $cache_stats['valid_files']; ?></p>
                <p class="metric-label">Valid Files</p>
            </div>
            <div>
                <p class="metric-value"><?php echo $cache_stats['expired_files']; ?></p>
                <p class="metric-label">Expired Files</p>
            </div>
            <div>
                <p class="metric-value"><?php echo round($cache_stats['total_size'] / 1024 / 1024, 2); ?><span class="metric-unit">MB</span></p>
                <p class="metric-label">Total Size</p>
            </div>
        </div>
        <div style="margin-top: 1rem;">
            <p class="metric-label">Cache Hit Rate</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $cache_hit_rate; ?>%"></div>
            </div>
            <p style="margin-top: 0.5rem; font-weight: 700; color: #1e293b;"><?php echo round($cache_hit_rate, 2); ?>%</p>
        </div>
    </div>

    <!-- Query Cache Performance -->
    <div class="metric-card">
        <p class="metric-label">MySQL Query Cache</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div>
                <p class="metric-value"><?php echo isset($qcache_stats['Qcache_hits']) ? $qcache_stats['Qcache_hits'] : 0; ?></p>
                <p class="metric-label">Cache Hits</p>
            </div>
            <div>
                <p class="metric-value"><?php echo isset($qcache_stats['Qcache_inserts']) ? $qcache_stats['Qcache_inserts'] : 0; ?></p>
                <p class="metric-label">Cache Inserts</p>
            </div>
            <div>
                <p class="metric-value"><?php echo round($qcache_hit_rate, 2); ?>%</p>
                <p class="metric-label">Hit Rate</p>
            </div>
        </div>
    </div>

    <!-- Query Execution Times -->
    <div class="metric-card">
        <p class="metric-label">Query Execution Times</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div>
                <p class="metric-value"><?php echo round($query_time_1 * 1000, 2); ?><span class="metric-unit">ms</span></p>
                <p class="metric-label">Table Stats Query</p>
            </div>
            <div>
                <p class="metric-value"><?php echo round($query_time_2 * 1000, 2); ?><span class="metric-unit">ms</span></p>
                <p class="metric-label">Index Stats Query</p>
            </div>
            <div>
                <p class="metric-value"><?php echo htmlspecialchars($slow_query_threshold); ?><span class="metric-unit">s</span></p>
                <p class="metric-label">Slow Query Threshold</p>
            </div>
        </div>
    </div>

    <!-- Table Statistics -->
    <div class="metric-card">
        <p class="metric-label">Table Statistics</p>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Rows</th>
                        <th>Size (MB)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables_data as $table): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($table['TABLE_NAME']); ?></strong></td>
                        <td><?php echo number_format($table['TABLE_ROWS']); ?></td>
                        <td><?php echo number_format($table['size_mb'], 2); ?></td>
                        <td>
                            <?php 
                            $status_class = 'status-good';
                            if ($table['size_mb'] > 100) {
                                $status_class = 'status-warning';
                            } elseif ($table['size_mb'] > 500) {
                                $status_class = 'status-critical';
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $table['size_mb'] > 100 ? 'Large' : 'Normal'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Index Statistics -->
    <div class="metric-card">
        <p class="metric-label">Database Indexes</p>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Index Name</th>
                        <th>Columns</th>
                        <th>Cardinality</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($indexes_data, 0, 20) as $index): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($index['table']); ?></strong></td>
                        <td><?php echo htmlspecialchars($index['index']); ?></td>
                        <td><?php echo htmlspecialchars(implode(', ', $index['columns'])); ?></td>
                        <td><?php echo $index['cardinality'] !== null ? number_format($index['cardinality']) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($indexes_data) > 20): ?>
        <p style="margin-top: 1rem; color: #64748b; font-size: 0.875rem;">
            Showing 20 of <?php echo count($indexes_data); ?> indexes
        </p>
        <?php endif; ?>
    </div>

    <!-- Cache Management -->
    <div class="metric-card">
        <p class="metric-label">Cache Management</p>
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button class="btn-primary btn-blue" onclick="clearExpiredCache()">
                <i class="fas fa-trash mr-2"></i> Clear Expired Cache
            </button>
            <button class="btn-primary" style="background: #ef4444; border-color: #dc2626;" onclick="clearAllCache()">
                <i class="fas fa-broom mr-2"></i> Clear All Cache
            </button>
        </div>
    </div>

    </main>
    </div>
    </div>

    <script>
        function clearExpiredCache() {
            if (confirm('Clear expired cache entries?')) {
                fetch('performance_monitor.php?action=clear_expired', {
                    method: 'POST'
                }).then(response => {
                    if (response.ok) {
                        alert('Expired cache cleared successfully');
                        location.reload();
                    }
                });
            }
        }

        function clearAllCache() {
            if (confirm('Clear ALL cache? This may impact performance temporarily.')) {
                fetch('performance_monitor.php?action=clear_all', {
                    method: 'POST'
                }).then(response => {
                    if (response.ok) {
                        alert('All cache cleared successfully');
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>

<?php
// Handle cache clearing actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'clear_expired') {
        $count = $cache->clear_expired();
        http_response_code(200);
        exit("Cleared $count expired cache files");
    } elseif ($action === 'clear_all') {
        $count = $cache->clear_all();
        http_response_code(200);
        exit("Cleared $count cache files");
    }
}
?>
