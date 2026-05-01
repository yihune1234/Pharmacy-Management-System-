<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/AlertSystem.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $types = ['info', 'success', 'warning', 'error', 'critical'];
    $rand_type = $types[array_rand($types)];
    
    $messages = [
        'info' => 'System maintenance scheduled for tonight.',
        'success' => 'Backup completed successfully.',
        'warning' => 'High memory usage detected (75%).',
        'error' => 'Failed to connect to secondary database server.',
        'critical' => 'CRITICAL SECURITY ALERT: Suspicious login attempt detected!'
    ];
    
    AlertSystem::addAlert($rand_type, $messages[$rand_type], [
        'generated_by' => 'Test Script',
        'random_value' => rand(100, 999)
    ]);
    
    echo json_encode(['success' => true, 'type' => $rand_type]);
}
?>
