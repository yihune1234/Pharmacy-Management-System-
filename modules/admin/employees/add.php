<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

if (isset($_POST['add'])) {
    $fname = $conn->real_escape_string($_POST['efname']);
    $lname = $conn->real_escape_string($_POST['elname']);
    $bdate = $_POST['ebdate'];
    $age   = (int)$_POST['eage'];
    $sex   = $conn->real_escape_string($_POST['esex']);
    $etype = $conn->real_escape_string($_POST['etype']);
    $jdate = $_POST['ejdate'];
    $sal   = (float)$_POST['esal'];
    $phno  = $conn->real_escape_string($_POST['ephno']);
    $mail  = $conn->real_escape_string($_POST['e_mail']);
    $add   = $conn->real_escape_string($_POST['eadd']);

    if (empty($fname) || $sex == "selected" || $etype == "selected") {
        set_flash_message("Please fill in all required fields.", "warning");
    } else {
        $gender_code = ($sex == 'Male') ? 'M' : (($sex == 'Female') ? 'F' : 'M');
        
        $sql = "INSERT INTO employee (E_Fname, E_Lname, Bdate, E_Age, E_Sex, E_Type, E_Jdate, E_Add, E_Mail, E_Phno, E_Sal) 
                VALUES ('$fname', '$lname', '$bdate', $age, '$gender_code', '$etype', '$jdate', '$add', '$mail', '$phno', $sal)";
        
        if ($conn->query($sql)) {
            set_flash_message("Employee '$fname' registered successfully.", "success");
            header("Location: view.php");
            exit();
        } else {
            set_flash_message("Enrollment failed. Please verify the details.", "error");
        }
    }
}
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

    <!-- Page Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-center lg:text-left">Register Personnel</h2>
            <p class="text-slate-500 mt-1 font-medium text-center lg:text-left">Onboard a new staff member to the pharmacy system.</p>
        </div>
        <a href="view.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Directory
        </a>
    </div>

    <!-- Form Card -->
    <div class="max-w-6xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Personal Info -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white p-10 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40">
                        <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center">
                            <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm">01</span>
                            Staff Identity
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="efname" placeholder="Staff first name" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Last Name</label>
                                <input type="text" name="elname" placeholder="Staff last name"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Birth Date</label>
                                <input type="date" name="ebdate" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Age</label>
                                    <input type="number" name="eage" placeholder="00"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Gender <span class="text-rose-500">*</span></label>
                                    <select name="esex" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                                        <option value="selected">Select</option>
                                        <option>Female</option>
                                        <option>Male</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 pt-10 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Contact Number</label>
                                <input type="tel" name="ephno" placeholder="+1..."
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                                <input type="email" name="e_mail" placeholder="email@pharmacia.com"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Residential Address</label>
                                <input type="text" name="eadd" placeholder="Current living address"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Info -->
                <div class="space-y-8">
                    <div class="bg-white p-10 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 h-full">
                        <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center">
                            <span class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mr-3 text-sm">02</span>
                            Employment
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Professional Role <span class="text-rose-500">*</span></label>
                                <select name="etype" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-bold text-slate-700 appearance-none">
                                    <option value="selected">Select Role</option>
                                    <option>Pharmacist</option>
                                    <option>Manager</option>
                                    <option>Cashier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Joining Date</label>
                                <input type="date" name="ejdate" value="<?php echo date('Y-m-d'); ?>"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-bold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Monthly Salary (Rs)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="esal" placeholder="0.00" required
                                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-black text-slate-700">
                                </div>
                            </div>
                        </div>

                        <div class="mt-12">
                            <button type="submit" name="add" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center">
                                Enroll Staff Member
                            </button>
                            <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-4">Employee ID will be auto-generated</p>
                        </div>
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