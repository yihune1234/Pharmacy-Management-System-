<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');

if (isset($_POST['add'])) {
    $fname = $conn->real_escape_string($_POST['cfname']);
    $lname = $conn->real_escape_string($_POST['clname']);
    $age   = (int)$_POST['age'];
    $sex   = $conn->real_escape_string($_POST['sex']);
    $phno  = $conn->real_escape_string($_POST['phno']);
    $mail  = $conn->real_escape_string($_POST['emid']);

    if (empty($fname) || $sex == "selected") {
        set_flash_message("Please provide at least a name and gender.", "warning");
    } else {
        $gender_code = ($sex == 'Male') ? 'M' : (($sex == 'Female') ? 'F' : 'M'); // Defaulting Others to M or we could add it to ENUM
        
        $sql = "INSERT INTO customer (C_Fname, C_Lname, C_Age, C_Sex, C_Phno, C_Mail) 
                VALUES ('$fname', '$lname', $age, '$gender_code', '$phno', '$mail')";
        
        if ($conn->query($sql)) {
            set_flash_message("Customer '$fname' registered successfully.", "success");
            header("Location: view.php");
            exit();
        } else {
            set_flash_message("Registration failed. Please check for duplicate details.", "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <!-- Page Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">New Member</h2>
            <p class="text-slate-500 mt-1 font-medium">Register a new customer for loyalty and history.</p>
        </div>
        <a href="view.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Directory
        </a>
    </div>

    <!-- Form Card -->
    <div class="max-w-4xl bg-white p-10 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                
                <!-- Personal Info -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-[0.2em] mb-4">Personal Details</h3>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">First name <span class="text-rose-500">*</span></label>
                        <input type="text" name="cfname" placeholder="John" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Last name</label>
                        <input type="text" name="clname" placeholder="Doe"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Age</label>
                            <input type="number" name="age" placeholder="00"
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Gender <span class="text-rose-500">*</span></label>
                            <select name="sex" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                                <option value="selected">Select</option>
                                <option>Female</option>
                                <option>Male</option>
                                <option>Others</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-emerald-600 uppercase tracking-[0.2em] mb-4">Contact Information</h3>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone Number</label>
                        <input type="tel" name="phno" placeholder="+1..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="emid" placeholder="john@example.com"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    <div class="pt-6">
                        <button type="submit" name="add" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black py-5 rounded-2xl shadow-xl transition-all active:scale-95 flex items-center justify-center">
                            Save Customer Details
                        </button>
                        <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-4">Membership ID will be assigned automatically</p>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>