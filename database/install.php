<?php
/* Prevent reinstallation */
if (file_exists("installed.lock")) {
    header("Location: index.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pharmacy_db";

/* Connect to MySQL server */
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =============================
   CREATE DATABASE
============================= */
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

/* =============================
   CREATE ROLES TABLE
============================= */
$conn->query("
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
)");

/* =============================
   CREATE EMPLOYEE TABLE
============================= */
$conn->query("
CREATE TABLE IF NOT EXISTS employee (
    E_ID INT AUTO_INCREMENT PRIMARY KEY,
    E_Fname VARCHAR(50) NOT NULL,
    E_Lname VARCHAR(50) NOT NULL,
    Bdate DATE,
    E_Age INT,
    E_Sex ENUM('M','F'),
    E_Type VARCHAR(30),
    E_Jdate DATE,
    E_Add VARCHAR(150),
    E_Mail VARCHAR(100),
    E_Phno VARCHAR(20),
    E_Sal DECIMAL(10,2),
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL
)");
 
/* =============================
   CREATE OTHER TABLES
============================= */
$conn->query("
CREATE TABLE IF NOT EXISTS customer (
    C_ID INT AUTO_INCREMENT PRIMARY KEY,
    C_Fname VARCHAR(50) NOT NULL,
    C_Lname VARCHAR(50) NOT NULL,
    C_Age INT,
    C_Sex ENUM('M','F'),
    C_Phno VARCHAR(20),
    C_Mail VARCHAR(100),
    C_Add VARCHAR(150),
    Loyalty_Points INT DEFAULT 0,
    Loyalty_Tier ENUM('Bronze','Silver','Gold') DEFAULT 'Bronze',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("
CREATE TABLE IF NOT EXISTS meds (
    Med_ID INT AUTO_INCREMENT PRIMARY KEY,
    Med_Name VARCHAR(100) NOT NULL,
    Med_Qty INT NOT NULL DEFAULT 0,
    Category VARCHAR(50),
    Med_Price DECIMAL(10,2) NOT NULL,
    Location_Rack VARCHAR(50),
    Barcode VARCHAR(50) UNIQUE,
    Min_Stock_Level INT DEFAULT 10,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("
CREATE TABLE IF NOT EXISTS suppliers (
    Sup_ID INT AUTO_INCREMENT PRIMARY KEY,
    Sup_Name VARCHAR(100) NOT NULL,
    Sup_Add VARCHAR(150),
    Sup_Phno VARCHAR(20),
    Sup_Mail VARCHAR(100),
    Contact_Person VARCHAR(100),
    Payment_Terms VARCHAR(50) DEFAULT 'Net 30',
    Credit_Limit DECIMAL(10,2) DEFAULT 0,
    Rating DECIMAL(2,1) DEFAULT 4.0,
    Status ENUM('Active', 'Inactive', 'Probation', 'Blacklisted') DEFAULT 'Active',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("
CREATE TABLE IF NOT EXISTS sales (
    Sale_ID INT AUTO_INCREMENT PRIMARY KEY,
    S_Date DATE NOT NULL,
    S_Time TIME NOT NULL,
    Total_Amt DECIMAL(10,2) NOT NULL,
    C_ID INT,
    E_ID INT,
    Refunded BOOLEAN DEFAULT FALSE,
    Refund_Reason TEXT,
    Refund_Date TIMESTAMP NULL,
    FOREIGN KEY (C_ID) REFERENCES customer(C_ID),
    FOREIGN KEY (E_ID) REFERENCES employee(E_ID)
)");

$conn->query("
CREATE TABLE IF NOT EXISTS sales_items (
    Med_ID INT,
    Sale_ID INT,
    Sale_Qty INT NOT NULL,
    Tot_Price DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (Med_ID, Sale_ID),
    FOREIGN KEY (Med_ID) REFERENCES meds(Med_ID),
    FOREIGN KEY (Sale_ID) REFERENCES sales(Sale_ID) ON DELETE CASCADE
)");

$conn->query("
CREATE TABLE IF NOT EXISTS medicine_batches (
    Batch_ID INT AUTO_INCREMENT PRIMARY KEY,
    Med_ID INT NOT NULL,
    Batch_Number VARCHAR(50) NOT NULL,
    Batch_Qty INT NOT NULL,
    Mfg_Date DATE NOT NULL,
    Exp_Date DATE NOT NULL,
    Supplier_ID INT,
    Cost_Price DECIMAL(10,2),
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Med_ID) REFERENCES meds(Med_ID) ON DELETE CASCADE,
    FOREIGN KEY (Supplier_ID) REFERENCES suppliers(Sup_ID) ON DELETE SET NULL,
    UNIQUE KEY unique_batch (Med_ID, Batch_Number)
)");

$conn->query("
CREATE TABLE IF NOT EXISTS purchase (
    P_ID INT AUTO_INCREMENT PRIMARY KEY,
    Med_ID INT,
    Sup_ID INT,
    Batch_ID INT,
    P_Qty INT NOT NULL,
    P_Cost DECIMAL(10,2) NOT NULL,
    Pur_Date DATE NOT NULL,
    Payment_Status ENUM('Pending', 'Paid', 'Partial') DEFAULT 'Pending',
    Payment_Date DATE NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Med_ID) REFERENCES meds(Med_ID),
    FOREIGN KEY (Sup_ID) REFERENCES suppliers(Sup_ID),
    FOREIGN KEY (Batch_ID) REFERENCES medicine_batches(Batch_ID) ON DELETE SET NULL
)");

$conn->query("
CREATE TABLE IF NOT EXISTS refunds (
    Refund_ID INT AUTO_INCREMENT PRIMARY KEY,
    Sale_ID INT NOT NULL,
    Refund_Amount DECIMAL(10,2) NOT NULL,
    Refund_Reason TEXT NOT NULL,
    Refund_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Employee_ID INT,
    FOREIGN KEY (Sale_ID) REFERENCES sales(Sale_ID),
    FOREIGN KEY (Employee_ID) REFERENCES employee(E_ID)
)");

$conn->query("
CREATE TABLE IF NOT EXISTS activity_logs (
    Log_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employee(E_ID) ON DELETE SET NULL
)");

$conn->query("
CREATE TABLE IF NOT EXISTS audit_log (
    Log_ID INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    user_id INT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employee(E_ID) ON DELETE SET NULL
)");

/* =============================
   CREATE TRIGGERS
============================= */
$conn->query("DROP TRIGGER IF EXISTS trg_after_purchase_insert");
$conn->query("
CREATE TRIGGER trg_after_purchase_insert
AFTER INSERT ON purchase
FOR EACH ROW
UPDATE meds SET Med_Qty = Med_Qty + NEW.P_Qty
WHERE Med_ID = NEW.Med_ID
");

$conn->query("
CREATE TRIGGER trg_after_sales_items_insert
AFTER INSERT ON sales_items
FOR EACH ROW
UPDATE meds SET Med_Qty = Med_Qty - NEW.Sale_Qty
WHERE Med_ID = NEW.Med_ID
");

/* =============================
   CREATE VIEWS
============================= */
$conn->query("
CREATE OR REPLACE VIEW view_daily_sales AS
SELECT S_Date AS Sale_Date,
COUNT(Sale_ID) AS Total_Bills,
SUM(Total_Amt) AS Total_Sales
FROM sales GROUP BY S_Date
");

$conn->query("
CREATE OR REPLACE VIEW view_low_stock AS
SELECT m.Med_ID, m.Med_Name, m.Med_Qty, m.Location_Rack, m.Min_Stock_Level,
       (SELECT COUNT(*) FROM medicine_batches mb WHERE mb.Med_ID = m.Med_ID AND mb.Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)) as Expiring_Soon_Count
FROM meds m 
WHERE m.Med_Qty <= m.Min_Stock_Level
");

$conn->query("
CREATE OR REPLACE VIEW view_expiry_alerts AS
SELECT m.Med_ID, m.Med_Name, mb.Batch_Number, mb.Exp_Date, mb.Batch_Qty,
       DATEDIFF(mb.Exp_Date, CURDATE()) as Days_To_Expiry,
       CASE 
           WHEN DATEDIFF(mb.Exp_Date, CURDATE()) <= 0 THEN 'Expired'
           WHEN DATEDIFF(mb.Exp_Date, CURDATE()) <= 30 THEN 'Expiring Soon'
           ELSE 'Valid'
       END as Status
FROM meds m
JOIN medicine_batches mb ON m.Med_ID = mb.Med_ID
WHERE mb.Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
ORDER BY mb.Exp_Date ASC
");

$conn->query("
CREATE OR REPLACE VIEW view_sales_details AS
SELECT s.Sale_ID, s.S_Date,
c.C_Fname AS Customer_Name,
e.E_Fname AS Employee_Name,
m.Med_Name, si.Sale_Qty, si.Tot_Price
FROM sales s
JOIN customer c ON s.C_ID = c.C_ID
JOIN employee e ON s.E_ID = e.E_ID
JOIN sales_items si ON s.Sale_ID = si.Sale_ID
JOIN meds m ON si.Med_ID = m.Med_ID
");

/* =============================
   INSERT DEFAULT ROLES
============================= */
$conn->query("
INSERT IGNORE INTO roles (role_id, role_name) VALUES
(1,'Admin'),
(2,'Pharmacist'),
(3,'Cashier')
");

/* =============================
   INSERT DEFAULT ADMIN ACCOUNT
   (password = admin123, hashed)
============================= */
$defaultPassword = password_hash("admin123", PASSWORD_DEFAULT);
$conn->query("
INSERT IGNORE INTO employee (E_Fname, E_Lname, username, password, role_id)
VALUES ('System','Admin','admin','$defaultPassword',1)
");

/* =============================
   CREATE LOCK FILE & REDIRECT
============================= */
file_put_contents("installed.lock","installed");

/* Redirect to index */
header("Location: ./config/config.php");
exit();
?>
