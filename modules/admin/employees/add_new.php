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
        
        // Check if username already exists
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
            
            // Log access profile creation
            set_flash_message("Personnel registration successful Access Protocol initialized.<br><strong>Username:</strong> $username<br><strong>Key:</strong> $default_password", "success");
            header("Location: view_new.php");
            exit();
        } else {
            set_flash_message("Operational failure during personnel integration.", "error");
        }
    } else {
        set_flash_message("Signature required: Primary fields missing.", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Recruitment - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header -->
    <div class="mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Human Capital Registry</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Recruit Staff</h1>
            <p class="text-slate-500 font-medium mt-1">Onboard new operational assets and synthesize system credentials.</p>
        </div>
        <a href="view_new.php" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
            <i class="fas fa-users mr-2"></i> Employee Directory
        </a>
    </div>

    <!-- Main Entry Console -->
    <div class="max-w-6xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Personnel Identity Block -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
                    <h3 class="text-xl font-black text-slate-900 uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black shadow-lg shadow-blue-200">01</span>
                        Personnel Identity
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Forename</label>
                            <input type="text" name="fname" placeholder="John" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Surname</label>
                            <input type="text" name="lname" placeholder="Doe" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Birth Protocol</label>
                            <input type="date" name="dob"
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Gender Alias</label>
                            <select name="gender" 
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700 appearance-none">
                                <option value="M">Masculine</option>
                                <option value="F">Feminine</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Comm Channel</label>
                            <input type="tel" name="phone" placeholder="98XXXXXXXX"
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Digital Mailbox</label>
                        <input type="email" name="email" placeholder="personnel@pharmacia.com"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                    </div>
                </div>
            </div>

            <!-- Operational Auth Block -->
            <div class="space-y-8">
                <div class="bg-slate-900 p-10 rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col h-full">
                    <h3 class="text-xl font-black text-white uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-white/10 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black backdrop-blur">02</span>
                        Operational Auth
                    </h3>

                    <div class="space-y-8 flex-1">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Designated Rank</label>
                            <input type="text" name="position" placeholder="e.g. Lead Pharmacist" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Reward Level (Rs)</label>
                            <input type="number" name="salary" placeholder="Monthly Salary..."
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">System Role</label>
                            <select name="role" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white appearance-none">
                                <option value="Pharmacist" class="bg-slate-800">Pharmacist</option>
                                <option value="Manager" class="bg-slate-800">Operational Manager</option>
                                <option value="Admin" class="bg-slate-800">System Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-10">
                        <button type="submit" name="add_employee" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-6 rounded-2xl shadow-xl shadow-blue-500/20 transition-all active:scale-95 flex items-center justify-center text-lg">
                            <i class="fas fa-plus-circle mr-3"></i> Initialize Onboarding
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>
</body>
</html>
