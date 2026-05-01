<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// 1. Total Medicines and Low Stock (from meds table)
$meds_stats = $conn->query("
    SELECT 
        COUNT(*) as total_medicines,
        SUM(CASE WHEN Med_Qty <= 10 THEN 1 ELSE 0 END) as low_stock_count
    FROM meds
")->fetch_assoc();

// 2. Expired Count (from purchase table, where individual batches are tracked)
$expired_stats = $conn->query("
    SELECT COUNT(DISTINCT Med_ID) as expired_count 
    FROM purchase 
    WHERE Exp_Date <= CURDATE()
")->fetch_assoc();

$inventory_status = [
    'total_medicines' => $meds_stats['total_medicines'] ?? 0,
    'low_stock_count' => $meds_stats['low_stock_count'] ?? 0,
    'expired_count' => $expired_stats['expired_count'] ?? 0
];

// 3. Expiry Alerts (Join meds and purchase)
$expiry_alerts = $conn->query("
    SELECT m.Med_Name, p.Exp_Date, m.Med_Qty, m.Location_Rack
    FROM meds m
    JOIN purchase p ON m.Med_ID = p.Med_ID
    WHERE p.Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    GROUP BY m.Med_ID, m.Med_Name, p.Exp_Date, m.Med_Qty, m.Location_Rack
    ORDER BY p.Exp_Date ASC
    LIMIT 5
");

// 4. Low Stock Alerts
$low_stock_alerts = $conn->query("
    SELECT Med_Name, Med_Qty, Location_Rack
    FROM meds
    WHERE Med_Qty <= 10
    ORDER BY Med_Qty ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard - PHARMACIA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .content {
            flex: 1;
            margin-left: 288px;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            height: 80px;
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        
        .main {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h2 {
            font-size: 10px;
            font-weight: 900;
            color: #e11d48;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .page-header h1 {
            font-size: 36px;
            font-weight: 900;
            color: #1e293b;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }
        
        .page-header p {
            color: #64748b;
            font-weight: 500;
            font-size: 14px;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary {
            background-color: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }
        
        .btn-secondary:hover {
            background-color: #f1f5f9;
        }
        
        .btn-primary {
            background-color: #1e293b;
            color: white;
            box-shadow: 0 20px 25px -5px rgba(30, 41, 59, 0.2);
        }
        
        .btn-primary:hover {
            background-color: #0f172a;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-bottom: 48px;
        }
        
        .metric-card {
            background-color: white;
            padding: 32px;
            border-radius: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
        }
        
        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 20px;
        }
        
        .metric-icon.emerald {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .metric-icon.rose {
            background-color: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
        }
        
        .metric-icon.amber {
            background-color: rgba(251, 146, 60, 0.1);
            color: #fb923c;
        }
        
        .metric-icon.indigo {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }
        
        .metric-label {
            font-size: 10px;
            font-weight: 900;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .metric-value {
            font-size: 32px;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
        }
        
        .metric-value.rose {
            color: #f43f5e;
        }
        
        .metric-value.amber {
            color: #fb923c;
        }
        
        .metric-value.indigo {
            color: #6366f1;
        }
        
        .alerts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
            margin-bottom: 48px;
        }
        
        .alert-card {
            background-color: white;
            padding: 40px;
            border-radius: 48px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
        }
        
        .alert-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        
        .alert-title {
            font-size: 18px;
            font-weight: 900;
            color: #1e293b;
            text-transform: uppercase;
            font-style: italic;
        }
        
        .alert-badge {
            padding: 6px 12px;
            background-color: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
            font-size: 9px;
            font-weight: 900;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .alert-badge.amber {
            background-color: rgba(251, 146, 60, 0.1);
            color: #fb923c;
        }
        
        .alert-items {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .alert-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background-color: rgba(248, 250, 252, 0.5);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .alert-item:hover {
            border-color: rgba(244, 63, 94, 0.2);
        }
        
        .alert-item-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .alert-indicator {
            width: 6px;
            height: 32px;
            background-color: #f43f5e;
            border-radius: 9999px;
        }
        
        .alert-item-info {
            display: flex;
            flex-direction: column;
        }
        
        .alert-item-name {
            font-size: 14px;
            font-weight: 900;
            color: #1e293b;
        }
        
        .alert-item-location {
            font-size: 10px;
            font-weight: 700;
            color: #cbd5e1;
            text-transform: uppercase;
        }
        
        .alert-item-value {
            font-size: 14px;
            font-weight: 900;
            color: #f43f5e;
        }
        
        .alert-item-value.amber {
            color: #fb923c;
        }
        
        .empty-state {
            color: #cbd5e1;
            font-weight: 700;
            font-style: italic;
            padding: 40px 0;
            text-align: center;
        }
        
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 48px;
        }
        
        .nav-card {
            background-color: white;
            padding: 32px;
            border-radius: 40px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .nav-card:hover {
            transform: translateY(-8px);
        }
        
        .nav-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        
        .nav-card:hover .nav-icon {
            transform: scale(1.1);
        }
        
        .nav-icon.blue {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .nav-icon.purple {
            background-color: rgba(147, 51, 234, 0.1);
            color: #9333ea;
        }
        
        .nav-icon.emerald {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .nav-title {
            font-size: 18px;
            font-weight: 900;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }
        
        .nav-desc {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php require('../sidebar.php'); ?>
        
        <div class="content">
            <header class="header">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div>
                        <h2 style="font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px 0;">Application</h2>
                        <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; letter-spacing: -0.5px; margin: 0; line-height: 1;">Reports Dashboard</h1>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary">
                        <i class="fas fa-file-pdf" style="color: #e11d48;"></i> Export PDF
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-file-excel" style="color: #10b981;"></i> Export Matrix
                    </button>
                </div>
            </header>
            
            <main class="main">
                <!-- Page Header -->
                <div class="page-header">
                    <h2>Visual Analysis</h2>
                    <h1>Reports Dashboard</h1>
                    <p>Cross-sectional business intelligence and performance matrix.</p>
                </div>
                
                <!-- Core Metrics -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon emerald">
                            <i class="fas fa-sack-dollar"></i>
                        </div>
                        <div class="metric-label">Total Assets Vol.</div>
                        <div class="metric-value"><?php echo $inventory_status['total_medicines']; ?></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon rose">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="metric-label">Critical Stock</div>
                        <div class="metric-value rose"><?php echo $inventory_status['low_stock_count']; ?></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon amber">
                            <i class="fas fa-calendar-xmark"></i>
                        </div>
                        <div class="metric-label">Expired Batch</div>
                        <div class="metric-value amber"><?php echo $inventory_status['expired_count']; ?></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon indigo">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="metric-label">Avg Efficiency</div>
                        <div class="metric-value indigo">94.2%</div>
                    </div>
                </div>
                
                <!-- Alert Console -->
                <div class="alerts-grid">
                    <div class="alert-card">
                        <div class="alert-header">
                            <h3 class="alert-title">Stock Depletion Alerts</h3>
                            <span class="alert-badge">High Priority</span>
                        </div>
                        <div class="alert-items">
                            <?php if ($low_stock_alerts && $low_stock_alerts->num_rows > 0): ?>
                                <?php while($item = $low_stock_alerts->fetch_assoc()): ?>
                                    <div class="alert-item">
                                        <div class="alert-item-left">
                                            <div class="alert-indicator"></div>
                                            <div class="alert-item-info">
                                                <div class="alert-item-name"><?php echo htmlspecialchars($item['Med_Name']); ?></div>
                                                <div class="alert-item-location"><?php echo htmlspecialchars($item['Location_Rack']); ?></div>
                                            </div>
                                        </div>
                                        <div class="alert-item-value"><?php echo $item['Med_Qty']; ?> Left</div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">No critical stock levels detected.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="alert-card">
                        <div class="alert-header">
                            <h3 class="alert-title">Expiry Watchdog</h3>
                            <span class="alert-badge amber">Schedule Log</span>
                        </div>
                        <div class="alert-items">
                            <?php if ($expiry_alerts && $expiry_alerts->num_rows > 0): ?>
                                <?php while($item = $expiry_alerts->fetch_assoc()): ?>
                                    <div class="alert-item">
                                        <div class="alert-item-left">
                                            <div class="alert-indicator" style="background-color: #fb923c;"></div>
                                            <div class="alert-item-info">
                                                <div class="alert-item-name"><?php echo htmlspecialchars($item['Med_Name']); ?></div>
                                                <div class="alert-item-location"><?php echo date('M Y', strtotime($item['Exp_Date'])); ?> Status</div>
                                            </div>
                                        </div>
                                        <div class="alert-item-value amber">Approaching</div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">No expiry risks synchronized.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Navigation Matrix -->
                <div class="nav-grid">
                    <a href="sales_report.php" class="nav-card">
                        <div class="nav-icon blue">
                            <i class="fas fa-chart-simple"></i>
                        </div>
                        <h4 class="nav-title">Sales Analytics</h4>
                        <p class="nav-desc">Deep dive into transactional high-frequency data logs.</p>
                    </a>
                    
                    <a href="stock_report.php" class="nav-card">
                        <div class="nav-icon purple">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                        <h4 class="nav-title">Stock Fidelity</h4>
                        <p class="nav-desc">Verify asset counts and storage location precision matrix.</p>
                    </a>
                    
                    <a href="../employees/view_new.php" class="nav-card">
                        <div class="nav-icon emerald">
                            <i class="fas fa-user-gear"></i>
                        </div>
                        <h4 class="nav-title">Force Dynamics</h4>
                        <p class="nav-desc">Analyze personnel contribution and operational efficiency ratio.</p>
                    </a>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
