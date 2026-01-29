<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/alerts.php';

// Test authentication functions
echo "<h1>Authentication System Test</h1>";

// Test 1: Check if user is logged in
echo "<h2>Test 1: Login Status</h2>";
if (is_logged_in()) {
    echo "<p style='color: green;'>✅ User is logged in</p>";
    $user = get_current_user();
    echo "<pre>" . print_r($user, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ User is not logged in</p>";
}

// Test 2: Role validation
echo "<h2>Test 2: Role Validation</h2>";
$role = get_user_role();
echo "<p>Current Role: " . ($role ?: 'None') . "</p>";

if (has_role('admin')) {
    echo "<p style='color: green;'>✅ User has admin role</p>";
} elseif (has_role('pharmacist')) {
    echo "<p style='color: green;'>✅ User has pharmacist role</p>";
} else {
    echo "<p style='color: orange;'>⚠️ User has no recognized role</p>";
}

// Test 3: Session timeout
echo "<h2>Test 3: Session Timeout</h2>";
echo "<p>Last Activity: " . ($_SESSION['last_activity'] ?? 'Not set') . "</p>";
echo "<p>Current Time: " . time() . "</p>";
echo "<p>Timeout: 1800 seconds (30 minutes)</p>";

// Test 4: Area validation
echo "<h2>Test 4: Area Validation</h2>";
echo "<p>Testing admin area validation...</p>";
try {
    validate_role_area('admin');
    echo "<p style='color: green;'>✅ Admin area access validated</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Admin area access denied: " . $e->getMessage() . "</p>";
}

echo "<p>Testing pharmacist area validation...</p>";
try {
    validate_role_area('pharmacist');
    echo "<p style='color: green;'>✅ Pharmacist area access validated</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Pharmacist area access denied: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Complete</h2>";
echo "<p><a href='../auth/login.php'>Go to Login</a></p>";
?>
