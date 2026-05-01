<?php
/**
 * Database Migration: Add Performance Indexes
 * Creates indexes on frequently queried columns and composite indexes for common queries
 * 
 * This migration optimizes query performance by adding strategic indexes
 * Run this migration to improve system performance
 */

require_once __DIR__ . '/../../config/config.php';

$migration_name = '002_add_performance_indexes';
$migration_timestamp = date('Y-m-d H:i:s');

echo "Running migration: $migration_name\n";
echo "Timestamp: $migration_timestamp\n\n";

$errors = [];
$success_count = 0;

// ============================================================================
// MEDS TABLE INDEXES
// ============================================================================

echo "Adding indexes to meds table...\n";

$indexes = [
    "ALTER TABLE meds ADD INDEX idx_med_id (Med_ID)" => "Med_ID index",
    "ALTER TABLE meds ADD INDEX idx_category (Category)" => "Category index",
    "ALTER TABLE meds ADD INDEX idx_barcode (Barcode)" => "Barcode index",
    "ALTER TABLE meds ADD INDEX idx_med_qty (Med_Qty)" => "Med_Qty index",
    "ALTER TABLE meds ADD INDEX idx_min_stock (Min_Stock_Level)" => "Min_Stock_Level index",
    "ALTER TABLE meds ADD INDEX idx_med_name (Med_Name)" => "Med_Name index",
    "ALTER TABLE meds ADD INDEX idx_category_qty (Category, Med_Qty)" => "Composite: Category + Qty",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// SALES TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to sales table...\n";

$indexes = [
    "ALTER TABLE sales ADD INDEX idx_sale_id (Sale_ID)" => "Sale_ID index",
    "ALTER TABLE sales ADD INDEX idx_sale_date (S_Date)" => "S_Date index",
    "ALTER TABLE sales ADD INDEX idx_customer_id (C_ID)" => "Customer_ID index",
    "ALTER TABLE sales ADD INDEX idx_employee_id (E_ID)" => "Employee_ID index",
    "ALTER TABLE sales ADD INDEX idx_total_amt (Total_Amt)" => "Total_Amt index",
    "ALTER TABLE sales ADD INDEX idx_refunded (Refunded)" => "Refunded status index",
    "ALTER TABLE sales ADD INDEX idx_date_customer (S_Date, C_ID)" => "Composite: Date + Customer",
    "ALTER TABLE sales ADD INDEX idx_date_employee (S_Date, E_ID)" => "Composite: Date + Employee",
    "ALTER TABLE sales ADD INDEX idx_date_range (S_Date, Total_Amt)" => "Composite: Date + Amount",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// CUSTOMER TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to customer table...\n";

$indexes = [
    "ALTER TABLE customer ADD INDEX idx_customer_id (C_ID)" => "Customer_ID index",
    "ALTER TABLE customer ADD INDEX idx_customer_name (C_Fname, C_Lname)" => "Composite: First + Last Name",
    "ALTER TABLE customer ADD INDEX idx_loyalty_tier (Loyalty_Tier)" => "Loyalty_Tier index",
    "ALTER TABLE customer ADD INDEX idx_phone (C_Phno)" => "Phone number index",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// EMPLOYEE TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to employee table...\n";

$indexes = [
    "ALTER TABLE employee ADD INDEX idx_employee_id (E_ID)" => "Employee_ID index",
    "ALTER TABLE employee ADD INDEX idx_employee_name (E_Fname, E_Lname)" => "Composite: First + Last Name",
    "ALTER TABLE employee ADD INDEX idx_username (E_Username)" => "Username index",
    "ALTER TABLE employee ADD INDEX idx_role_id (role_id)" => "Role_ID index",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// SALES_ITEMS TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to sales_items table...\n";

$indexes = [
    "ALTER TABLE sales_items ADD INDEX idx_med_id (Med_ID)" => "Med_ID index",
    "ALTER TABLE sales_items ADD INDEX idx_sale_id (Sale_ID)" => "Sale_ID index",
    "ALTER TABLE sales_items ADD INDEX idx_med_sale (Med_ID, Sale_ID)" => "Composite: Med + Sale",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// MEDICINE_BATCHES TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to medicine_batches table...\n";

$indexes = [
    "ALTER TABLE medicine_batches ADD INDEX idx_med_id (Med_ID)" => "Med_ID index",
    "ALTER TABLE medicine_batches ADD INDEX idx_exp_date (Exp_Date)" => "Exp_Date index",
    "ALTER TABLE medicine_batches ADD INDEX idx_batch_number (Batch_Number)" => "Batch_Number index",
    "ALTER TABLE medicine_batches ADD INDEX idx_supplier_id (Supplier_ID)" => "Supplier_ID index",
    "ALTER TABLE medicine_batches ADD INDEX idx_med_exp (Med_ID, Exp_Date)" => "Composite: Med + Expiry",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// PURCHASE TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to purchase table...\n";

$indexes = [
    "ALTER TABLE purchase ADD INDEX idx_med_id (Med_ID)" => "Med_ID index",
    "ALTER TABLE purchase ADD INDEX idx_supplier_id (Sup_ID)" => "Supplier_ID index",
    "ALTER TABLE purchase ADD INDEX idx_purchase_date (Pur_Date)" => "Purchase_Date index",
    "ALTER TABLE purchase ADD INDEX idx_payment_status (Payment_Status)" => "Payment_Status index",
    "ALTER TABLE purchase ADD INDEX idx_supplier_date (Sup_ID, Pur_Date)" => "Composite: Supplier + Date",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// SUPPLIERS TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to suppliers table...\n";

$indexes = [
    "ALTER TABLE suppliers ADD INDEX idx_supplier_id (Sup_ID)" => "Supplier_ID index",
    "ALTER TABLE suppliers ADD INDEX idx_supplier_name (Sup_Name)" => "Supplier_Name index",
    "ALTER TABLE suppliers ADD INDEX idx_status (Status)" => "Status index",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// ACTIVITY_LOGS TABLE INDEXES
// ============================================================================

echo "\nAdding indexes to activity_logs table...\n";

$indexes = [
    "ALTER TABLE activity_logs ADD INDEX idx_user_id (user_id)" => "User_ID index",
    "ALTER TABLE activity_logs ADD INDEX idx_created_at (created_at)" => "Created_At index",
    "ALTER TABLE activity_logs ADD INDEX idx_action (action)" => "Action index",
    "ALTER TABLE activity_logs ADD INDEX idx_user_action (user_id, action)" => "Composite: User + Action",
];

foreach ($indexes as $sql => $description) {
    if ($conn->query($sql)) {
        echo "✓ $description created\n";
        $success_count++;
    } else {
        if (strpos($conn->error, 'Duplicate key name') === false) {
            $error = "✗ Error creating $description: " . $conn->error;
            echo $error . "\n";
            $errors[] = $error;
        } else {
            echo "⊘ $description already exists\n";
            $success_count++;
        }
    }
}

// ============================================================================
// MIGRATION SUMMARY
// ============================================================================

echo "\n" . str_repeat("=", 70) . "\n";
echo "Migration Summary\n";
echo str_repeat("=", 70) . "\n";
echo "Indexes created/verified: $success_count\n";
echo "Errors encountered: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nMigration completed at: " . date('Y-m-d H:i:s') . "\n";

// Record migration in database
$migration_record = "
INSERT INTO migrations (migration_name, executed_at) 
VALUES ('$migration_name', '$migration_timestamp')
ON DUPLICATE KEY UPDATE executed_at = '$migration_timestamp'
";

if ($conn->query($migration_record)) {
    echo "Migration record saved to database.\n";
} else {
    echo "Warning: Could not save migration record: " . $conn->error . "\n";
}

?>
