<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Handle Initialize Order
if (isset($_POST['custadd'])) {
    $cid = $conn->real_escape_string($_POST['cid']);
    $username = $conn->real_escape_string($_SESSION['username']);
    
    // Fetch employee ID from session safely
    $qry1 = "SELECT E_ID FROM employee WHERE username='$username'";
    $result1 = $conn->query($qry1);
    $row1 = $result1 ? $result1->fetch_assoc() : null;
    $eid = $row1 ? $row1['E_ID'] : null;

    if ($cid > 0 && $eid) {
        $qry2 = "INSERT INTO sales(c_id, e_id) VALUES ('$cid', '$eid')";
        if ($conn->query($qry2)) {
            set_flash_message("New order session initialized.", "success");
        } else {
            set_flash_message("Error starting order session.", "error");
        }
    } else {
        set_flash_message("Please select a valid customer.", "warning");
    }
}

// Handle Add Item to Cart
if (isset($_POST['add'])) {
    $qry5 = "SELECT sale_id FROM sales ORDER BY sale_id DESC LIMIT 1";
    $result5 = $conn->query($qry5);
    $row5 = $result5->fetch_row();
    $sid = $row5[0];

    $mid = $conn->real_escape_string($_POST['medid']);
    $aqty = (int)$_POST['mqty'];
    $qty = (int)$_POST['mcqty'];
    $mprice = (float)$_POST['mprice'];

    if ($qty > $aqty || $qty <= 0) {
        set_flash_message("Invalid quantity. Only $aqty units in stock.", "error");
    } else {
        $total_price = $mprice * $qty;
        $qry6 = "INSERT INTO sales_items (sale_id, med_id, sale_qty, tot_price) VALUES ('$sid', '$mid', '$qty', '$total_price')";
        if ($conn->query($qry6)) {
            set_flash_message("Item added to cart.", "success");
            header("Location: pos2.php?sid=" . $sid);
            exit();
        } else {
            set_flash_message("Error adding item to cart.", "error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php render_flash_message(); ?>
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Point of Sale</h2>
        <p class="text-slate-500 mt-1 font-medium">Create a new order and record transactions.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Step 1: Customer Selection -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-black text-blue-600 uppercase tracking-widest mb-6">Step 1: Customer</h3>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Select ID</label>
                    <select id="cid" name="cid" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none font-bold">
                        <option value="">*Select Customer</option>
                        <?php
                        $customers = $conn->query("SELECT C_ID, C_Fname, C_Lname FROM customer ORDER BY C_Fname");
                        while ($c = $customers->fetch_assoc()) {
                            echo "<option value='{$c['C_ID']}'>{$c['C_Fname']} {$c['C_Lname']} (ID: {$c['C_ID']})</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="custadd" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-100 hover:bg-blue-700 active:scale-95 transition-all">
                    Initialize Order
                </button>
            </form>
        </div>

        <!-- Step 2: Medicine Search -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-black text-indigo-600 uppercase tracking-widest mb-6">Step 2: Add Medical Items</h3>
                <form method="post" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="flex-grow">
                        <select id="med" name="med" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none font-bold">
                            <option value="">Select Medicine</option>
                            <?php
                            $medicines = $conn->query("SELECT Med_Name FROM meds WHERE Med_Qty > 0 ORDER BY Med_Name");
                            while ($m = $medicines->fetch_assoc()) {
                                echo "<option>{$m['Med_Name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="search" class="bg-indigo-600 text-white font-black py-3 px-10 rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                        Check Stock
                    </button>
                </form>

                <?php
                if (isset($_POST['search']) && !empty($_POST['med'])) {
                    $med = $conn->real_escape_string($_POST['med']);
                    $res = $conn->query("SELECT * FROM meds WHERE Med_Name='$med'");
                    if ($row4 = $res->fetch_row()) {
                ?>
                        <div class="mt-8 pt-8 border-t border-slate-100 animate-in fade-in duration-500">
                            <form method="post">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 text-center sm:text-left">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Med ID</p>
                                        <input type="number" name="medid" value="<?php echo $row4[0]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-bold text-slate-900 focus:ring-0">
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Name</p>
                                        <input type="text" name="mdname" value="<?php echo $row4[1]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-bold text-slate-900 focus:ring-0">
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Available</p>
                                        <input type="number" name="mqty" value="<?php echo $row4[2]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-bold text-emerald-600 focus:ring-0 text-center sm:text-left">
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Price (1 Unit)</p>
                                        <input type="number" name="mprice" value="<?php echo $row4[4]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-black text-slate-900 focus:ring-0">
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row items-end space-y-4 sm:space-y-0 sm:space-x-4 bg-slate-900 p-6 rounded-2xl">
                                    <div class="flex-grow w-full">
                                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Quantity Required</label>
                                        <input type="number" name="mcqty" required class="w-full bg-slate-800 border-slate-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none font-black text-lg">
                                    </div>
                                    <button type="submit" name="add" class="w-full sm:w-auto bg-blue-600 text-white font-black py-4 px-10 rounded-xl shadow-xl hover:bg-blue-700 transition-all active:scale-95">
                                        Add to Cart
                                    </button>
                                </div>
                            </form>
                        </div>
                <?php }
                } ?>
            </div>
        </div>
    </div>

</body>

</html>