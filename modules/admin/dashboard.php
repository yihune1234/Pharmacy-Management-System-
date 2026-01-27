<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('./sidebar.php'); ?>

    <!-- Header Section -->
    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-center lg:text-left">Admin Dashboard</h2>
        <p class="text-slate-500 mt-2 font-medium text-center lg:text-left">Overview and Quick Actions</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Stock Level</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">Healthy</p>
                </div>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Pending Orders</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">05</p>
                </div>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Today's Revenue</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">Rs. 4,200</p>
                </div>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm transition hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Staff Count</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">12</p>
                </div>
                <div class="p-2 bg-orange-50 text-orange-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        <a href="sales/pos1.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
            <div class="flex items-center space-x-6 mb-6">
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 group-hover:rotate-6 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Point of Sale</h3>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed mb-4">Open the direct billing terminal to generate customer invoices and record sales transactions.</p>
            <span class="text-blue-600 text-xs font-extrabold uppercase tracking-widest flex items-center">
                Launch Terminal
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </span>
        </a>

        <a href="inventory/view.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
            <div class="flex items-center space-x-6 mb-6">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 group-hover:rotate-6 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Inventory Control</h3>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed mb-4">Manage stock levels, update medical prices, and organize product locations using specialized shelves.</p>
            <span class="text-indigo-600 text-xs font-extrabold uppercase tracking-widest flex items-center">
                Manage Stock
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </span>
        </a>

        <a href="employees/view.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
            <div class="flex items-center space-x-6 mb-6">
                <div class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200 group-hover:rotate-6 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Staff Directory</h3>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed mb-4">View and modify system users, update contact details, and handle professional payroll operations.</p>
            <span class="text-emerald-600 text-xs font-extrabold uppercase tracking-widest flex items-center">
                User Management
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </span>
        </a>

        <a href="reports/sales_report.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
            <div class="flex items-center space-x-6 mb-6">
                <div class="w-16 h-16 bg-amber-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-amber-200 group-hover:rotate-6 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Data Analysis</h3>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed mb-4">Export detailed profit reports and visualize sales data trends for any specific period or time frame.</p>
            <span class="text-amber-600 text-xs font-extrabold uppercase tracking-widest flex items-center">
                Financial Audit
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </span>
        </a>

        <a href="reports/stock_report.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 group">
            <div class="flex items-center space-x-6 mb-6">
                <div class="w-16 h-16 bg-red-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-red-200 group-hover:rotate-6 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-red-600 transition-colors">Security & Alerts</h3>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed mb-4">Instantly monitor low stock warnings and expiration alerts to ensure seamless pharmacy operations.</p>
            <span class="text-red-600 text-xs font-extrabold uppercase tracking-widest flex items-center">
                Check Criticals
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </span>
        </a>
    </div>

    <!-- End tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>