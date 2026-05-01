<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate authentication
require_auth();

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current employee data
    $employee_id = $_SESSION['user'];
    $emp_sql = "SELECT E_Password, Password_Changed FROM employee WHERE E_ID = $employee_id";
    $emp_result = $conn->query($emp_sql);
    $employee = $emp_result->fetch_assoc();
    
    // Validate current password
    if (!password_verify($current_password, $employee['E_Password'])) {
        set_flash_message("Current password is incorrect.", "error");
    } elseif (strlen($new_password) < 6) {
        set_flash_message("New password must be at least 6 characters long.", "error");
    } elseif ($new_password !== $confirm_password) {
        set_flash_message("New passwords do not match.", "error");
    } else {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and mark as changed
        $update_sql = "UPDATE employee SET E_Password = '$hashed_password', Password_Changed = 1 WHERE E_ID = $employee_id";
        
        if ($conn->query($update_sql)) {
            // Log activity
            log_activity($employee_id, 'PASSWORD_CHANGE', "Employee changed their password");
            
            set_flash_message("Password changed successfully!", "success");
            header("Location: ../dashboard_secure.php");
            exit();
        } else {
            set_flash_message("Error changing password. Please try again.", "error");
        }
    }
}

// Get employee info
$employee_id = $_SESSION['user'];
$emp_sql = "SELECT E_Fname, E_Lname, E_Username, Password_Changed FROM employee WHERE E_ID = $employee_id";
$emp_result = $conn->query($emp_sql);
$employee = $emp_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Change Password</h2>
            <p class="text-slate-500 mt-1 font-medium">Update your account password</p>
        </div>
        <a href="../dashboard_secure.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <?php if (!$employee['Password_Changed']): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 mb-8">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M3 20a6 6 0 0112 0v-6a6 6 0 00-12 0v6a6 6 0 00-6-6h-2m0 10a6 6 0 006 6h10a6 6 0 006-6v-10a6 6 0 00-6-6h-2"></path>
            </svg>
            <div>
                <h3 class="text-lg font-bold text-amber-800">Security Notice</h3>
                <p class="text-amber-700">You are using a default password. Please change your password for security reasons.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="max-w-2xl">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($employee['E_Fname'] . ' ' . $employee['E_Lname']); ?></h3>
                        <p class="text-slate-600">Username: <?php echo htmlspecialchars($employee['E_Username']); ?></p>
                    </div>
                </div>
            </div>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-6">
                <!-- Current Password -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Current Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="current_password" required
                           placeholder="Enter your current password" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">New Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="new_password" required minlength="6"
                           placeholder="Enter your new password (min 6 characters)" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    <p class="text-xs text-slate-400 mt-1">Password must be at least 6 characters long</p>
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Confirm New Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="confirm_password" required minlength="6"
                           placeholder="Confirm your new password" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                </div>

                <!-- Password Requirements -->
                <div class="bg-blue-50 p-4 rounded-2xl">
                    <h4 class="text-sm font-bold text-blue-900 mb-2">Password Requirements:</h4>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            At least 6 characters long
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Different from current password
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            New password and confirmation must match
                        </li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="../dashboard_secure.php" class="bg-white text-slate-600 px-8 py-4 rounded-2xl font-bold border border-slate-200 hover:bg-slate-50 transition-all">
                        Cancel
                    </a>
                    <button type="submit" name="change_password" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
