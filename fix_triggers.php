<?php
require_once __DIR__ . '/config/config.php';

$queries = [
    "DROP TRIGGER IF EXISTS trg_after_sales_items_update",
    "CREATE TRIGGER trg_after_sales_items_update 
     AFTER UPDATE ON sales_items 
     FOR EACH ROW 
     UPDATE meds SET Med_Qty = Med_Qty - (NEW.Sale_Qty - OLD.Sale_Qty) 
     WHERE Med_ID = NEW.Med_ID",
    
    "DROP TRIGGER IF EXISTS trg_after_sales_items_delete",
    "CREATE TRIGGER trg_after_sales_items_delete 
     AFTER DELETE ON sales_items 
     FOR EACH ROW 
     UPDATE meds SET Med_Qty = Med_Qty + OLD.Sale_Qty 
     WHERE Med_ID = OLD.Med_ID"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Success: $q\n";
    } else {
        echo "Error: " . $conn->error . " in query: $q\n";
    }
}
$conn->close();
?>
