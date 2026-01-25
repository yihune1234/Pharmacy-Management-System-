/* =========================================================
   DATABASE
========================================================= */
CREATE DATABASE IF NOT EXISTS pharmacy_db;
USE pharmacy_db;

/* =========================================================
   CUSTOMER
========================================================= */
CREATE TABLE IF NOT EXISTS customer (
    C_ID INT AUTO_INCREMENT PRIMARY KEY,
    C_Fname VARCHAR(50) NOT NULL,
    C_Lname VARCHAR(50) NOT NULL,
    C_Age INT,
    C_Sex ENUM('M','F'),
    C_Phno VARCHAR(20),
    C_Mail VARCHAR(100)
);

/* =========================================================
   EMPLOYEE
========================================================= */
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
    E_Sal DECIMAL(10,2)
);

/* =========================================================
   ADMIN
========================================================= */
CREATE TABLE IF NOT EXISTS admin (
    A_Username VARCHAR(50) PRIMARY KEY,
    A_Password VARCHAR(255) NOT NULL,
    ID INT,
    FOREIGN KEY (ID) REFERENCES employee(E_ID)
        ON DELETE CASCADE
);

/* =========================================================
   EMPLOYEE LOGIN
========================================================= */
CREATE TABLE IF NOT EXISTS emplogin (
    E_Username VARCHAR(50) PRIMARY KEY,
    E_Password VARCHAR(255) NOT NULL,
    E_ID INT,
    FOREIGN KEY (E_ID) REFERENCES employee(E_ID)
        ON DELETE CASCADE
);

/* =========================================================
   MEDS
========================================================= */
CREATE TABLE IF NOT EXISTS meds (
    Med_ID INT AUTO_INCREMENT PRIMARY KEY,
    Med_Name VARCHAR(100) NOT NULL,
    Med_Qty INT NOT NULL DEFAULT 0,
    Category VARCHAR(50),
    Med_Price DECIMAL(10,2) NOT NULL,
    Location_Rack VARCHAR(50)
);

/* =========================================================
   SUPPLIERS
========================================================= */
CREATE TABLE IF NOT EXISTS suppliers (
    Sup_ID INT AUTO_INCREMENT PRIMARY KEY,
    Sup_Name VARCHAR(100) NOT NULL,
    Sup_Add VARCHAR(150),
    Sup_Phno VARCHAR(20),
    Sup_Mail VARCHAR(100)
);

/* =========================================================
   SALES
========================================================= */
CREATE TABLE IF NOT EXISTS sales (
    Sale_ID INT AUTO_INCREMENT PRIMARY KEY,
    S_Date DATE NOT NULL,
    S_Time TIME NOT NULL,
    Total_Amt DECIMAL(10,2) NOT NULL,
    C_ID INT,
    E_ID INT,
    FOREIGN KEY (C_ID) REFERENCES customer(C_ID),
    FOREIGN KEY (E_ID) REFERENCES employee(E_ID)
);

/* =========================================================
   SALES ITEMS
========================================================= */
CREATE TABLE IF NOT EXISTS sales_items (
    Med_ID INT,
    Sale_ID INT,
    Sale_Qty INT NOT NULL,
    Tot_Price DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (Med_ID, Sale_ID),
    FOREIGN KEY (Med_ID) REFERENCES meds(Med_ID),
    FOREIGN KEY (Sale_ID) REFERENCES sales(Sale_ID)
        ON DELETE CASCADE
);

/* =========================================================
   PURCHASE
========================================================= */
CREATE TABLE IF NOT EXISTS purchase (
    P_ID INT AUTO_INCREMENT PRIMARY KEY,
    Med_ID INT,
    Sup_ID INT,
    P_Qty INT NOT NULL,
    P_Cost DECIMAL(10,2) NOT NULL,
    Pur_Date DATE NOT NULL,
    Mfg_Date DATE,
    Exp_Date DATE,
    FOREIGN KEY (Med_ID) REFERENCES meds(Med_ID),
    FOREIGN KEY (Sup_ID) REFERENCES suppliers(Sup_ID)
);

/* =========================================================
   TRIGGERS
========================================================= */
DELIMITER $$

/* Increase stock after purchase */
CREATE TRIGGER trg_after_purchase_insert
AFTER INSERT ON purchase
FOR EACH ROW
BEGIN
    UPDATE meds
    SET Med_Qty = Med_Qty + NEW.P_Qty
    WHERE Med_ID = NEW.Med_ID;
END$$

/* Decrease stock after sale */
CREATE TRIGGER trg_after_sales_items_insert
AFTER INSERT ON sales_items
FOR EACH ROW
BEGIN
    UPDATE meds
    SET Med_Qty = Med_Qty - NEW.Sale_Qty
    WHERE Med_ID = NEW.Med_ID;
END$$

DELIMITER ;

/* =========================================================
   VIEWS
========================================================= */

/* Daily Sales Summary */
CREATE OR REPLACE VIEW view_daily_sales AS
SELECT
    S_Date AS Sale_Date,
    COUNT(Sale_ID) AS Total_Bills,
    SUM(Total_Amt) AS Total_Sales
FROM sales
GROUP BY S_Date;

/* Low Stock Alert */
CREATE OR REPLACE VIEW view_low_stock AS
SELECT
    Med_ID,
    Med_Name,
    Med_Qty,
    Location_Rack
FROM meds
WHERE Med_Qty <= 10;

/* Detailed Sales Report */
CREATE OR REPLACE VIEW view_sales_details AS
SELECT
    s.Sale_ID,
    s.S_Date,
    c.C_Fname AS Customer_Name,
    e.E_Fname AS Employee_Name,
    m.Med_Name,
    si.Sale_Qty,
    si.Tot_Price
FROM sales s
JOIN customer c ON s.C_ID = c.C_ID
JOIN employee e ON s.E_ID = e.E_ID
JOIN sales_items si ON s.Sale_ID = si.Sale_ID
JOIN meds m ON si.Med_ID = m.Med_ID;
