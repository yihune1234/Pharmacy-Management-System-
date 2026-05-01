<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$employee_id = $_GET['id'] ?? null;

if (!$employee_id) {
    set_flash_message("Operational failure: Personnel ID missing.", "error");
    header("Location: view_new.php");
    exit();
}

// Fetch employee details
$stmt = $conn->prepare("SELECT * FROM employee WHERE E_ID = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    set_flash_message("Protocol breach: Employee not found in registry.", "error");
    header("Location: view_new.php");
    exit();
}

// Handle form submission
if (isset($_POST['update_employee'])) {
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $position = $conn->real_escape_string($_POST['position']);
    $salary = (float)$_POST['salary'];
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? 'M';

    if (!empty($fname) && !empty($lname)) {
        $stmt = $conn->prepare("UPDATE employee SET E_Fname = ?, E_Lname = ?, E_Mail = ?, E_Phno = ?, E_Add = ?, E_Type = ?, E_Sal = ? WHERE E_ID = ?");
        $stmt->bind_param("ssssssdi", $fname, $lname, $email, $phone, $address, $position, $salary, $employee_id);
        
        if ($stmt->execute()) {
            set_flash_message("Personnel records successfully synchronized for ID #$employee_id.", "success");
            header("Location: view_new.php");
            exit();
        } else {
            set_flash_message("Operational failure during personnel record update.", "error");
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
    <title>Personnel Update - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header -->
    <div class="mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Human Capital Registry</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Edit Personnel</h1>
            <p class="text-slate-500 font-medium mt-1">Synchronize data for ID #<?php echo str_pad($employee_id, 3, '0', STR_PAD_LEFT); ?>.</p>
        </div>
        <a href="view_new.php" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
            <i class="fas fa-arrow-left-long mr-2"></i> Return to Registry
        </a>
    </div>

    <!-- Main Entry Console -->
    <div class="max-w-6xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $employee_id; ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Personnel Identity Block -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
                    <h3 class="text-xl font-black text-slate-900 uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black shadow-lg shadow-blue-200">ID</span>
                        Profile Identity
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Forename</label>
                            <input type="text" name="fname" value="<?php echo htmlspecialchars($employee['E_Fname']); ?>" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Surname</label>
                            <input type="text" name="lname" value="<?php echo htmlspecialchars($employee['E_Lname']); ?>" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Birth Protocol</label>
                            <input type="date" name="dob" value=""
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
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($employee['E_Phno']); ?>" 
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Digital Mailbox</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($employee['E_Mail']); ?>" 
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Residential Sector (Address)</label>
                        <textarea name="address" rows="2"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700"><?php echo htmlspecialchars($employee['E_Add']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Operational Auth Block -->
            <div class="space-y-8">
                <div class="bg-slate-900 p-10 rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col h-full">
                    <h3 class="text-xl font-black text-white uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-white/10 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black backdrop-blur">OP</span>
                        System Auth
                    </h3>

                    <div class="space-y-8 flex-1">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Designated Rank</label>
                            <input type="text" name="position" value="<?php echo htmlspecialchars($employee['E_Type']); ?>" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Reward Level (Rs)</label>
                            <input type="number" name="salary" value="<?php echo $employee['E_Sal']; ?>"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white">
                        </div>
                        
                        <div class="p-6 bg-white/5 rounded-3xl border border-white/10 border-dashed">
                             <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Access Credentials</p>
                             <p class="text-white text-sm font-bold">Username: <span class="text-blue-400"><?php echo $employee['E_Username']; ?></span></p>
                             <p class="text-slate-500 text-[10px] mt-2">Passwords are encrypted and cannot be displayed.</p>
                        </div>
                    </div>

                    <div class="pt-10">
                        <button type="submit" name="update_employee" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-6 rounded-2xl shadow-xl shadow-blue-500/20 transition-all active:scale-95 flex items-center justify-center text-lg">
                            <i class="fas fa-sync-alt mr-3"></i> Synchronize Data
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
