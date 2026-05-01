<?php
/**
 * Setup Test Accounts for All Roles
 * Access via: http://localhost/Pharmacy-Management-System-/database/setup_test_accounts.php
 */

require_once __DIR__ . '/../config/config.php';

$message = '';
$message_type = '';
$accounts_added = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_accounts'])) {
    // Test accounts to add - using correct role names from database
    $test_accounts = [
        [
            'username' => 'admin',
            'password' => 'admin123',
            'fname' => 'Admin',
            'lname' => 'User',
            'role' => 'Admin',
            'email' => 'admin@pharmacia.com'
        ],
        [
            'username' => 'pharmacist',
            'password' => 'pharmacist123',
            'fname' => 'Pharmacist',
            'lname' => 'User',
            'role' => 'Pharmacist',
            'email' => 'pharmacist@pharmacia.com'
        ],
        [
            'username' => 'cashier',
            'password' => 'cashier123',
            'fname' => 'Cashier',
            'lname' => 'User',
            'role' => 'Cashier',
            'email' => 'cashier@pharmacia.com'
        ]
    ];

    try {
        // Get role IDs - normalize role names for matching
        $roles = [];
        $role_result = $conn->query("SELECT role_id, role_name FROM roles");
        
        if ($role_result) {
            while ($row = $role_result->fetch_assoc()) {
                // Store both original and lowercase versions for flexible matching
                $roles[$row['role_name']] = $row['role_id'];
                $roles[strtolower($row['role_name'])] = $row['role_id'];
            }
        }
        
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
                $accounts_added[] = [
                    'username' => $username,
                    'status' => 'skipped',
                    'message' => 'Account already exists'
                ];
                $skipped++;
                $check_stmt->close();
                continue;
            }
            $check_stmt->close();
            
            // Get role ID
            if (!isset($roles[$role_name])) {
                $accounts_added[] = [
                    'username' => $username,
                    'status' => 'error',
                    'message' => "Role '$role_name' not found"
                ];
                continue;
            }
            
            $role_id = $roles[$role_name];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert account
            $insert_stmt = $conn->prepare("
                INSERT INTO employee (E_Username, E_Password, E_Fname, E_Lname, E_Mail, role_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $insert_stmt->bind_param("sssssi", $username, $hashed_password, $fname, $lname, $email, $role_id);
            
            if ($insert_stmt->execute()) {
                $accounts_added[] = [
                    'username' => $username,
                    'password' => $password,
                    'role' => $role_name,
                    'status' => 'added',
                    'message' => 'Account created successfully'
                ];
                $added++;
            } else {
                $accounts_added[] = [
                    'username' => $username,
                    'status' => 'error',
                    'message' => $insert_stmt->error
                ];
            }
            
            $insert_stmt->close();
        }
        
        $message = "Setup complete! Added: $added, Skipped: $skipped";
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Test Accounts - PHARMACIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2rem;
            font-weight: 900;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1rem;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .accounts-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .accounts-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            color: #555;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-added {
            background: #d4edda;
            color: #155724;
        }
        
        .status-skipped {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .credentials-box strong {
            color: #667eea;
        }
        
        .credentials-box code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #667eea;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> Setup Test Accounts</h1>
            <p>Create test employee accounts for all roles</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($accounts_added)): ?>
            <table class="accounts-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts_added as $account): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($account['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($account['role'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $account['status']; ?>">
                                <?php echo ucfirst($account['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($account['message']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="credentials-box">
                <strong>✓ Test Accounts Ready!</strong><br><br>
                You can now login with these credentials:<br><br>
                <strong>Admin:</strong> <code>admin</code> / <code>admin123</code><br>
                <strong>Pharmacist:</strong> <code>pharmacist</code> / <code>pharmacist123</code><br>
                <strong>Cashier:</strong> <code>cashier</code> / <code>cashier123</code><br><br>
                <a href="/Pharmacy-Management-System-/landing/login.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                    Go to Login Page →
                </a>
            </div>
        <?php else: ?>
            <form method="POST">
                <p style="color: #666; margin-bottom: 20px;">
                    Click the button below to create test accounts for Admin, Pharmacist, and Cashier roles.
                </p>
                <button type="submit" name="setup_accounts" class="button">
                    <i class="fas fa-plus-circle"></i> Create Test Accounts
                </button>
            </form>

            <div class="credentials-box">
                <strong>Test Accounts to be Created:</strong><br><br>
                <strong>Admin:</strong> admin / admin123<br>
                <strong>Pharmacist:</strong> pharmacist / pharmacist123<br>
                <strong>Cashier:</strong> cashier / cashier123
            </div>
        <?php endif; ?>

        <a href="/Pharmacy-Management-System-/landing/index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</body>
</html>
