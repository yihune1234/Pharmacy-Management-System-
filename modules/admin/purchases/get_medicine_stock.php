<?php
require_once __DIR__ . '/../../../config/config.php';

$medicine_id = $_GET['id'] ?? 0;

if ($medicine_id > 0) {
    $sql = "SELECT Med_Qty FROM meds WHERE Med_ID = $medicine_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode(['stock' => $row['Med_Qty']]);
        exit();
    }
}

header('Content-Type: application/json');
echo json_encode(['stock' => 0]);
?>
