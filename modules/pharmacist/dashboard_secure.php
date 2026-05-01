<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/alerts.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('./sidebar.php'); ?>

    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pharmacist Dashboard</h2>
        <p class="text-slate-500 mt-2 font-medium">Daily Operations & Sales</p>
    </div>

    <!-- Welcome Message -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-8">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h3>
                <p class="text-slate-500 text-sm">You are logged in as Pharmacist</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-6">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Today's Sales</p>
                <p class="text-2xl font-black text-slate-900">0 Items Sold</p>
            </div>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-6">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Total Inventory</p>
                <p class="text-2xl font-black text-slate-900">154 Skus</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <a href="sales/pos1.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2 tracking-tight">Point of Sale</h3>
            <p class="text-slate-500 mb-6 leading-relaxed">Start a new transaction, scan medications, and generate digital receipts for walk-in customers.</p>
            <div class="flex items-center text-blue-600 font-bold uppercase text-xs tracking-widest">
                <span>Start Selling</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <a href="inventory/view.php" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="w-20 h-20 bg-slate-800 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2 tracking-tight">Stock Search</h3>
            <p class="text-slate-500 mb-6 leading-relaxed">Instantly check medication availability, pricing, shelf localization and expiry dates in the database.</p>
            <div class="flex items-center text-slate-900 font-bold uppercase text-xs tracking-widest">
                <span>Check Records</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>
    </div>

    <!-- Sidebar footer tags handled by include -->
    </main>
    </div>
    </div>
</body>
</html>
