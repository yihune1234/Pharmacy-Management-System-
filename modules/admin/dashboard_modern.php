<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get dashboard statistics
$total_meds = $conn->query("SELECT COUNT(*) as count FROM meds")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM view_low_stock")->fetch_assoc()['count'];
$today_sales = $conn->query("SELECT COUNT(*) as count FROM sales WHERE S_Date = CURDATE()")->fetch_assoc()['count'];
$today_revenue = $conn->query("SELECT COALESCE(SUM(Total_Amt), 0) as total FROM sales WHERE S_Date = CURDATE()")->fetch_assoc()['total'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'];
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'];
$total_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'];

// Recent sales
$recent_sales = $conn->query("SELECT s.Sale_ID, s.S_Date, s.Total_Amt, c.C_Fname, c.C_Lname, e.E_Fname as emp_name 
                             FROM sales s 
                             LEFT JOIN customer c ON s.C_ID = c.C_ID 
                             LEFT JOIN employee e ON s.E_ID = e.E_ID 
                             ORDER BY s.Sale_ID DESC LIMIT 10");

// Low stock items
$low_stock_items = $conn->query("SELECT Med_Name, Med_Qty, Location_Rack FROM view_low_stock ORDER BY Med_Qty ASC LIMIT 8");

// Top selling medicines
$top_meds = $conn->query("SELECT m.Med_Name, SUM(si.Sale_Qty) as total_sold, SUM(si.Tot_Price) as revenue 
                         FROM meds m 
                         JOIN sales_items si ON m.Med_ID = si.Med_ID 
                         JOIN sales s ON si.Sale_ID = s.Sale_ID 
                         WHERE s.S_Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                         GROUP BY m.Med_ID, m.Med_Name 
                         ORDER BY total_sold DESC LIMIT 5");

// Monthly sales trend
$monthly_sales = $conn->query("SELECT DATE_FORMAT(S_Date, '%Y-%m') as month, COUNT(*) as sales_count, SUM(Total_Amt) as revenue 
                              FROM sales 
                              WHERE S_Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                              GROUP BY DATE_FORMAT(S_Date, '%Y-%m') 
                              ORDER BY month DESC LIMIT 12");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PHARMACIA</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Styles -->
    <style>
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Main content scrollbar */
        .main-content::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .main-content::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        
        .main-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .main-content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Card hover effects */
        .dashboard-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Menu item hover */
        .menu-item {
            transition: all 0.2s ease;
        }
        
        .menu-item:hover {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 3px solid #0ea5e9;
        }
        
        .menu-item.active {
            background: linear-gradient(90deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 3px solid #3b82f6;
        }
        
        /* Table scroll */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Smooth transitions */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        
        /* Notification badge animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .notification-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Sticky Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Left Section: Logo and Menu Toggle -->
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-gray-100 lg:hidden">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-plus text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">PHARMACIA</h1>
                        <p class="text-xs text-gray-500">Pharmacy Management System</p>
                    </div>
                </div>
            </div>
            
            <!-- Center Section: Search Bar -->
            <div class="hidden md:flex flex-1 max-w-xl mx-8">
                <div class="relative w-full">
                    <input type="text" 
                           placeholder="Search medicines, customers, suppliers..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Right Section: User Actions -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-gray-600"></i>
                    <?php if ($low_stock > 0): ?>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full notification-pulse"></span>
                    <?php endif; ?>
                </button>
                
                <!-- User Profile -->
                <div class="flex items-center space-x-3 pl-4 border-l border-gray-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin User'); ?></p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        <?php echo strtoupper(substr($_SESSION['name'] ?? 'A', 0)); ?>
                    </div>
                    <button onclick="location.href='../auth/logout.php'" class="p-2 rounded-lg hover:bg-red-50 text-gray-600 hover:text-red-600 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <div class="flex h-screen pt-16">
        <!-- Scrollable Sidebar -->
        <aside id="sidebar" class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40">
            <nav class="sidebar-scroll h-full overflow-y-auto py-6">
                <div class="px-4 space-y-2">
                    <!-- Main Navigation -->
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Main Menu</h3>
                        <div class="space-y-1">
                            <a href="dashboard.php" class="menu-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-tachometer-alt w-5 text-blue-600"></i>
                                <span class="font-medium">Dashboard</span>
                            </a>
                            <a href="../sales/pos1.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-cash-register w-5 text-green-600"></i>
                                <span class="font-medium">Point of Sale</span>
                            </a>
                            <a href="inventory/view.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-pills w-5 text-purple-600"></i>
                                <span class="font-medium">Inventory</span>
                                <?php if ($low_stock > 0): ?>
                                    <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full"><?php echo $low_stock; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Management -->
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Management</h3>
                        <div class="space-y-1">
                            <a href="customers/view.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-users w-5 text-blue-600"></i>
                                <span class="font-medium">Customers</span>
                                <span class="ml-auto text-xs text-gray-500"><?php echo $total_customers; ?></span>
                            </a>
                            <a href="employees/view_new.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-user-tie w-5 text-orange-600"></i>
                                <span class="font-medium">Employees</span>
                                <span class="ml-auto text-xs text-gray-500"><?php echo $total_employees; ?></span>
                            </a>
                            <a href="suppliers/view.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-truck w-5 text-teal-600"></i>
                                <span class="font-medium">Suppliers</span>
                                <span class="ml-auto text-xs text-gray-500"><?php echo $total_suppliers; ?></span>
                            </a>
                            <a href="purchases/view.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-shopping-cart w-5 text-indigo-600"></i>
                                <span class="font-medium">Purchases</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Reports -->
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Reports & Analytics</h3>
                        <div class="space-y-1">
                            <a href="reports/reports_dashboard.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-chart-bar w-5 text-pink-600"></i>
                                <span class="font-medium">Reports Dashboard</span>
                            </a>
                            <a href="alerts/alerts.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-bell w-5 text-red-600"></i>
                                <span class="font-medium">Alerts</span>
                                <?php if ($low_stock > 0): ?>
                                    <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full notification-pulse"><?php echo $low_stock; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- System -->
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">System</h3>
                        <div class="space-y-1">
                            <a href="employees/change_password.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-key w-5 text-gray-600"></i>
                                <span class="font-medium">Change Password</span>
                            </a>
                            <a href="../auth/logout.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span class="font-medium">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="flex-1 lg:ml-64 main-content overflow-y-auto">
            <div class="p-6 max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Overview</h2>
                    <p class="text-gray-600">Welcome back! Here's what's happening in your pharmacy today.</p>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Today's Sales</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2"><?php echo $today_sales; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Transactions</p>
                        </div>
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Today's Revenue</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">₹<?php echo number_format($today_revenue, 0); ?></p>
                            <p class="text-xs text-gray-500 mt-1">Total sales</p>
                        </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-rupee-sign text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Total Medicines</p>
                            <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2"><?php echo $total_meds; ?></p>
                            <p class="text-xs text-gray-500 mt-1">In inventory</p>
                        </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-pills text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Low Stock Alert</p>
                            <p class="text-2xl sm:text-3xl font-bold text-red-600 mt-1 sm:mt-2"><?php echo $low_stock; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Items need restock</p>
                        </div>
                            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Tables Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Top Medicines -->
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Selling Medicines</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Paracetamol 500mg</p>
                                    <p class="text-xs text-gray-500">245 units sold</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">₹6,125</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Amoxicillin 500mg</p>
                                    <p class="text-xs text-gray-500">189 units sold</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">₹8,647</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Ibuprofen 400mg</p>
                                    <p class="text-xs text-gray-500">156 units sold</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">₹2,846</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="location.href='../sales/pos1.php'" class="p-3 bg-green-50 hover:bg-green-100 rounded-lg text-center transition-colors">
                                <i class="fas fa-cash-register text-green-600 text-xl mb-2"></i>
                                <p class="text-xs font-medium text-gray-700">New Sale</p>
                            </button>
                            <button onclick="location.href='inventory/add.php'" class="p-3 bg-blue-50 hover:bg-blue-100 rounded-lg text-center transition-colors">
                                <i class="fas fa-plus-circle text-blue-600 text-xl mb-2"></i>
                                <p class="text-xs font-medium text-gray-700">Add Medicine</p>
                            </button>
                            <button onclick="location.href='customers/add.php'" class="p-3 bg-purple-50 hover:bg-purple-100 rounded-lg text-center transition-colors">
                                <i class="fas fa-user-plus text-purple-600 text-xl mb-2"></i>
                                <p class="text-xs font-medium text-gray-700">Add Customer</p>
                            </button>
                            <button onclick="location.href='purchases/add.php'" class="p-3 bg-orange-50 hover:bg-orange-100 rounded-lg text-center transition-colors">
                                <i class="fas fa-shopping-cart text-orange-600 text-xl mb-2"></i>
                                <p class="text-xs font-medium text-gray-700">New Purchase</p>
                            </button>
                            <button onclick="location.href='reports/reports_dashboard.php'" class="p-3 bg-pink-50 hover:bg-pink-100 rounded-lg text-center transition-colors">
                                <i class="fas fa-chart-bar text-pink-600 text-xl mb-2"></i>
                                <p class="text-xs font-medium text-gray-700">Reports</p>
                            </button>
                            <button onclick="location.href='alerts/alerts.php'" class="p-3 bg-red-50 hover:bg-red-100 rounded-lg text-center transition-colors">
                                <i class="fas fa-bell text-red-600 text-xl mb-2"></i>
                                <p class="text-xs font-medium text-gray-700">Alerts</p>
                            </button>
                        </div>
                    </div>
                    
                    <!-- System Status -->
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">System Status</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-700">Database</span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Online</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-700">Server</span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Normal</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-700">Storage</span>
                                </div>
                                <span class="text-xs text-yellow-600 font-medium">78%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-700">Backup</span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Updated</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Sales Table -->
                <div class="dashboard-card bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Sales</h3>
                            <button onclick="location.href='sales/view.php'" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                View All →
                            </button>
                        </div>
                    </div>
                    <div class="table-container overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale ID</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Employee</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1024</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">Jan 29</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="truncate block max-w-24 sm:max-w-full">John Smith</span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">Sarah Johnson</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹1,250</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1023</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">Jan 29</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="truncate block max-w-24 sm:max-w-full">Mary Davis</span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">Michael Brown</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹890</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1022</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">Jan 29</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="truncate block max-w-24 sm:max-w-full">Walk-in</span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">Emily Davis</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹2,450</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1021</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">Jan 28</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="truncate block max-w-24 sm:max-w-full">Robert Wilson</span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">Sarah Johnson</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹3,180</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1020</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">Jan 28</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="truncate block max-w-24 sm:max-w-full">Lisa Anderson</span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">Michael Brown</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹960</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Chart instance variable
        let salesChart = null;
        
        // Sidebar Toggle for Mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth < 1024 && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.add('-translate-x-full');
            }
        });
        
        // Chart data for different time ranges
        const chartData = {
            12: {
                labels: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                data: [120000, 135000, 125000, 145000, 160000, 155000, 170000, 165000, 180000, 175000, 190000, 185000]
            },
            6: {
                labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                data: [170000, 165000, 180000, 175000, 190000, 185000]
            },
            3: {
                labels: ['Nov', 'Dec', 'Jan'],
                data: [175000, 190000, 185000]
            },
            1: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                data: [45000, 48000, 52000, 40280]
            }
        };
        
        // Initialize chart
        function initChart(type = 'line', timeRange = 12) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (salesChart) {
                salesChart.destroy();
            }
            
            const data = chartData[timeRange];
            
            const config = {
                type: type,
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: data.data,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: type === 'line' ? 'rgba(59, 130, 246, 0.1)' : [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(251, 146, 60, 0.8)',
                            'rgba(147, 51, 234, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(107, 114, 128, 0.8)',
                            'rgba(14, 165, 233, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(251, 191, 36, 0.8)'
                        ],
                        tension: 0.4,
                        fill: type === 'line',
                        borderWidth: type === 'line' ? 2 : 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: type === 'doughnut'
                        }
                    },
                    scales: type === 'doughnut' ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + (value >= 1000 ? (value/1000) + 'k' : value);
                                }
                            }
                        }
                    }
                }
            };
            
            salesChart = new Chart(ctx, config);
        }
        
        // Change chart type
        function changeChartType(type) {
            // Update button styles
            document.querySelectorAll('.chart-type-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('border-gray-200');
            });
            
            const activeBtn = document.querySelector(`[data-type="${type}"]`);
            activeBtn.classList.remove('border-gray-200');
            activeBtn.classList.add('bg-blue-500', 'text-white');
            
            // Get current time range
            const timeRange = parseInt(document.querySelector('select').value) || 12;
            initChart(type, timeRange);
        }
        
        // Change time range
        function changeTimeRange(range) {
            const activeBtn = document.querySelector('.chart-type-btn.bg-blue-500');
            const type = activeBtn ? activeBtn.dataset.type : 'line';
            initChart(type, parseInt(range));
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            initChart('line', 12);
            
            // Set initial active button
            document.querySelector('[data-type="line"]').classList.add('bg-blue-500', 'text-white');
            document.querySelector('[data-type="line"]').classList.remove('border-gray-200');
        });
        
        // Active menu highlighting
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (salesChart) {
                salesChart.resize();
            }
        });
    </script>
    
    <?php render_flash_message(); ?>
</body>
</html>
