<?php
require_once __DIR__ . '/../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHARMACIA - Sample Data Population</h2>";

// Clear existing data (for fresh start)
echo "<h3>Clearing existing data...</h3>";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DELETE FROM activity_logs");
$conn->query("DELETE FROM audit_log");
$conn->query("DELETE FROM sales_items");
$conn->query("DELETE FROM sales");
$conn->query("DELETE FROM purchase");
$conn->query("DELETE FROM refunds");
$conn->query("DELETE FROM customer");
$conn->query("DELETE FROM employee");
$conn->query("DELETE FROM suppliers");
$conn->query("DELETE FROM meds");
$conn->query("DELETE FROM roles");
$conn->query("DELETE FROM admin");
$conn->query("DELETE FROM emplogin");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "✓ Cleared existing data<br>";

// Insert sample roles
echo "<h3>Inserting roles...</h3>";
$roles = [
    ['Admin', 'System administrator with full access'],
    ['Pharmacist', 'Pharmacy operations and sales'],
    ['Manager', 'Store management and reporting'],
    ['Cashier', 'Point of sale operations'],
    ['Staff', 'General staff operations']
];

foreach ($roles as $role) {
    $conn->query("INSERT INTO roles (role_name, description) VALUES ('{$role[0]}', '{$role[1]}')");
}
echo "✓ Inserted " . count($roles) . " roles<br>";

// Insert admin user
echo "<h3>Inserting admin user...</h3>";
$conn->query("INSERT INTO admin (A_Username, A_Password) VALUES ('admin', 'admin')");
echo "✓ Admin user created (username: admin, password: admin)<br>";

// Insert sample employees
echo "<h3>Inserting employees...</h3>";
$employees = [
    ['John', 'Smith', 'Pharmacist', 75000, 'M', '1990-05-15', '123-456-7890', 'john.smith@pharmacia.com', '123 Main St', 'johnsmith', 'password123'],
    ['Sarah', 'Johnson', 'Manager', 85000, 'F', '1985-08-22', '234-567-8901', 'sarah.johnson@pharmacia.com', '456 Oak Ave', 'sarahjohnson', 'password123'],
    ['Michael', 'Brown', 'Cashier', 45000, 'M', '1992-12-10', '345-678-9012', 'michael.brown@pharmacia.com', '789 Pine Rd', 'michaelbrown', 'password123'],
    ['Emily', 'Davis', 'Pharmacist', 72000, 'F', '1988-03-18', '456-789-0123', 'emily.davis@pharmacia.com', '321 Elm St', 'emilydavis', 'password123'],
    ['David', 'Wilson', 'Staff', 40000, 'M', '1995-07-25', '567-890-1234', 'david.wilson@pharmacia.com', '654 Maple Dr', 'davidwilson', 'password123']
];

foreach ($employees as $emp) {
    $role_id = $emp[2] === 'Pharmacist' ? 2 : ($emp[2] === 'Manager' ? 3 : ($emp[2] === 'Cashier' ? 4 : 5));
    $hashed_password = password_hash($emp[10], PASSWORD_DEFAULT);
    
    $conn->query("INSERT INTO employee (E_Fname, E_Lname, E_Type, E_Sal, E_Sex, E_Bdate, E_Phno, E_Mail, E_Add, E_Username, E_Password, E_Jdate, Password_Changed) 
                  VALUES ('{$emp[0]}', '{$emp[1]}', '{$emp[2]}', {$emp[3]}, '{$emp[4]}', '{$emp[5]}', '{$emp[6]}', '{$emp[7]}', '{$emp[8]}', '{$emp[9]}', '$hashed_password', CURDATE(), 0)");
    
    $emp_id = $conn->insert_id;
    $conn->query("UPDATE employee SET role_id = $role_id WHERE E_ID = $emp_id");
}
echo "✓ Inserted " . count($employees) . " employees<br>";

// Insert sample suppliers
echo "<h3>Inserting suppliers...</h3>";
$suppliers = [
    ['MediCorp Pharmaceuticals', '123 Pharma Blvd, Pharma City', '555-0101', 'info@medicorp.com', 'Dr. Robert Chen', 'Net 30', 100000, 4.5, 'Active'],
    ['HealthPlus Solutions', '456 Health Ave, Wellness Town', '555-0102', 'sales@healthplus.com', 'Maria Rodriguez', 'Net 45', 75000, 4.2, 'Active'],
    ['GlobalMed Supplies', '789 Medical Park, Medicine City', '555-0103', 'orders@globalmed.com', 'James Thompson', 'Net 30', 120000, 4.8, 'Active'],
    ['QuickCare Distributors', '321 Care Street, Careville', '555-0104', 'contact@quickcare.com', 'Lisa Anderson', 'COD', 50000, 3.9, 'Active'],
    ['BioPharma Labs', '654 Bio Road, Biotech City', '555-0105', 'info@biopharma.com', 'Dr. Sarah Kim', 'Net 60', 150000, 4.6, 'Active']
];

foreach ($suppliers as $sup) {
    $conn->query("INSERT INTO suppliers (Sup_Name, Sup_Add, Sup_Phno, Sup_Mail, Contact_Person, Payment_Terms, Credit_Limit, Rating, Status, Created_At) 
                  VALUES ('{$sup[0]}', '{$sup[1]}', '{$sup[2]}', '{$sup[3]}', '{$sup[4]}', '{$sup[5]}', {$sup[6]}, {$sup[7]}, '{$sup[8]}', CURDATE())");
}
echo "✓ Inserted " . count($suppliers) . " suppliers<br>";

// Insert sample medicines
echo "<h3>Inserting medicines...</h3>";
$medicines = [
    ['Paracetamol 500mg', 'Pain Relief', 150, 25.50, 'A1', '2025-12-31', 'Tablet'],
    ['Amoxicillin 500mg', 'Antibiotics', 80, 45.75, 'B2', '2025-06-30', 'Capsule'],
    ['Ibuprofen 400mg', 'Pain Relief', 120, 18.25, 'A2', '2025-10-31', 'Tablet'],
    ['Cetirizine 10mg', 'Allergy', 200, 12.50, 'C1', '2025-08-31', 'Tablet'],
    ['Omeprazole 20mg', 'Gastrointestinal', 60, 35.80, 'D1', '2025-07-31', 'Capsule'],
    ['Metformin 500mg', 'Diabetes', 90, 28.90, 'E1', '2025-09-30', 'Tablet'],
    ['Lisinopril 10mg', 'Cardiovascular', 45, 42.30, 'F1', '2025-05-31', 'Tablet'],
    ['Simvastatin 20mg', 'Cardiovascular', 55, 38.60, 'F2', '2025-11-30', 'Tablet'],
    ['Aspirin 75mg', 'Pain Relief', 300, 8.75, 'A3', '2026-01-31', 'Tablet'],
    ['Vitamin D3 1000IU', 'Supplements', 180, 15.40, 'G1', '2026-02-28', 'Capsule'],
    ['Azithromycin 250mg', 'Antibiotics', 40, 65.20, 'B3', '2025-04-30', 'Tablet'],
    ['Prednisone 5mg', 'Steroids', 25, 22.80, 'H1', '2025-03-31', 'Tablet'],
    ['Warfarin 5mg', 'Anticoagulants', 15, 48.90, 'I1', '2025-02-28', 'Tablet'],
    ['Insulin Glargine', 'Diabetes', 30, 125.50, 'E2', '2025-01-15', 'Injection'],
    ['Salbutamol Inhaler', 'Respiratory', 35, 85.75, 'J1', '2025-06-15', 'Inhaler']
];

foreach ($medicines as $med) {
    $conn->query("INSERT INTO meds (Med_Name, Med_Cat, Med_Qty, Med_Price, Location_Rack, Exp_Date, Med_Type) 
                  VALUES ('{$med[0]}', '{$med[1]}', {$med[2]}, {$med[3]}, '{$med[4]}', '{$med[5]}', '{$med[6]}')");
}
echo "✓ Inserted " . count($medicines) . " medicines<br>";

// Insert sample customers
echo "<h3>Inserting customers...</h3>";
$customers = [
    ['Alice', 'Williams', 35, 'F', '555-1001', 'alice.williams@email.com', '123 Customer St', 250, 'Silver'],
    ['Robert', 'Taylor', 45, 'M', '555-1002', 'robert.taylor@email.com', '456 Client Ave', 180, 'Bronze'],
    ['Jennifer', 'Brown', 28, 'F', '555-1003', 'jennifer.brown@email.com', '789 Patient Rd', 420, 'Gold'],
    ['William', 'Jones', 52, 'M', '555-1004', 'william.jones@email.com', '321 Member Dr', 95, 'Bronze'],
    ['Patricia', 'Garcia', 38, 'F', '555-1005', 'patricia.garcia@email.com', '654 Shopper Ln', 310, 'Silver'],
    ['Richard', 'Miller', 41, 'M', '555-1006', 'richard.miller@email.com', '987 Buyer Way', 150, 'Bronze'],
    ['Linda', 'Davis', 33, 'F', '555-1007', 'linda.davis@email.com', '147 Consumer Blvd', 280, 'Silver'],
    ['Charles', 'Rodriguez', 47, 'M', '555-1008', 'charles.rodriguez@email.com', '258 Patron St', 520, 'Gold'],
    ['Nancy', 'Martinez', 29, 'F', '555-1009', 'nancy.martinez@email.com', '369 Client Ave', 75, 'Bronze'],
    ['Daniel', 'Hernandez', 36, 'M', '555-1010', 'daniel.hernandez@email.com', '741 Shopper Rd', 190, 'Silver']
];

foreach ($customers as $cust) {
    $conn->query("INSERT INTO customer (C_Fname, C_Lname, C_Age, C_Sex, C_Phno, C_Mail, C_Add, Loyalty_Points, Loyalty_Tier, Created_At) 
                  VALUES ('{$cust[0]}', '{$cust[1]}', {$cust[2]}, '{$cust[3]}', '{$cust[4]}', '{$cust[5]}', '{$cust[6]}', {$cust[7]}, '{$cust[8]}', CURDATE())");
}
echo "✓ Inserted " . count($customers) . " customers<br>";

// Insert sample purchases
echo "<h3>Inserting purchases...</h3>";
$purchases = [];
for ($i = 0; $i < 20; $i++) {
    $med_id = rand(1, 15);
    $sup_id = rand(1, 5);
    $qty = rand(20, 200);
    $cost = rand(10, 100) + (rand(0, 99) / 100);
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    
    $conn->query("INSERT INTO purchase (Med_ID, Sup_ID, P_Qty, P_Cost, Pur_Date, Payment_Status, Payment_Date) 
                  VALUES ($med_id, $sup_id, $qty, $cost, '$date', 'Paid', '$date')");
    
    $purchases[] = ['id' => $conn->insert_id, 'med_id' => $med_id, 'qty' => $qty];
}
echo "✓ Inserted 20 purchases<br>";

// Insert sample sales
echo "<h3>Inserting sales...</h3>";
$sales = [];
for ($i = 0; $i < 50; $i++) {
    $emp_id = rand(1, 5);
    $cust_id = rand(1, 10);
    $date = date('Y-m-d', strtotime('-' . rand(1, 30) . ' days'));
    $time = sprintf('%02d:%02d:%02d', rand(8, 20), rand(0, 59), rand(0, 59));
    $total = rand(50, 500) + (rand(0, 99) / 100);
    
    $conn->query("INSERT INTO sales (S_Date, S_Time, Total_Amt, C_ID, E_ID, Refunded) 
                  VALUES ('$date', '$time', $total, $cust_id, $emp_id, 0)");
    
    $sales[] = ['id' => $conn->insert_id, 'total' => $total, 'med_id' => rand(1, 15)];
}
echo "✓ Inserted 50 sales<br>";

// Insert sales items
echo "<h3>Inserting sales items...</h3>";
foreach ($sales as $sale) {
    $qty = rand(1, 10);
    $price = $sale['total'] / $qty;
    
    $conn->query("INSERT INTO sales_items (Sale_ID, Med_ID, Sale_Qty, Tot_price) 
                  VALUES ({$sale['id']}, {$sale['med_id']}, $qty, $price)");
}
echo "✓ Inserted sales items<br>";

// Insert sample activity logs
echo "<h3>Inserting activity logs...</h3>";
$activities = [
    ['LOGIN', 'User logged in successfully'],
    ['ADD_EMPLOYEE', 'Added new employee to system'],
    ['ADD_MEDICINE', 'Added new medicine to inventory'],
    ['PROCESS_SALE', 'Completed sales transaction'],
    ['ADD_CUSTOMER', 'Registered new customer'],
    ['ADD_PURCHASE', 'Recorded new purchase order'],
    ['EDIT_MEDICINE', 'Updated medicine information'],
    ['GENERATE_REPORT', 'Generated system report'],
    ['LOGIN_FAILED', 'Failed login attempt'],
    ['SECURITY_BREACH', 'Security alert triggered']
];

for ($i = 0; $i < 100; $i++) {
    $user_id = rand(1, 5);
    $action = $activities[array_rand($activities)];
    $description = $action[1] . ' - Entry #' . ($i + 1);
    $date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 720) . ' hours'));
    
    $conn->query("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) 
                  VALUES ($user_id, '{$action[0]}', '$description', '192.168.1.' . rand(1, 254), 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '$date')");
}
echo "✓ Inserted 100 activity logs<br>";

// Insert some refunds
echo "<h3>Inserting refunds...</h3>";
for ($i = 0; $i < 5; $i++) {
    $sale_id = $sales[array_rand($sales)]['id'];
    $amount = rand(20, 200) + (rand(0, 99) / 100);
    $reason = ['Customer request', 'Defective product', 'Wrong item', 'Price adjustment'][array_rand([0, 1, 2, 3])];
    
    $conn->query("INSERT INTO refunds (Sale_ID, Refund_Amount, Refund_Reason, Employee_ID) 
                  VALUES ($sale_id, $amount, '$reason', " . rand(1, 5) . ")");
    
    $conn->query("UPDATE sales SET Refunded = 1, Refund_Reason = '$reason', Refund_Date = NOW() WHERE Sale_ID = $sale_id");
}
echo "✓ Inserted 5 refunds<br>";

echo "<h2>✅ Sample Data Population Complete!</h2>";
echo "<h3>Login Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username: admin, password: admin</li>";
echo "<li><strong>Employees:</strong> username: johnsmith, sarahjohnson, michaelbrown, emilydavis, davidwilson (all password: password123)</li>";
echo "</ul>";
echo "<h3>System Statistics:</h3>";
echo "<ul>";
echo "<li>Employees: " . count($employees) . "</li>";
echo "<li>Suppliers: " . count($suppliers) . "</li>";
echo "<li>Medicines: " . count($medicines) . "</li>";
echo "<li>Customers: " . count($customers) . "</li>";
echo "<li>Purchases: 20</li>";
echo "<li>Sales: 50</li>";
echo "<li>Activity Logs: 100</li>";
echo "<li>Refunds: 5</li>";
echo "</ul>";
echo "<p><a href='../modules/auth/login.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
?>
