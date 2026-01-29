<?php
// Test script to check login functionality
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/alerts.php';

echo "<h1>Pharmacy Management System - Login Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    echo "<p>⚠️ Please run the database installer first: <a href='database/install.php'>database/install.php</a></p>";
} else {
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
}

// Test if tables exist
echo "<h2>2. Database Tables Test</h2>";
$tables = ['employee', 'roles', 'meds', 'sales', 'customer'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' missing</p>";
    }
}

// Test if admin user exists
echo "<h2>3. Admin User Test</h2>";
$stmt = $conn->prepare("SELECT e.E_ID, e.E_Fname, e.username, r.role_name FROM employee e LEFT JOIN roles r ON e.role_id = r.role_id WHERE e.username = 'admin'");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Admin user found: " . htmlspecialchars($row['E_Fname']) . " (" . htmlspecialchars($row['username']) . ")</p>";
        echo "<p>Role: " . htmlspecialchars($row['role_name']) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Admin user not found</p>";
    }
}

// Test login logic
echo "<h2>4. Login Logic Test</h2>";
echo "<form method='post' action=''>";
echo "<input type='hidden' name='test_login' value='1'>";
echo "<label>Username: <input type='text' name='uname' value='admin'></label><br><br>";
echo "<label>Password: <input type='password' name='pwd' value='admin123'></label><br><br>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $uname = trim($_POST['uname'] ?? '');
    $password = trim($_POST['pwd'] ?? '');
    
    if ($uname === '' || $password === '') {
        echo "<p style='color: red;'>Please enter both username and password</p>";
    } else {
        $stmt = $conn->prepare("
            SELECT e.E_ID, e.E_Fname, e.username, e.password, r.role_name
            FROM employee e
            LEFT JOIN roles r ON e.role_id = r.role_id
            WHERE e.username = ?
            LIMIT 1
        ");
        
        if ($stmt) {
            $stmt->bind_param("s", $uname);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                if (password_verify($password, $row['password'])) {
                    echo "<p style='color: green;'>✅ Login successful!</p>";
                    echo "<p>Welcome, " . htmlspecialchars($row['E_Fname']) . "</p>";
                    echo "<p>Role: " . htmlspecialchars($row['role_name']) . "</p>";
                    echo "<p>User ID: " . $row['E_ID'] . "</p>";
                } else {
                    echo "<p style='color: red;'>❌ Password verification failed</p>";
                    echo "<p>Entered password: " . htmlspecialchars($password) . "</p>";
                    echo "<p>Stored hash: " . htmlspecialchars($row['password']) . "</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ User not found</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database query failed</p>";
        }
    }
}

// Test session functionality
echo "<h2>5. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'working';
if (isset($_SESSION['test'])) {
    echo "<p style='color: green;'>✅ Sessions are working</p>";
} else {
    echo "<p style='color: red;'>❌ Sessions are not working</p>";
}

echo "<h2>6. Quick Actions</h2>";
echo "<p><a href='database/install.php'>🔧 Run Database Installer</a></p>";
echo "<p><a href='modules/auth/login.php'>🔐 Go to Login Page</a></p>";
echo "<p><a href='modules/admin/dashboard.php'>📊 Admin Dashboard</a></p>";
echo "<p><a href='modules/pharmacist/dashboard.php'>💊 Pharmacist Dashboard</a></p>";

echo "<hr>";
echo "<small><strong>Default Credentials:</strong><br>";
echo "Username: admin<br>";
echo "Password: admin123</small>";
?>
