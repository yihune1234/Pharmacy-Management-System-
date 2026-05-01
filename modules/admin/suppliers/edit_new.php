<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$supplier_id = $_GET['id'] ?? 0;

if ($supplier_id == 0) {
    set_flash_message("Invalid supplier ID.", "error");
    header("Location: view_new.php");
    exit();
}

// Get supplier details
$supplier_sql = "SELECT * FROM suppliers WHERE Sup_ID = $supplier_id";
$supplier_result = $conn->query($supplier_sql);
$supplier = $supplier_result->fetch_assoc();

if (!$supplier) {
    set_flash_message("Supplier not found.", "error");
    header("Location: view_new.php");
    exit();
}

// Handle form submission
if (isset($_POST['update_supplier'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact_person = $conn->real_escape_string($_POST['contact_person']);
    $payment_terms = $conn->real_escape_string($_POST['payment_terms']);
    $credit_limit = (float)$_POST['credit_limit'];
    $rating = (float)$_POST['rating'];
    $status = $conn->real_escape_string($_POST['status']);

    if (!empty($name) && !empty($phone)) {
        // Check if supplier already exists (excluding current)
        $check_sql = "SELECT Sup_ID FROM suppliers WHERE (Sup_Name = '$name' OR Sup_Phno = '$phone') AND Sup_ID != $supplier_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            set_flash_message("Another supplier with this name or phone number already exists.", "error");
        } else {
            $sql = "UPDATE suppliers SET 
                    Sup_Name = '$name', 
                    Sup_Add = '$address', 
                    Sup_Phno = '$phone', 
                    Sup_Mail = '$email',
                    Contact_Person = '$contact_person',
                    Payment_Terms = '$payment_terms',
                    Credit_Limit = $credit_limit,
                    Rating = $rating,
                    Status = '$status',
                    Updated_At = CURRENT_TIMESTAMP
                    WHERE Sup_ID = $supplier_id";
            
            if ($conn->query($sql)) {
                set_flash_message("Supplier '$name' updated successfully!", "success");
                header("Location: view_new.php");
                exit();
            } else {
                set_flash_message("Error updating supplier. Please try again.", "error");
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
    <title>Edit Supplier - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Supplier</h2>
            <p class="text-slate-500 mt-1 font-medium">Update supplier information and performance rating</p>
        </div>
        <a href="view_new.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Suppliers
        </a>
    </div>

    <div class="max-w-4xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $supplier_id; ?>" method="post" class="space-y-8">
            <!-- Basic Information -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Basic Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Company Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($supplier['Sup_Name']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Contact Person</label>
                        <input type="text" name="contact_person"
                               value="<?php echo htmlspecialchars($supplier['Contact_Person'] ?? ''); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Phone Number <span class="text-rose-500">*</span></label>
                        <input type="tel" name="phone" required
                               value="<?php echo htmlspecialchars($supplier['Sup_Phno']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="email"
                               value="<?php echo htmlspecialchars($supplier['Sup_Mail']); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Address</label>
                    <textarea name="address" rows="3"
                              class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700"><?php echo htmlspecialchars($supplier['Sup_Add']); ?></textarea>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Financial Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Payment Terms</label>
                        <select name="payment_terms" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="Net 15" <?php echo ($supplier['Payment_Terms'] ?? '') == 'Net 15' ? 'selected' : ''; ?>>Net 15 Days</option>
                            <option value="Net 30" <?php echo ($supplier['Payment_Terms'] ?? '') == 'Net 30' ? 'selected' : ''; ?>>Net 30 Days</option>
                            <option value="Net 45" <?php echo ($supplier['Payment_Terms'] ?? '') == 'Net 45' ? 'selected' : ''; ?>>Net 45 Days</option>
                            <option value="Net 60" <?php echo ($supplier['Payment_Terms'] ?? '') == 'Net 60' ? 'selected' : ''; ?>>Net 60 Days</option>
                            <option value="COD" <?php echo ($supplier['Payment_Terms'] ?? '') == 'COD' ? 'selected' : ''; ?>>Cash on Delivery</option>
                            <option value="Advance" <?php echo ($supplier['Payment_Terms'] ?? '') == 'Advance' ? 'selected' : ''; ?>>Advance Payment</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Credit Limit (Rs)</label>
                        <input type="number" name="credit_limit" value="<?php echo $supplier['Credit_Limit'] ?? 0; ?>" min="0" step="1000"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-semibold text-slate-700">
                        <p class="text-xs text-slate-400 mt-1">Maximum credit amount for this supplier</p>
                    </div>
                </div>
            </div>

            <!-- Performance Rating -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-8 rounded-3xl border border-purple-200">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                    Performance Rating
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Rating</label>
                        <select name="rating" 
                                class="w-full bg-white border border-purple-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="5" <?php echo ($supplier['Rating'] ?? 4) == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ Excellent (5)</option>
                            <option value="4" <?php echo ($supplier['Rating'] ?? 4) == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ Good (4)</option>
                            <option value="3" <?php echo ($supplier['Rating'] ?? 4) == 3 ? 'selected' : ''; ?>>⭐⭐⭐ Average (3)</option>
                            <option value="2" <?php echo ($supplier['Rating'] ?? 4) == 2 ? 'selected' : ''; ?>>⭐⭐ Poor (2)</option>
                            <option value="1" <?php echo ($supplier['Rating'] ?? 4) == 1 ? 'selected' : ''; ?>>⭐ Very Poor (1)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
                        <select name="status" 
                                class="w-full bg-white border border-purple-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                            <option value="Active" <?php echo ($supplier['Status'] ?? 'Active') == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($supplier['Status'] ?? 'Active') == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="Probation" <?php echo ($supplier['Status'] ?? 'Active') == 'Probation' ? 'selected' : ''; ?>>Probation</option>
                            <option value="Blacklisted" <?php echo ($supplier['Status'] ?? 'Active') == 'Blacklisted' ? 'selected' : ''; ?>>Blacklisted</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Supplier Statistics -->
            <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-slate-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Purchase Statistics
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php
                    $stats = $conn->query("SELECT COUNT(*) as total_purchases, COALESCE(SUM(P_Qty * P_Cost), 0) as total_spent, MAX(Pur_Date) as last_purchase FROM purchase WHERE Sup_ID = $supplier_id")->fetch_assoc();
                    ?>
                    <div class="bg-white p-4 rounded-xl">
                        <div class="text-sm text-slate-500">Total Purchases</div>
                        <div class="text-2xl font-bold text-slate-900"><?php echo $stats['total_purchases']; ?></div>
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
                <button type="submit" name="update_supplier" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Supplier
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
