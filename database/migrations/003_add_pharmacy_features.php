<?php
/**
 * Migration: Add Critical Pharmacy Features
 * - Prescriptions management
 * - Drug interactions database
 * - Payment methods tracking
 * 
 * Run this migration to add new tables for pharmacy operations
 */

require_once __DIR__ . '/../config/config.php';

// Get database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "pharmacy_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];
$success = [];

// =============================
// CREATE PRESCRIPTIONS TABLE
// =============================
$sql_prescriptions = "
CREATE TABLE IF NOT EXISTS prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    prescription_date DATE NOT NULL,
    file_path VARCHAR(255),
    status ENUM('Active', 'Completed', 'Expired', 'Archived') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer(C_ID) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_date (prescription_date),
    INDEX idx_status (status)
)";

if (!$conn->query($sql_prescriptions)) {
    $errors[] = "Prescriptions table: " . $conn->error;
} else {
    $success[] = "Prescriptions table created successfully";
}

// =============================
// CREATE DRUG INTERACTIONS TABLE
// =============================
$sql_interactions = "
CREATE TABLE IF NOT EXISTS drug_interactions (
    interaction_id INT AUTO_INCREMENT PRIMARY KEY,
    med_id_1 INT NOT NULL,
    med_id_2 INT NOT NULL,
    severity ENUM('Low', 'Moderate', 'High', 'Critical') DEFAULT 'Moderate',
    description TEXT NOT NULL,
    recommendation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (med_id_1) REFERENCES meds(Med_ID) ON DELETE CASCADE,
    FOREIGN KEY (med_id_2) REFERENCES meds(Med_ID) ON DELETE CASCADE,
    UNIQUE KEY unique_interaction (med_id_1, med_id_2),
    INDEX idx_severity (severity)
)";

if (!$conn->query($sql_interactions)) {
    $errors[] = "Drug interactions table: " . $conn->error;
} else {
    $success[] = "Drug interactions table created successfully";
}

// =============================
// CREATE PAYMENT METHODS TABLE
// =============================
$sql_payment_methods = "
CREATE TABLE IF NOT EXISTS payment_methods (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    method ENUM('Cash', 'Card', 'Mobile Money', 'Check', 'Credit') DEFAULT 'Cash',
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(100),
    transaction_id VARCHAR(100),
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(Sale_ID) ON DELETE CASCADE,
    INDEX idx_method (method),
    INDEX idx_status (status),
    INDEX idx_sale (sale_id)
)";

if (!$conn->query($sql_payment_methods)) {
    $errors[] = "Payment methods table: " . $conn->error;
} else {
    $success[] = "Payment methods table created successfully";
}

// =============================
// ADD EXPIRY MANAGEMENT COLUMNS TO MEDICINE_BATCHES
// =============================
$sql_check_expiry_col = "SHOW COLUMNS FROM medicine_batches LIKE 'expiry_status'";
$result = $conn->query($sql_check_expiry_col);

if ($result->num_rows == 0) {
    $sql_add_expiry = "
    ALTER TABLE medicine_batches 
    ADD COLUMN expiry_status ENUM('Valid', 'Expiring Soon', 'Expired') DEFAULT 'Valid',
    ADD COLUMN is_fifo_compliant BOOLEAN DEFAULT TRUE,
    ADD INDEX idx_expiry_status (expiry_status),
    ADD INDEX idx_exp_date (Exp_Date)
    ";
    
    if (!$conn->query($sql_add_expiry)) {
        $errors[] = "Adding expiry columns: " . $conn->error;
    } else {
        $success[] = "Expiry management columns added to medicine_batches";
    }
}

// =============================
// CREATE EXPIRY ALERTS VIEW
// =============================
$sql_expiry_view = "
CREATE OR REPLACE VIEW view_expiry_management AS
SELECT 
    m.Med_ID,
    m.Med_Name,
    mb.Batch_ID,
    mb.Batch_Number,
    mb.Batch_Qty,
    mb.Exp_Date,
    DATEDIFF(mb.Exp_Date, CURDATE()) as days_to_expiry,
    CASE 
        WHEN mb.Exp_Date < CURDATE() THEN 'Expired'
        WHEN DATEDIFF(mb.Exp_Date, CURDATE()) <= 30 THEN 'Expiring Soon'
        ELSE 'Valid'
    END as expiry_status,
    mb.is_fifo_compliant,
    mb.Mfg_Date,
    s.Sup_Name as supplier_name
FROM meds m
JOIN medicine_batches mb ON m.Med_ID = mb.Med_ID
LEFT JOIN suppliers s ON mb.Supplier_ID = s.Sup_ID
ORDER BY mb.Exp_Date ASC
";

if (!$conn->query($sql_expiry_view)) {
    $errors[] = "Expiry management view: " . $conn->error;
} else {
    $success[] = "Expiry management view created successfully";
}

// =============================
// CREATE PAYMENT RECONCILIATION VIEW
// =============================
$sql_payment_view = "
CREATE OR REPLACE VIEW view_payment_reconciliation AS
SELECT 
    pm.payment_id,
    pm.sale_id,
    s.S_Date,
    s.Total_Amt,
    pm.method,
    pm.amount,
    pm.reference,
    pm.status,
    c.C_Fname,
    c.C_Lname,
    e.E_Fname,
    e.E_Lname
FROM payment_methods pm
JOIN sales s ON pm.sale_id = s.Sale_ID
LEFT JOIN customer c ON s.C_ID = c.C_ID
LEFT JOIN employee e ON s.E_ID = e.E_ID
ORDER BY pm.created_at DESC
";

if (!$conn->query($sql_payment_view)) {
    $errors[] = "Payment reconciliation view: " . $conn->error;
} else {
    $success[] = "Payment reconciliation view created successfully";
}

// =============================
// CREATE DRUG INTERACTION ALERTS VIEW
// =============================
$sql_interaction_view = "
CREATE OR REPLACE VIEW view_drug_interaction_alerts AS
SELECT 
    di.interaction_id,
    m1.Med_Name as medicine_1,
    m2.Med_Name as medicine_2,
    di.severity,
    di.description,
    di.recommendation
FROM drug_interactions di
JOIN meds m1 ON di.med_id_1 = m1.Med_ID
JOIN meds m2 ON di.med_id_2 = m2.Med_ID
ORDER BY di.severity DESC
";

if (!$conn->query($sql_interaction_view)) {
    $errors[] = "Drug interaction alerts view: " . $conn->error;
} else {
    $success[] = "Drug interaction alerts view created successfully";
}

// =============================
// DISPLAY RESULTS
// =============================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Features Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 4px; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pharmacy Features Migration</h1>
        
        <?php if (!empty($success)): ?>
            <h2>✓ Successful Operations:</h2>
            <?php foreach($success as $msg): ?>
                <div class="success"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <h2>✗ Errors:</h2>
            <?php foreach($errors as $msg): ?>
                <div class="error"><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (empty($errors)): ?>
            <div class="success" style="margin-top: 20px; font-size: 16px;">
                <strong>Migration completed successfully!</strong><br>
                All pharmacy feature tables and views have been created.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>
