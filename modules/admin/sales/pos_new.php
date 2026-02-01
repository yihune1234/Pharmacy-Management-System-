<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get customers for dropdown
$customers = $conn->query("SELECT C_ID, C_Fname, C_Lname FROM customer ORDER BY C_Fname, C_Lname");

// Get medicines for dropdown
$medicines = $conn->query("SELECT Med_ID, Med_Name, Med_Price, Med_Qty FROM meds WHERE Med_Qty > 0 ORDER BY Med_Name");

// Handle customer selection
if (isset($_POST['select_customer'])) {
    $customer_id = (int)$_POST['customer_id'];
    
    if ($customer_id > 0) {
        $_SESSION['pos_customer_id'] = $customer_id;
        $_SESSION['pos_items'] = [];
        set_flash_message("Customer selected. Now add medicines to cart.", "success");
        header("Location: pos_cart.php");
        exit();
    } else {
        set_flash_message("Please select a valid customer.", "error");
    }
}

// Handle new customer creation
if (isset($_POST['add_customer'])) {
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $age = (int)$_POST['age'];
    $sex = $_POST['sex'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);

    if (!empty($fname) && !empty($lname)) {
        $sql = "INSERT INTO customer (C_Fname, C_Lname, C_Age, C_Sex, C_Phno, C_Mail) 
                VALUES ('$fname', '$lname', $age, '$sex', '$phone', '$email')";
        
        if ($conn->query($sql)) {
            $customer_id = $conn->insert_id;
            $_SESSION['pos_customer_id'] = $customer_id;
            $_SESSION['pos_items'] = [];
            set_flash_message("New customer added successfully!", "success");
            header("Location: pos_cart.php");
            exit();
        } else {
            set_flash_message("Error adding customer. Please try again.", "error");
        }
    } else {
        set_flash_message("Please fill in all required fields.", "error");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Point of Sale</h2>
            <p class="text-slate-500 mt-1 font-medium">Create new sales transaction</p>
        </div>
        <div class="flex space-x-3">
            <a href="../dashboard.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Cancel
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Customer Selection -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Select Customer
            </h3>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Existing Customer</label>
                    <select name="customer_id" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                        <option value="0">Choose Customer</option>
                        <?php while($customer = $customers->fetch_assoc()): ?>
                            <option value="<?php echo $customer['C_ID']; ?>">
                                <?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" name="select_customer" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    Continue to Cart
                </button>
            </form>
        </div>

        <!-- Add New Customer -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Add New Customer
            </h3>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="fname" required
                               placeholder="John" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="lname" required
                               placeholder="Doe" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Age</label>
                        <input type="number" name="age" min="1" max="120"
                               placeholder="25" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Gender</label>
                        <select name="sex" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone</label>
                        <input type="tel" name="phone"
                               placeholder="123-456-7890" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email</label>
                    <input type="email" name="email"
                           placeholder="john@example.com" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                </div>
                
                <button type="submit" name="add_customer" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-200 transition-all flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Customer & Continue
                </button>
            </form>
        </div>
    </div>

    <!-- Available Medicines Preview -->
    <div class="mt-8 bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
            <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Available Medicines (<?php echo $medicines ? $medicines->num_rows : 0; ?> items)
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php if ($medicines && $medicines->num_rows > 0): ?>
                <?php 
                $medicines->data_seek(0);
                $count = 0;
                while(($medicine = $medicines->fetch_assoc()) && $count < 8): 
                ?>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($medicine['Med_Name']); ?></div>
                        <div class="text-xs text-slate-600 mt-1">
                            Stock: <span class="font-bold text-emerald-600"><?php echo $medicine['Med_Qty']; ?></span> | 
                            Price: <span class="font-bold text-blue-600">Rs. <?php echo number_format($medicine['Med_Price'], 2); ?></span>
                        </div>
                    </div>
                <?php 
                $count++;
                endwhile; 
                ?>
            <?php else: ?>
                <div class="col-span-full text-center text-slate-500 py-8">
                    No medicines available in stock
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($medicines && $medicines->num_rows > 8): ?>
            <p class="text-sm text-slate-500 mt-4 text-center">Showing 8 of <?php echo $medicines->num_rows; ?> available medicines</p>
        <?php endif; ?>
    </div>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
