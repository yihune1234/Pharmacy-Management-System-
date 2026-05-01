<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$customer_id = $_GET['id'] ?? 0;

if ($customer_id == 0) {
    set_flash_message("Invalid customer ID.", "error");
    header("Location: view_new.php");
    exit();
}

// Get customer details
$customer_sql = "SELECT * FROM customer WHERE C_ID = $customer_id";
$customer_result = $conn->query($customer_sql);
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    set_flash_message("Customer not found.", "error");
    header("Location: view_new.php");
    exit();
}

// Handle form submission
if (isset($_POST['update_customer'])) {
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $age = (int)$_POST['age'];
    $sex = $_POST['sex'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $loyalty_points = (int)$_POST['loyalty_points'];

    if (!empty($fname) && !empty($lname)) {
        // Check if phone/email already exists for other customers
        $check_sql = "SELECT C_ID FROM customer WHERE (C_Phno = '$phone' OR C_Mail = '$email') AND C_ID != $customer_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            set_flash_message("Another customer with this phone number or email already exists.", "error");
        } else {
            $sql = "UPDATE customer SET 
                    C_Fname = '$fname', 
                    C_Lname = '$lname', 
                    C_Age = $age, 
                    C_Sex = '$sex', 
                    C_Phno = '$phone', 
                    C_Mail = '$email', 
                    C_Add = '$address',
                    Loyalty_Points = $loyalty_points,
                    Updated_At = CURRENT_TIMESTAMP
                    WHERE C_ID = $customer_id";
            
            if ($conn->query($sql)) {
                set_flash_message("Customer '$fname $lname' updated successfully!", "success");
                header("Location: view_new.php");
                exit();
            } else {
                set_flash_message("Error updating customer. Please try again.", "error");
            }
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
    <title>Edit Customer - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Customer</h2>
            <p class="text-slate-500 mt-1 font-medium">Update customer information and loyalty status</p>
        </div>
        <a href="view_new.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Customers
        </a>
    </div>

    <div class="max-w-4xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $customer_id; ?>" method="post" class="space-y-8">
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
                               value="<?php echo htmlspecialchars($customer['C_Fname']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="lname" required
                               value="<?php echo htmlspecialchars($customer['C_Lname']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Age</label>
                        <input type="number" name="age" min="1" max="120"
                               value="<?php echo htmlspecialchars($customer['C_Age']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Gender</label>
                        <select name="sex" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="M" <?php echo $customer['C_Sex'] == 'M' ? 'selected' : ''; ?>>Male</option>
                            <option value="F" <?php echo $customer['C_Sex'] == 'F' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone Number <span class="text-rose-500">*</span></label>
                        <input type="tel" name="phone" required
                               value="<?php echo htmlspecialchars($customer['C_Phno']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Contact Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="email"
                               value="<?php echo htmlspecialchars($customer['C_Mail']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                        <p class="text-xs text-slate-400 mt-1">Optional - for notifications and receipts</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Address</label>
                        <textarea name="address" rows="3"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700"><?php echo htmlspecialchars($customer['C_Add']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Loyalty Program -->
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-8 rounded-3xl border border-amber-200">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    Loyalty Program
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Current Points</label>
                        <input type="number" name="loyalty_points" value="<?php echo $customer['Loyalty_Points'] ?? 0; ?>" min="0"
                               class="w-full bg-white border border-amber-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none font-semibold text-slate-700">
                        <p class="text-xs text-slate-400 mt-1">Current loyalty points balance</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Current Tier</label>
                        <div class="bg-white border border-amber-200 rounded-2xl px-5 py-4">
                            <?php
                            $points = $customer['Loyalty_Points'] ?? 0;
                            $tier = $points >= 1000 ? 'Gold' : ($points >= 500 ? 'Silver' : 'Bronze');
                            $tier_color = ['Bronze' => 'text-slate-700', 'Silver' => 'text-gray-700', 'Gold' => 'text-amber-700'];
                            ?>
                            <span class="font-bold <?php echo $tier_color[$tier]; ?>"><?php echo $tier; ?> Tier</span>
                            <span class="text-sm text-slate-500 ml-2">(<?php echo $points; ?> points)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Statistics -->
            <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-slate-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Purchase Statistics
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php
                    $stats = $conn->query("SELECT COUNT(*) as total_sales, COALESCE(SUM(Total_Amt), 0) as total_spent, MAX(S_Date) as last_purchase FROM sales WHERE C_ID = $customer_id AND Refunded = 0")->fetch_assoc();
                    ?>
                    <div class="bg-white p-4 rounded-xl">
                        <div class="text-sm text-slate-500">Total Sales</div>
                        <div class="text-2xl font-bold text-slate-900"><?php echo $stats['total_sales']; ?></div>
                    </div>
                    <div class="bg-white p-4 rounded-xl">
                        <div class="text-sm text-slate-500">Total Spent</div>
                        <div class="text-2xl font-bold text-slate-900">Rs. <?php echo number_format($stats['total_spent'], 0); ?></div>
                    </div>
                    <div class="bg-white p-4 rounded-xl">
                        <div class="text-sm text-slate-500">Last Purchase</div>
                        <div class="text-lg font-bold text-slate-900"><?php echo $stats['last_purchase'] ? date('M j, Y', strtotime($stats['last_purchase'])) : 'Never'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="view_new.php" class="bg-white text-slate-600 px-8 py-4 rounded-2xl font-bold border border-slate-200 hover:bg-slate-50 transition-all">
                    Cancel
                </a>
                <button type="submit" name="update_customer" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Customer
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
