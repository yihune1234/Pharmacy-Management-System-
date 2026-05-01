<?php
/**
 * Add Test Accounts for All Roles
 * This script adds test employee accounts for Admin, Pharmacist, and Cashier roles
 */

require_once __DIR__ . '/../config/config.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    ADDING TEST ACCOUNTS                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Test accounts to add
$test_accounts = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'fname' => 'Admin',
        'lname' => 'User',
        'role' => 'admin',
        'email' => 'admin@pharmacia.com'
    ],
    [
        'username' => 'pharmacist',
        'password' => 'pharmacist123',
        'fname' => 'Pharmacist',
        'lname' => 'User',
        'role' => 'pharmacist',
        'email' => 'pharmacist@pharmacia.com'
    ],
    [
        'username' => 'cashier',
        'password' => 'cashier123',
        'fname' => 'Cashier',
        'lname' => 'User',
        'role' => 'cashier',
        'email' => 'cashier@pharmacia.com'
    ]
];

try {
    // Get role IDs
    $roles = [];
    $role_result = $conn->query("SELECT role_id, role_name FROM roles");
    
    if ($role_result) {
        while ($row = $role_result->fetch_assoc()) {
            $roles[$row['role_name']] = $row['role_id'];
        }
    }
    
    echo "Available Roles:\n";
    foreach ($roles as $role_name => $role_id) {
        echo "  ✓ $role_name (ID: $role_id)\n";
    }
    echo "\n";
    
    // Add test accounts
    $added = 0;
    $skipped = 0;
    
    foreach ($test_accounts as $account) {
        $username = $account['username'];
        $password = $account['password'];
        $fname = $account['fname'];
        $lname = $account['lname'];
        $role_name = $account['role'];
        $email = $account['email'];
        
        // Check if account already exists
        $check_stmt = $conn->prepare("SELECT E_ID FROM employee WHERE E_Username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo "⊘ Skipping '$username' - Account already exists\n";
            $skipped++;
            $check_stmt->close();
            continue;
        }
        $check_stmt->close();
        
        // Get role ID
        if (!isset($roles[$role_name])) {
            echo "✗ Error: Role '$role_name' not found\n";
            continue;
        }
        
        $role_id = $roles[$role_name];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert account
        $insert_stmt = $conn->prepare("
            INSERT INTO employee (E_Username, E_Password, E_Fname, E_Lname, E_Email, role_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $insert_stmt->bind_param("sssssi", $username, $hashed_password, $fname, $lname, $email, $role_id);
        
        if ($insert_stmt->execute()) {
            echo "✓ Added '$username' ($role_name) - Password: $password\n";
            $added++;
        } else {
            echo "✗ Error adding '$username': " . $insert_stmt->error . "\n";
        }
        
        $insert_stmt->close();
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                           SUMMARY                                         ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    echo "Accounts Added: $added\n";
    echo "Accounts Skipped: $skipped\n\n";
    
    // Display all test accounts
    echo "TEST ACCOUNTS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "ADMIN ACCOUNT:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "  Role: Administrator\n";
    echo "  Dashboard: /Pharmacy-Management-System-/modules/admin/dashboard.php\n\n";
    
    echo "PHARMACIST ACCOUNT:\n";
    echo "  Username: pharmacist\n";
    echo "  Password: pharmacist123\n";
    echo "  Role: Pharmacist\n";
    echo "  Dashboard: /Pharmacy-Management-System-/modules/pharmacist/dashboard.php\n\n";
    
    echo "CASHIER ACCOUNT:\n";
    echo "  Username: cashier\n";
    echo "  Password: cashier123\n";
    echo "  Role: Cashier\n";
    echo "  Dashboard: /Pharmacy-Management-System-/modules/cashier/dashboard.php\n\n";
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✓ Test accounts setup complete!\n";
    echo "✓ You can now login with any of these accounts\n";
    echo "✓ Visit: http://localhost/Pharmacy-Management-System-/landing/login.php\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
