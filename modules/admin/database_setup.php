<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/alerts.php';

// Validate admin access
require_admin();
validate_role_area('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-4xl">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-12">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-slate-900 mb-4">Database Setup Required</h1>
                <p class="text-slate-600 text-lg">Your database needs to be initialized with the correct schema.</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-amber-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h3 class="font-bold text-amber-900 mb-2">Database Schema Mismatch</h3>
                        <p class="text-amber-800">The authentication system expects a different database structure than what's currently installed.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-slate-50 rounded-2xl p-6">
                    <h3 class="font-bold text-slate-900 mb-4">Required Database Structure:</h3>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><code class="bg-slate-200 px-2 py-1 rounded">employee</code> table with username, password, role_id</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><code class="bg-slate-200 px-2 py-1 rounded">roles</code> table with role definitions</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Password hashing with <code class="bg-slate-200 px-2 py-1 rounded">password_verify()</code></span>
                        </li>
                    </ul>
                </div>

                <div class="bg-blue-50 rounded-2xl p-6">
                    <h3 class="font-bold text-blue-900 mb-4">Solution:</h3>
                    <ol class="space-y-3 text-sm text-blue-800">
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-blue-600">1.</span>
                            <span>Run the database installer: <code class="bg-blue-100 px-2 py-1 rounded">database/install.php</code></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-blue-600">2.</span>
                            <span>Default admin account: username <code class="bg-blue-100 px-2 py-1 rounded">admin</code>, password <code class="bg-blue-100 px-2 py-1 rounded">admin123</code></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-blue-600">3.</span>
                            <span>The installer will create proper tables with hashed passwords</span>
                        </li>
                    </ol>
                </div>

                <div class="flex space-x-4">
                    <a href="../database/install.php" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-2xl shadow-lg transition-all flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3l2 2m0 0l2-2m-2 2v18m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <span>Run Database Installer</span>
                    </a>
                    <a href="../auth/logout.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-4 px-6 rounded-2xl transition-all">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
