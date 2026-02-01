<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Check if customer is selected
if (!isset($_SESSION['pos_customer_id'])) {
    set_flash_message("Please select a customer first.", "error");
    header("Location: pos_new.php");
    exit();
}

// Handle clear cart
if (isset($_GET['clear_cart'])) {
    unset($_SESSION['pos_items']);
    set_flash_message("Cart cleared.", "success");
    header("Location: pos_cart.php");
    exit();
}

$customer_id = $_SESSION['pos_customer_id'];
$customer_info = null;

// Get customer information
$customer_sql = "SELECT C_ID, C_Fname, C_Lname FROM customer WHERE C_ID = $customer_id";
$customer_result = $conn->query($customer_sql);
if ($customer_result) {
    $customer_info = $customer_result->fetch_assoc();
}

// Initialize cart if not exists
if (!isset($_SESSION['pos_items'])) {
    $_SESSION['pos_items'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $medicine_id = (int)$_POST['medicine_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($medicine_id > 0 && $quantity > 0) {
        // Check stock availability
        $stock_check = $conn->query("SELECT Med_Name, Med_Price, Med_Qty FROM meds WHERE Med_ID = $medicine_id");
        $medicine = $stock_check->fetch_assoc();
        
        if ($medicine && $medicine['Med_Qty'] >= $quantity) {
            $item_key = $medicine_id;
            
            if (isset($_SESSION['pos_items'][$item_key])) {
                // Update existing item
                $new_quantity = $_SESSION['pos_items'][$item_key]['quantity'] + $quantity;
                if ($medicine['Med_Qty'] >= $new_quantity) {
                    $_SESSION['pos_items'][$item_key]['quantity'] = $new_quantity;
                    set_flash_message("Item quantity updated in cart.", "success");
                } else {
                    set_flash_message("Insufficient stock. Available: " . $medicine['Med_Qty'] . " units.", "error");
                }
            } else {
                // Add new item
                $_SESSION['pos_items'][$item_key] = [
                    'id' => $medicine_id,
                    'name' => $medicine['Med_Name'],
                    'price' => $medicine['Med_Price'],
                    'quantity' => $quantity
                ];
                set_flash_message("Item added to cart.", "success");
            }
        } else {
            set_flash_message("Insufficient stock or medicine not found.", "error");
        }
    }
    
    header("Location: pos_cart.php");
    exit();
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $item_key = $_GET['remove'];
    if (isset($_SESSION['pos_items'][$item_key])) {
        unset($_SESSION['pos_items'][$item_key]);
        set_flash_message("Item removed from cart.", "success");
    }
    header("Location: pos_cart.php");
    exit();
}

// Handle update quantity
if (isset($_POST['update_quantity'])) {
    $item_key = $_POST['item_key'];
    $quantity = (int)$_POST['quantity'];
    
    if (isset($_SESSION['pos_items'][$item_key]) && $quantity > 0) {
        $medicine_id = $_SESSION['pos_items'][$item_key]['id'];
        $stock_check = $conn->query("SELECT Med_Qty FROM meds WHERE Med_ID = $medicine_id");
        $medicine = $stock_check->fetch_assoc();
        
        if ($medicine && $medicine['Med_Qty'] >= $quantity) {
            $_SESSION['pos_items'][$item_key]['quantity'] = $quantity;
            set_flash_message("Cart updated.", "success");
        } else {
            set_flash_message("Insufficient stock. Available: " . $medicine['Med_Qty'] . " units.", "error");
        }
    }
    
    header("Location: pos_cart.php");
    exit();
}

// Handle complete sale
if (isset($_POST['complete_sale'])) {
    if (!empty($_SESSION['pos_items'])) {
        $total_amount = 0;
        foreach ($_SESSION['pos_items'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        // Get employee ID from session
        $employee_id = $_SESSION['user'] ?? 1;
        
        // Insert into sales table
        $sales_sql = "INSERT INTO sales (S_Date, S_Time, Total_Amt, C_ID, E_ID) 
                       VALUES (CURDATE(), CURTIME(), $total_amount, $customer_id, $employee_id)";
        
        if ($conn->query($sales_sql)) {
            $sale_id = $conn->insert_id;
            
            // Insert items into sales_items table
            foreach ($_SESSION['pos_items'] as $item) {
                $med_id = $item['id'];
                $quantity = $item['quantity'];
                $total_price = $item['price'] * $quantity;
                
                $items_sql = "INSERT INTO sales_items (Med_ID, Sale_ID, Sale_Qty, Tot_Price) 
                              VALUES ($med_id, $sale_id, $quantity, $total_price)";
                $conn->query($items_sql);
            }
            
            // Clear cart and customer
            unset($_SESSION['pos_items']);
            unset($_SESSION['pos_customer_id']);
            
            set_flash_message("Sale completed successfully! Sale ID: #$sale_id", "success");
            header("Location: receipt.php?sale_id=$sale_id");
            exit();
        } else {
            set_flash_message("Error completing sale. Please try again.", "error");
        }
    } else {
        set_flash_message("Cart is empty. Please add items to complete sale.", "error");
    }
}

// Get medicines for dropdown
$medicines = $conn->query("SELECT Med_ID, Med_Name, Med_Price, Med_Qty FROM meds WHERE Med_Qty > 0 ORDER BY Med_Name");

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['pos_items'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Cart - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sales Cart</h2>
            <p class="text-slate-500 mt-1 font-medium">
                Customer: <span class="font-bold text-blue-600"><?php echo htmlspecialchars($customer_info['C_Fname'] . ' ' . $customer_info['C_Lname']); ?></span>
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="pos_new.php" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Change Customer
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Items Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Add Medicine Form -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Medicine to Cart
                </h3>
                
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Medicine</label>
                            <select name="medicine_id" id="medicine_id" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 appearance-none">
                                <option value="0">Select Medicine</option>
                                <?php while($medicine = $medicines->fetch_assoc()): ?>
                                    <option value="<?php echo $medicine['Med_ID']; ?>" 
                                            data-price="<?php echo $medicine['Med_Price']; ?>"
                                            data-stock="<?php echo $medicine['Med_Qty']; ?>">
                                        <?php echo htmlspecialchars($medicine['Med_Name']); ?> (Stock: <?php echo $medicine['Med_Qty']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Quantity</label>
                            <input type="number" name="quantity" id="quantity" min="1" required
                                   placeholder="1" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Price</label>
                            <input type="text" id="price_display" readonly
                                   placeholder="Rs. 0.00" 
                                   class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-5 py-4 text-slate-600">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_to_cart" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add to Cart
                    </button>
                </form>
            </div>

            <!-- Cart Items -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Cart Items (<?php echo count($_SESSION['pos_items']); ?>)</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Medicine</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($_SESSION['pos_items'])): ?>
                                <?php foreach ($_SESSION['pos_items'] as $key => $item): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-600">Rs. <?php echo number_format($item['price'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="flex items-center space-x-2">
                                                <input type="hidden" name="item_key" value="<?php echo $key; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" 
                                                       class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm">
                                                <button type="submit" name="update_quantity" 
                                                        class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="?remove=<?php echo $key; ?>" 
                                               onclick="return confirm('Remove this item from cart?')" 
                                               class="text-red-600 hover:text-red-800 font-bold text-sm">
                                                Remove
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                        Cart is empty. Add medicines to continue.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Order Summary</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Subtotal</span>
                        <span class="font-bold text-slate-900">Rs. <?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Tax (0%)</span>
                        <span class="font-bold text-slate-900">Rs. 0.00</span>
                    </div>
                    
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-slate-900">Total</span>
                            <span class="text-2xl font-black text-blue-600">Rs. <?php echo number_format($cart_total, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($_SESSION['pos_items'])): ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="mt-6">
                        <button type="submit" name="complete_sale" 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-200 transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Complete Sale
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <h4 class="font-bold text-slate-900 mb-4">Quick Actions</h4>
                <div class="space-y-2">
                    <button onclick="clearCart()" class="w-full text-left bg-slate-50 hover:bg-slate-100 text-slate-700 px-4 py-3 rounded-xl transition-all flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear Cart
                    </button>
                    <a href="view.php" class="block bg-slate-50 hover:bg-slate-100 text-slate-700 px-4 py-3 rounded-xl transition-all flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        View Sales History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update price display when medicine is selected
        document.getElementById('medicine_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const stock = selectedOption.getAttribute('data-stock');
            
            document.getElementById('price_display').value = price ? 'Rs. ' + parseFloat(price).toFixed(2) : 'Rs. 0.00';
            
            // Set max quantity based on stock
            document.getElementById('quantity').max = stock || 1;
        });

        // Clear cart function
        function clearCart() {
            if (confirm('Are you sure you want to clear the cart?')) {
                window.location.href = '?clear_cart=1';
            }
        }
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
