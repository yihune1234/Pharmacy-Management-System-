<?php
require_once __DIR__ . '/../config/config.php';

// Get role IDs
$roles = [];
$role_result = $conn->query('SELECT role_id, role_name FROM roles');
while ($row = $role_result->fetch_assoc()) {
    $roles[$row['role_name']] = $row['role_id'];
}

$accounts = [
    ['admin', 'admin123', 'Admin', 'User', 'Admin'],
    ['pharmacist', 'pharmacist123', 'Pharmacist', 'User', 'Pharmacist'],
    ['cashier', 'cashier123', 'Cashier', 'User', 'Cashier']
];

echo "Creating test accounts...\n";

foreach ($accounts as $acc) {
    // Check if exists
    $check = $conn->query("SELECT E_ID FROM employee WHERE E_Username = '{$acc[0]}'");
    if ($check->num_rows > 0) {
        echo "✓ {$acc[0]} already exists\n";
        continue;
    }
    
    $hash = password_hash($acc[1], PASSWORD_DEFAULT);
    $role_id = $roles[$acc[4]];
    $email = $acc[0] . '@pharmacia.com';
    
    $stmt = $conn->prepare('INSERT INTO employee (E_Username, E_Password, E_Fname, E_Lname, E_Email, role_id) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $acc[0], $hash, $acc[2], $acc[3], $email, $role_id);
    
    if ($stmt->execute()) {
        echo "✓ Created: {$acc[0]}\n";
    } else {
        echo "✗ Error: {$stmt->error}\n";
    }
}

echo "\nTest Accounts:\n";
echo "Admin: admin / admin123\n";
echo "Pharmacist: pharmacist / pharmacist123\n";
echo "Cashier: cashier / cashier123\n";
?>
