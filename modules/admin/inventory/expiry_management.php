<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get expiry data with FIFO/FEFO implementation
$expiry_data = $conn->query("
    SELECT 
        m.Med_ID,
        m.Med_Name,
        p.P_ID as Batch_ID,
        p.P_ID as Batch_Number,
        p.P_Qty as Batch_Qty,
        p.Exp_Date,
        p.Mfg_Date,
        DATEDIFF(p.Exp_Date, CURDATE()) as days_to_expiry,
        CASE 
            WHEN p.Exp_Date < CURDATE() THEN 'Expired'
            WHEN DATEDIFF(p.Exp_Date, CURDATE()) <= 30 THEN 'Expiring Soon'
            ELSE 'Valid'
        END as expiry_status,
        s.Sup_Name
    FROM meds m
    JOIN purchase p ON m.Med_ID = p.Med_ID
    LEFT JOIN suppliers s ON p.Sup_ID = s.Sup_ID
    ORDER BY p.Exp_Date ASC
");

// Get summary statistics
$expired_count = $conn->query("SELECT COUNT(*) as count FROM purchase WHERE Exp_Date < CURDATE()")->fetch_assoc()['count'] ?? 0;
$expiring_soon = $conn->query("SELECT COUNT(*) as count FROM purchase WHERE Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND Exp_Date > CURDATE()")->fetch_assoc()['count'] ?? 0;
$valid_count = $conn->query("SELECT COUNT(*) as count FROM purchase WHERE Exp_Date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiry Management - PHARMACIA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .page-header h1 {
            font-size: 32px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background-color: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-label {
            font-size: 10px;
            font-weight: 900;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 900;
            color: #1e293b;
        }
        
        .stat-value.emerald {
            color: #10b981;
        }
        
        .stat-value.amber {
            color: #fb923c;
        }
        
        .stat-value.rose {
            color: #f43f5e;
        }
        
        .stat-value.blue {
            color: #3b82f6;
        }
        
        .stat-icon {
            font-size: 32px;
            opacity: 0.2;
        }
        
        .card {
            background-color: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 32px;
        }
        
        .card-header {
            padding: 24px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-header h2 {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .card-header i {
            font-size: 18px;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8fafc;
        }
        
        th {
            padding: 16px 24px;
            text-align: left;
            font-size: 10px;
            font-weight: 900;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #475569;
        }
        
        tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid;
        }
        
        .status-valid {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.2);
        }
        
        .status-expiring {
            background-color: rgba(251, 146, 60, 0.1);
            color: #fb923c;
            border-color: rgba(251, 146, 60, 0.2);
        }
        
        .status-expired {
            background-color: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
            border-color: rgba(244, 63, 94, 0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: #cbd5e1;
        }
        
        .empty-state i {
            font-size: 48px;
            opacity: 0.1;
            margin-bottom: 16px;
            display: block;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-top: 32px;
        }
        
        .info-card {
            background-color: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }
        
        .info-card h3 {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-card p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        
        .info-card p:last-child {
            margin-bottom: 0;
        }
        
        .info-card strong {
            color: #1e293b;
        }
        
        .check-icon {
            color: #10b981;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php require('../sidebar.php'); ?>
        
        <div class="content">
            <header class="header">
                <div>
                    <h2 style="font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px 0;">Application</h2>
                    <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; letter-spacing: -0.5px; margin: 0; line-height: 1;">Expiry Management</h1>
                </div>
            </header>
            
            <main class="main">
                <!-- Page Header -->
                <div class="page-header">
                    <h2>Inventory Control</h2>
                    <h1>Expiry Management & FIFO</h1>
                    <p>Track medicine expiry dates and ensure FIFO/FEFO compliance</p>
                </div>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div>
                                <div class="stat-label">Valid Stock</div>
                                <div class="stat-value emerald"><?php echo $valid_count; ?></div>
                            </div>
                            <i class="fas fa-check-circle stat-icon" style="color: #10b981;"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div>
                                <div class="stat-label">Expiring Soon</div>
                                <div class="stat-value amber"><?php echo $expiring_soon; ?></div>
                            </div>
                            <i class="fas fa-exclamation-triangle stat-icon" style="color: #fb923c;"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div>
                                <div class="stat-label">Expired</div>
                                <div class="stat-value rose"><?php echo $expired_count; ?></div>
                            </div>
                            <i class="fas fa-times-circle stat-icon" style="color: #f43f5e;"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div>
                                <div class="stat-label">Total Batches</div>
                                <div class="stat-value blue"><?php echo $valid_count + $expiring_soon + $expired_count; ?></div>
                            </div>
                            <i class="fas fa-boxes-stacked stat-icon" style="color: #3b82f6;"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Expiry Details Table -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-times" style="color: #f43f5e;"></i>
                        <h2>Batch Expiry Status</h2>
                    </div>
                    
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Batch #</th>
                                    <th>Qty</th>
                                    <th>Mfg Date</th>
                                    <th>Exp Date</th>
                                    <th>Days Left</th>
                                    <th>Status</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($expiry_data && $expiry_data->num_rows > 0): ?>
                                    <?php while($row = $expiry_data->fetch_assoc()): ?>
                                        <?php 
                                            $status_class = 'status-valid';
                                            if ($row['expiry_status'] === 'Expired') {
                                                $status_class = 'status-expired';
                                            } elseif ($row['expiry_status'] === 'Expiring Soon') {
                                                $status_class = 'status-expiring';
                                            }
                                            
                                            $days_color = '#10b981';
                                            if ($row['days_to_expiry'] < 0) {
                                                $days_color = '#f43f5e';
                                            } elseif ($row['days_to_expiry'] <= 30) {
                                                $days_color = '#fb923c';
                                            }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['Med_Name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['Batch_Number']); ?></td>
                                            <td><?php echo $row['Batch_Qty']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['Mfg_Date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['Exp_Date'])); ?></td>
                                            <td><strong style="color: <?php echo $days_color; ?>"><?php echo $row['days_to_expiry']; ?> days</strong></td>
                                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $row['expiry_status']; ?></span></td>
                                            <td><?php echo htmlspecialchars($row['Sup_Name'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar"></i>
                                                <p>No batches found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- FIFO/FEFO Information -->
                <div class="info-grid">
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                            FIFO/FEFO Implementation
                        </h3>
                        <p><strong>FIFO (First In, First Out):</strong> Oldest batches are sold first based on manufacturing date.</p>
                        <p><strong>FEFO (First Expired, First Out):</strong> Batches closest to expiry are prioritized for sale.</p>
                        <p><strong>Compliance Check:</strong> System automatically verifies FIFO/FEFO order to prevent expired medicines from being sold.</p>
                        <p><strong>POS Integration:</strong> Point of Sale system highlights near-expiry stock for priority sales.</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>
                            <i class="fas fa-shield-alt" style="color: #10b981;"></i>
                            Expiry Prevention Features
                        </h3>
                        <p><i class="fas fa-check check-icon"></i> Automatic expiry status tracking</p>
                        <p><i class="fas fa-check check-icon"></i> Prevents selling expired medicines</p>
                        <p><i class="fas fa-check check-icon"></i> 30-day expiry alerts</p>
                        <p><i class="fas fa-check check-icon"></i> Batch-level expiry management</p>
                        <p><i class="fas fa-check check-icon"></i> Automated archival of expired stock</p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
