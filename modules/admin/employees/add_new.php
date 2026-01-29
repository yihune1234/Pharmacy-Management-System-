<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Handle form submission
if (isset($_POST['add_employee'])) {
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $position = $conn->real_escape_string($_POST['position']);
    $salary = (float)$_POST['salary'];
    $role = $conn->real_escape_string($_POST['role']);
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? 'M';

    if (!empty($fname) && !empty($lname)) {
        // Auto-generate username and password
        $username = strtolower($fname) . strtolower($lname);
        $default_password = strtolower($fname) . '123';
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        // Check if username already exists, add number if needed
        $check_sql = "SELECT E_ID FROM employee WHERE E_Username = '$username'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $counter = 1;
            do {
                $new_username = $username . $counter;
                $check_sql = "SELECT E_ID FROM employee WHERE E_Username = '$new_username'";
                $check_result = $conn->query($check_sql);
                $counter++;
            } while ($check_result && $check_result->num_rows > 0);
            $username = $new_username;
        }
        
        $sql = "INSERT INTO employee (E_Fname, E_Lname, E_Email, E_Phno, E_Add, E_Type, E_Sal, E_Username, E_Password, E_Bdate, E_Sex, E_Jdate) 
                VALUES ('$fname', '$lname', '$email', '$phone', '$address', '$position', $salary, '$username', '$hashed_password', '$dob', '$gender', CURDATE())";
        
        if ($conn->query($sql)) {
            $employee_id = $conn->insert_id;
            
            // Insert into roles table
            $role_sql = "INSERT INTO roles (role_name, description) VALUES ('$role', 'Employee role for $position')";
            $conn->query($role_sql);
            $role_id = $conn->insert_id;
            
            // Update employee with role_id
            $update_sql = "UPDATE employee SET role_id = $role_id WHERE E_ID = $employee_id";
            $conn->query($update_sql);
            
            // Log activity
            log_activity($_SESSION['user'], 'ADD_EMPLOYEE', "Added new employee: $fname $lname with username: $username and password: $default_password");
            
            set_flash_message("Employee '$fname $lname' added successfully!<br><strong>Username:</strong> $username<br><strong>Password:</strong> $default_password", "success");
            header("Location: view_new.php");
            exit();
        } else {
            set_flash_message("Error adding employee. Please try again.", "error");
        }
    } else {
        set_flash_message("Please fill in all required fields.", "error");
    }
}

// Get roles for dropdown
$roles = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Add New Employee</h2>
            <p class="text-slate-500 mt-1 font-medium">Register a new employee in the system</p>
        </div>
        <a href="view_new.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            View Employees
        </a>
    </div>

    <div class="max-w-4xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-8">
            <!-- Personal Information -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="fname" required
                               placeholder="John" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="lname" required
                               placeholder="Doe" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Date of Birth</label>
                        <input type="date" name="dob"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Gender</label>
                        <select name="gender" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone Number</label>
                        <input type="tel" name="phone"
                               placeholder="123-456-7890" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="email"
                               placeholder="john@example.com" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Address</label>
                        <textarea name="address" rows="3"
                                  placeholder="123 Main St, City, State" 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700"></textarea>
                    </div>
                </div>
            </div>

            <!-- Job Information -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 9c-2.395 0-4.595.24-6.34-2.414-.13-.274-.274-.52.584-.52 1.67-.21 3.334.0 4.995-.31 2.326-.31 4.512-.705 6.244-2.236 1.732-1.531 3.113-3.328 3.113-5.456 0-2.128-2.062-3.761-4.663-3.761-5.93 0-1.31.465-2.38 1.236-2.38 1.236-.408 1.236 1.826 0 2.236-.16.45.308.292.486.477 1.649-1.586 2.913-1.586 4.835 0 1.248.0 2.398.705 3.429 1.975.055.4.145.108.224.108.224 0 1.416-.015 1.515-1.515.753-1.863.726-2.039.192-.3.324-.1.486-.1.726 0-1.336.13-2.462.157-3.628.075-.773.412-1.156.895-1.156 1.486 0 1.542.005 1.542.005.646 0 1.322-.16.770-.277 1.424-.064.318-.215.724-.215.724 0 1.418.437 1.418.437.16.316.477.654.477.654 0 1.29-.128 1.29-.128.328 0 .646-.195.646-.195 0 .723-.247.723-.247.138 0 .227-.008.227-.008.1 0 .215-.008.438 0 .438 0 1.26-.064 1.26-.064.313 0 .628-.255.628-.255 0 1.41-.33 1.41-.33.064 0 .127.008.127.008.064 0 .215-.009.215-.009.1 0 .4-.057.8-.057.8 0 .993.062.993.062.17 0 .322-.009.322-.009.093 0 .205-.009.205-.009.064 0 .222-.01.222-.01.1 0 .384-.031.384-.031.063 0 .127-.01.127-.01.063 0 .257-.016.257-.016.113 0 .266-.016.266-.016.063 0 .125-.01.125-.01.063 0 .251-.008.251-.008.125 0 .503-.016.503-.016.063 0 .251-.008.251-.008z"></path>
                    </svg>
                    Job Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Position/Job Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="position" required
                               placeholder="Pharmacist" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Salary (Rs)</label>
                        <input type="number" name="salary" step="100" min="0"
                               placeholder="50000" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Role <span class="text-rose-500">*</span></label>
                    <select name="role" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                        <option value="">Select Role</option>
                        <option value="Admin">Administrator</option>
                        <option value="Pharmacist">Pharmacist</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
            </div>

            <!-- Login Credentials -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100">
                <h3 class="text-lg font-bold text-blue-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Login Credentials (Auto-Generated)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-xl border border-blue-200">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Username</p>
                        <p class="text-sm text-slate-700 font-medium">firstname + lastname (lowercase)</p>
                        <p class="text-xs text-slate-500 mt-1">Example: johnsmith</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-blue-200">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Password</p>
                        <p class="text-sm text-slate-700 font-medium">firstname + 123</p>
                        <p class="text-xs text-slate-500 mt-1">Example: john123</p>
                    </div>
                </div>
                <p class="text-xs text-blue-700 mt-3 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Credentials will be displayed after successful employee creation
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="view_new.php" class="bg-white text-slate-600 px-8 py-4 rounded-2xl font-bold border border-slate-200 hover:bg-slate-50 transition-all">
                    Cancel
                </a>
                <button type="submit" name="add_employee" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Add Employee
                </button>
            </div>
        </form>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
