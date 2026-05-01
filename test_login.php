<?php
require_once 'config/config.php';

echo "=== TEST CREDENTIALS ===\n";
echo "Admin: admin / admin123\n";
echo "Pharmacist: pharmacist / pharmacist123\n";
echo "Cashier: cashier / cashier123\n\n";

// Get available roles
echo "=== AVAILABLE ROLES ===\n";
$roles_result = $conn->query("SELECT role_id, role_name FROM roles");
$roles = [];
while ($row = $roles_result->fetch_assoc()) {
    echo "- {$row['role_name']} (ID: {$row['role_id']})\n";
    $roles[$row['role_name']] = $row['role_id'];
}

echo "\n=== CREATING TEST ACCOUNTS ===\n";

$test_data = [
    ['admin', 'admin123', 'Admin', 'User', 'Admin'],
    ['pharmacist', 'pharmacist123', 'Pharmacist', 'User', 'Pharmacist'],
    ['cashier', 'cashier123', 'Cashier', 'User', 'Cashier']
];

$created = 0;
$skipped = 0;

foreach ($test_data as $data) {
    $username = $data[0];
    $password = $data[1];
    $fname = $data[2];
    $lname = $data[3];
    $role_name = $data[4];
    $email = $username . '@pharmacia.com';
    
    // Check if exists
    $check = $conn->query("SELECT E_ID FROM employee WHERE E_Username = '$username'");
    if ($check->num_rows > 0) {
        echo "⊘ $username already exists\n";
        $skipped++;
        continue;
    }
    
    if (!isset($roles[$role_name])) {
        echo "✗ Role '$role_name' not found\n";
        continue;
    }
    
    $role_id = $roles[$role_name];
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO employee (E_Username, E_Password, E_Fname, E_Lname, E_Email, role_id) VALUES ('$username', '$hashed', '$fname', '$lname', '$email', $role_id)";
    
    if ($conn->query($sql)) {
        echo "✓ Created: $username\n";
        $created++;
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Created: $created\n";
echo "Skipped: $skipped\n";

// Verify
echo "\n=== VERIFICATION ===\n";
$verify = $conn->query("SELECT E_Username, E_Fname, role_id FROM employee WHERE E_Username IN ('admin', 'pharmacist', 'cashier')");
if ($verify->num_rows > 0) {
    while ($row = $verify->fetch_assoc()) {
        echo "✓ {$row['E_Username']} - {$row['E_Fname']} (Role ID: {$row['role_id']})\n";
    }
} else {
    echo "No accounts found\n";
}
?>
