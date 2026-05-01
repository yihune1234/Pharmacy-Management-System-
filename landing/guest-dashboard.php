<?php
session_start();

// Check if guest is logged in
if (!isset($_SESSION['guest_mode']) || !$_SESSION['guest_mode']) {
    header("Location: register.php");
    exit();
}

// Handle role switching
if (isset($_GET['role']) && in_array($_GET['role'], $_SESSION['guest_roles'])) {
    $_SESSION['current_role'] = $_GET['role'];
}

$current_role = $_SESSION['current_role'] ?? 'admin';
$guest_name = $_SESSION['guest_name'] ?? 'Guest User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Dashboard - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 900;
            color: #667eea;
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-badge {
            background: #e3f2fd;
            color: #667eea;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .logout-btn {
            background: #667eea;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #764ba2;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .role-switcher {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .role-switcher h3 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .role-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .role-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .role-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .role-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 900;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .content-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .feature-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .feature-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }

        .feature-name {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .demo-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .demo-banner-text h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .demo-banner-text p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .demo-banner-actions {
            display: flex;
            gap: 10px;
        }

        .demo-banner-actions a {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .demo-banner-actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 15px;
            }

            .user-info {
                flex-direction: column;
                width: 100%;
            }

            .role-buttons {
                flex-direction: column;
            }

            .role-btn {
                width: 100%;
                text-align: center;
            }

            .demo-banner {
                flex-direction: column;
                gap: 15px;
            }

            .demo-banner-actions {
                width: 100%;
                flex-direction: column;
            }

            .demo-banner-actions a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="logo">
                <i class="fas fa-pills"></i> PHARMACIA
            </a>
            <div class="user-info">
                <div class="user-badge">
                    <i class="fas fa-star"></i> Demo Mode - <?php echo ucfirst($current_role); ?>
                </div>
                <span style="color: #666; font-weight: 600;">Welcome, <?php echo htmlspecialchars($guest_name); ?></span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Exit Demo
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Demo Banner -->
        <div class="demo-banner">
            <div class="demo-banner-text">
                <h2>Welcome to PHARMACIA Demo</h2>
                <p>Explore all features with full access to Admin, Pharmacist, and Cashier roles</p>
            </div>
            <div class="demo-banner-actions">
                <a href="index.php">Back to Home</a>
                <a href="login.php">Login with Account</a>
            </div>
        </div>

        <!-- Role Switcher -->
        <div class="role-switcher">
            <h3><i class="fas fa-user-tie"></i> Switch Role</h3>
            <div class="role-buttons">
                <a href="?role=admin" class="role-btn <?php echo $current_role === 'admin' ? 'active' : ''; ?>">
                    <i class="fas fa-crown"></i> Admin
                </a>
                <a href="?role=pharmacist" class="role-btn <?php echo $current_role === 'pharmacist' ? 'active' : ''; ?>">
                    <i class="fas fa-user-md"></i> Pharmacist
                </a>
                <a href="?role=cashier" class="role-btn <?php echo $current_role === 'cashier' ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register"></i> Cashier
                </a>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-pills"></i></div>
                <div class="stat-number">1,250</div>
                <div class="stat-label">Medicines in Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-number">342</div>
                <div class="stat-label">Sales Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number">5,680</div>
                <div class="stat-label">Total Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-number">Rs. 45,230</div>
                <div class="stat-label">Today's Revenue</div>
            </div>
        </div>

        <!-- Admin Features -->
        <?php if ($current_role === 'admin'): ?>
        <div class="content-section">
            <h2 class="section-title"><i class="fas fa-crown"></i> Admin Features</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-boxes"></i></div>
                    <div class="feature-name">Inventory Management</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <div class="feature-name">Employee Management</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-truck"></i></div>
                    <div class="feature-name">Supplier Management</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="feature-name">Advanced Reports</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <div class="feature-name">Security Settings</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-cog"></i></div>
                    <div class="feature-name">System Configuration</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <div class="feature-name">Alert Management</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-history"></i></div>
                    <div class="feature-name">Audit Logs</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pharmacist Features -->
        <?php if ($current_role === 'pharmacist'): ?>
        <div class="content-section">
            <h2 class="section-title"><i class="fas fa-user-md"></i> Pharmacist Features</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-cash-register"></i></div>
                    <div class="feature-name">POS System</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-flask"></i></div>
                    <div class="feature-name">Drug Interactions</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-file-medical"></i></div>
                    <div class="feature-name">Prescriptions</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-user-injured"></i></div>
                    <div class="feature-name">Customer Profiles</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-search"></i></div>
                    <div class="feature-name">Inventory Search</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-undo"></i></div>
                    <div class="feature-name">Refund Processing</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-receipt"></i></div>
                    <div class="feature-name">Receipt Generation</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-star"></i></div>
                    <div class="feature-name">Loyalty Points</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cashier Features -->
        <?php if ($current_role === 'cashier'): ?>
        <div class="content-section">
            <h2 class="section-title"><i class="fas fa-cash-register"></i> Cashier Features</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="feature-name">Process Sales</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="feature-name">Payment Methods</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-receipt"></i></div>
                    <div class="feature-name">Generate Receipts</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="feature-name">Register Customers</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-search"></i></div>
                    <div class="feature-name">Customer Lookup</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-undo"></i></div>
                    <div class="feature-name">Process Refunds</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-history"></i></div>
                    <div class="feature-name">Transaction History</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="feature-name">Daily Summary</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Common Features -->
        <div class="content-section">
            <h2 class="section-title"><i class="fas fa-star"></i> Available in All Roles</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <div class="feature-name">2FA Security</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <div class="feature-name">High Performance</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-database"></i></div>
                    <div class="feature-name">Data Encryption</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <div class="feature-name">Smart Alerts</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="feature-name">Analytics</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-download"></i></div>
                    <div class="feature-name">Export Reports</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <div class="feature-name">Responsive Design</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <div class="feature-name">24/7 Support</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
