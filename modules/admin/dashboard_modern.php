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
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full notification-pulse"></span>
                </button>
                
                <!-- User Profile -->
                <div class="flex items-center space-x-3 pl-4 border-l border-gray-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-900">Admin User</p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        A
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
                                <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full">3</span>
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
                                <span class="ml-auto text-xs text-gray-500">156</span>
                            </a>
                            <a href="employees/view_new.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-user-tie w-5 text-orange-600"></i>
                                <span class="font-medium">Employees</span>
                                <span class="ml-auto text-xs text-gray-500">12</span>
                            </a>
                            <a href="suppliers/view.php" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700">
                                <i class="fas fa-truck w-5 text-teal-600"></i>
                                <span class="font-medium">Suppliers</span>
                                <span class="ml-auto text-xs text-gray-500">8</span>
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
                                <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full notification-pulse">3</span>
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
                            <div>
                                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">24</p>
                                <p class="text-xs text-gray-500 mt-1">Transactions</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">₹45,280</p>
                                <p class="text-xs text-gray-500 mt-1">Total sales</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-rupee-sign text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Medicines</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">156</p>
                                <p class="text-xs text-gray-500 mt-1">In inventory</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-pills text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Low Stock Alert</p>
                                <p class="text-3xl font-bold text-red-600 mt-2">3</p>
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
                    <!-- Sales Chart -->
                    <div class="lg:col-span-2 dashboard-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Sales Trend</h3>
                            <select class="text-sm border border-gray-200 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Last 12 Months</option>
                                <option>Last 6 Months</option>
                                <option>Last 3 Months</option>
                            </select>
                        </div>
                        <div class="h-64">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    
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
                    <div class="table-container">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1024</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 29, 2026</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">John Smith</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Sarah Johnson</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹1,250.00</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1023</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 29, 2026</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Mary Davis</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Michael Brown</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹890.50</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-700 font-medium">View</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1022</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 29, 2026</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Walk-in Customer</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Emily Davis</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹2,450.00</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
        
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                datasets: [{
                    label: 'Revenue',
                    data: [120000, 135000, 125000, 145000, 160000, 155000, 170000, 165000, 180000, 175000, 190000, 185000],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Active menu highlighting
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
