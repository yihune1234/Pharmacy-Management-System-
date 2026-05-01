# PHARMACIA - System Flows & Workflows

## 🔄 Complete System Workflows

### 1. USER AUTHENTICATION FLOW

```
┌─────────────────────────────────────────────────────────────┐
│                    LOGIN PROCESS                            │
└─────────────────────────────────────────────────────────────┘

User Visits: index.php
    ↓
Redirects to: modules/auth/login.php
    ↓
┌─────────────────────────────────────────┐
│  Login Form Displayed                   │
│  - Username field                       │
│  - Password field                       │
│  - Submit button                        │
└─────────────────────────────────────────┘
    ↓
User Enters Credentials
    ↓
Form Submitted (POST)
    ↓
┌─────────────────────────────────────────┐
│  Validation                             │
│  - Check if fields empty                │
│  - Trim whitespace                      │
└─────────────────────────────────────────┘
    ↓
Query Database:
SELECT e.E_ID, e.E_Fname, e.E_Username, 
       e.E_Password, r.role_name
FROM employee e
LEFT JOIN roles r ON e.role_id = r.role_id
WHERE e.E_Username = ?
    ↓
┌─────────────────────────────────────────┐
│  Verify Password                        │
│  password_verify($input, $hash)         │
└─────────────────────────────────────────┘
    ↓
    ├─ NO ──→ Set Error Message
    │         Display Login Form Again
    │
    └─ YES ──→ Create Session Variables:
               $_SESSION['user'] = E_ID
               $_SESSION['username'] = E_Username
               $_SESSION['name'] = E_Fname
               $_SESSION['role'] = role_name
               $_SESSION['last_activity'] = time()
                ↓
            Role-Based Redirect:
            ├─ admin → modules/admin/dashboard.php
            ├─ pharmacist → modules/pharmacist/dashboard.php
            └─ cashier → modules/cashier/dashboard.php
                ↓
            ✅ LOGIN SUCCESSFUL
```

**Session Timeout:** 30 minutes of inactivity
**Password Hashing:** Argon2ID (PASSWORD_DEFAULT)
**Security:** Prepared statements, no SQL injection

---

### 2. STOCK ENTRY FLOW (Purchase)

```
┌─────────────────────────────────────────────────────────────┐
│              PURCHASE/STOCK ENTRY PROCESS                   │
└─────────────────────────────────────────────────────────────┘

Admin Navigates to: modules/admin/purchases/add_new.php
    ↓
┌─────────────────────────────────────────┐
│  Purchase Form Displayed                │
│  - Select Supplier                      │
│  - Select Medicine                      │
│  - Enter Quantity                       │
│  - Enter Cost Price                     │
│  - Enter Batch Number                   │
│  - Enter Mfg Date                       │
│  - Enter Exp Date                       │
└─────────────────────────────────────────┘
    ↓
Admin Fills Form & Submits
    ↓
┌─────────────────────────────────────────┐
│  Validation                             │
│  - Check all fields                     │
│  - Validate dates                       │
│  - Check supplier exists                │
│  - Check medicine exists                │
└─────────────────────────────────────────┘
    ↓
    ├─ VALIDATION FAILS → Show Error
    │
    └─ VALIDATION PASSES ↓
    
┌─────────────────────────────────────────┐
│  Database Operations (Transaction)      │
└─────────────────────────────────────────┘
    ↓
Step 1: Insert into medicine_batches
INSERT INTO medicine_batches 
(Med_ID, Batch_Number, Batch_Qty, Mfg_Date, Exp_Date, Supplier_ID, Cost_Price)
VALUES (?, ?, ?, ?, ?, ?, ?)
    ↓
Step 2: Insert into purchase
INSERT INTO purchase 
(Med_ID, Sup_ID, Batch_ID, P_Qty, P_Cost, Pur_Date, Payment_Status)
VALUES (?, ?, ?, ?, ?, ?, 'Pending')
    ↓
Step 3: TRIGGER FIRES → trg_after_purchase_insert
UPDATE meds SET Med_Qty = Med_Qty + NEW.P_Qty
WHERE Med_ID = NEW.Med_ID
    ↓
Step 4: Log Activity
INSERT INTO activity_logs 
(user_id, action, description, ip_address, user_agent)
VALUES (?, 'purchase_created', ?, ?, ?)
    ↓
┌─────────────────────────────────────────┐
│  Stock Updated Successfully             │
│  - meds.Med_Qty increased               │
│  - Batch tracked                        │
│  - Purchase recorded                    │
│  - Activity logged                      │
└─────────────────────────────────────────┘
    ↓
Redirect to: modules/admin/purchases/view_new.php
    ↓
✅ PURCHASE COMPLETE
```

**Tables Modified:** medicine_batches, purchase, meds, activity_logs
**Triggers Fired:** 1 (trg_after_purchase_insert)
**Stock Impact:** +P_Qty units

---

### 3. SALES/POS FLOW (Multi-Step)

```
┌─────────────────────────────────────────────────────────────┐
│              POINT OF SALE (POS) PROCESS                    │
└─────────────────────────────────────────────────────────────┘

Cashier/Pharmacist Clicks: "Launch POS"
    ↓
Redirects to: modules/[role]/sales/pos1.php
    ↓
┌─────────────────────────────────────────┐
│  STEP 1: CUSTOMER SELECTION             │
│  - Display customer list                │
│  - Search customer                      │
│  - Option to add new customer           │
└─────────────────────────────────────────┘
    ↓
User Selects Customer (or creates new)
    ↓
Store in Session: $_SESSION['selected_customer']
    ↓
Redirect to: modules/[role]/sales/pos2.php
    ↓
┌─────────────────────────────────────────┐
│  STEP 2: MEDICINE SELECTION             │
│  - Display medicine list                │
│  - Show stock levels                    │
│  - Show prices                          │
│  - Search medicines                     │
│  - Add to cart button                   │
└─────────────────────────────────────────┘
    ↓
User Selects Medicines & Quantities
    ↓
┌─────────────────────────────────────────┐
│  STEP 3: CART MANAGEMENT                │
│  - Display cart items                   │
│  - Show subtotal                        │
│  - Show total                           │
│  - Remove item option                   │
│  - Modify quantity option               │
│  - Proceed to checkout                  │
└─────────────────────────────────────────┘
    ↓
User Clicks: "Proceed to Checkout"
    ↓
┌─────────────────────────────────────────┐
│  STEP 4: VALIDATION                     │
│  - Check stock availability             │
│  - Validate quantities                  │
│  - Check customer exists                │
│  - Calculate totals                     │
└─────────────────────────────────────────┘
    ↓
    ├─ VALIDATION FAILS → Show Error, Return to Cart
    │
    └─ VALIDATION PASSES ↓

┌─────────────────────────────────────────┐
│  STEP 5: DATABASE TRANSACTION           │
└─────────────────────────────────────────┘
    ↓
Transaction Start
    ↓
Step 5a: Insert into sales
INSERT INTO sales 
(S_Date, S_Time, Total_Amt, C_ID, E_ID, Refunded)
VALUES (CURDATE(), CURTIME(), ?, ?, ?, FALSE)
    ↓
Get Sale_ID from INSERT
    ↓
Step 5b: For Each Item in Cart:
INSERT INTO sales_items 
(Med_ID, Sale_ID, Sale_Qty, Tot_Price)
VALUES (?, ?, ?, ?)
    ↓
Step 5c: TRIGGER FIRES → trg_after_sales_items_insert
UPDATE meds SET Med_Qty = Med_Qty - NEW.Sale_Qty
WHERE Med_ID = NEW.Med_ID
    ↓
Step 5d: Update Customer Loyalty Points
UPDATE customer 
SET Loyalty_Points = Loyalty_Points + (Total_Amt / 10)
WHERE C_ID = ?
    ↓
Step 5e: Log Activity
INSERT INTO activity_logs 
(user_id, action, description, ip_address, user_agent)
VALUES (?, 'sale_created', ?, ?, ?)
    ↓
Transaction Commit
    ↓
┌─────────────────────────────────────────┐
│  STEP 6: RECEIPT GENERATION             │
│  - Display receipt                      │
│  - Show sale details                    │
│  - Show items purchased                 │
│  - Show total amount                    │
│  - Print option                         │
│  - Email option                         │
└─────────────────────────────────────────┘
    ↓
User Clicks: "Print" or "Email"
    ↓
┌─────────────────────────────────────────┐
│  SALE COMPLETE                          │
│  - Stock decreased                      │
│  - Sale recorded                        │
│  - Loyalty points added                 │
│  - Activity logged                      │
│  - Receipt generated                    │
└─────────────────────────────────────────┘
    ↓
✅ TRANSACTION SUCCESSFUL
```

**Tables Modified:** sales, sales_items, meds, customer, activity_logs
**Triggers Fired:** 1 per item (trg_after_sales_items_insert)
**Stock Impact:** -Sale_Qty units per item
**Loyalty Impact:** +Points (Total_Amt / 10)

---

### 4. REFUND FLOW

```
┌─────────────────────────────────────────────────────────────┐
│              REFUND PROCESSING                              │
└─────────────────────────────────────────────────────────────┘

Cashier/Admin Navigates to: modules/admin/sales/refunds.php
    ↓
┌─────────────────────────────────────────┐
│  STEP 1: SELECT SALE TO REFUND          │
│  - Display recent sales                 │
│  - Search by sale ID                    │
│  - Search by customer                   │
│  - Show sale details                    │
└─────────────────────────────────────────┘
    ↓
User Selects Sale
    ↓
┌─────────────────────────────────────────┐
│  STEP 2: REFUND DETAILS                 │
│  - Show original sale amount            │
│  - Show items purchased                 │
│  - Enter refund reason                  │
│  - Select items to refund               │
│  - Calculate refund amount              │
└─────────────────────────────────────────┘
    ↓
User Enters Refund Details & Submits
    ↓
┌─────────────────────────────────────────┐
│  STEP 3: VALIDATION                     │
│  - Check sale exists                    │
│  - Check not already refunded           │
│  - Validate refund amount               │
│  - Check reason provided                │
└─────────────────────────────────────────┘
    ↓
    ├─ VALIDATION FAILS → Show Error
    │
    └─ VALIDATION PASSES ↓

┌─────────────────────────────────────────┐
│  STEP 4: DATABASE TRANSACTION           │
└─────────────────────────────────────────┘
    ↓
Transaction Start
    ↓
Step 4a: Insert into refunds
INSERT INTO refunds 
(Sale_ID, Refund_Amount, Refund_Reason, Employee_ID)
VALUES (?, ?, ?, ?)
    ↓
Step 4b: Update sales record
UPDATE sales 
SET Refunded = TRUE, Refund_Reason = ?, Refund_Date = NOW()
WHERE Sale_ID = ?
    ↓
Step 4c: Reverse Stock for Each Item
For each item in original sale:
UPDATE meds SET Med_Qty = Med_Qty + Sale_Qty
WHERE Med_ID = ?
    ↓
Step 4d: Reverse Loyalty Points
UPDATE customer 
SET Loyalty_Points = Loyalty_Points - (Refund_Amount / 10)
WHERE C_ID = ?
    ↓
Step 4e: Log Activity
INSERT INTO activity_logs 
(user_id, action, description, ip_address, user_agent)
VALUES (?, 'refund_processed', ?, ?, ?)
    ↓
Transaction Commit
    ↓
┌─────────────────────────────────────────┐
│  REFUND COMPLETE                        │
│  - Stock reversed                       │
│  - Refund recorded                      │
│  - Loyalty points reversed              │
│  - Activity logged                      │
│  - Payment reversed                     │
└─────────────────────────────────────────┘
    ↓
✅ REFUND SUCCESSFUL
```

**Tables Modified:** refunds, sales, meds, customer, activity_logs
**Stock Impact:** +Sale_Qty units (reversed)
**Loyalty Impact:** -Points (reversed)

---

### 5. INVENTORY ALERT FLOW

```
┌─────────────────────────────────────────────────────────────┐
│              ALERT MONITORING SYSTEM                        │
└─────────────────────────────────────────────────────────────┘

System Continuously Monitors:
    ↓
┌─────────────────────────────────────────┐
│  CHECK 1: LOW STOCK ALERT               │
│  Query: SELECT * FROM view_low_stock    │
│  Condition: Med_Qty <= Min_Stock_Level  │
└─────────────────────────────────────────┘
    ↓
    ├─ Items Found → Create Alert
    │
    └─ No Items → Continue
    ↓
┌─────────────────────────────────────────┐
│  CHECK 2: EXPIRY ALERT                  │
│  Query: SELECT * FROM view_expiry_alerts│
│  Condition: Exp_Date <= NOW() + 30 days │
└─────────────────────────────────────────┘
    ↓
    ├─ Items Found → Create Alert
    │
    └─ No Items → Continue
    ↓
┌─────────────────────────────────────────┐
│  ALERT ACTIONS                          │
│  - Display on Dashboard                 │
│  - Log in alerts table                  │
│  - Sound notification                   │
│  - Email notification (optional)        │
│  - SMS notification (optional)          │
└─────────────────────────────────────────┘
    ↓
Admin/Pharmacist Views: modules/admin/alerts/alerts.php
    ↓
┌─────────────────────────────────────────┐
│  ALERT DASHBOARD                        │
│  - List all active alerts               │
│  - Show alert type                      │
│  - Show affected items                  │
│  - Show severity level                  │
│  - Clear alert button                   │
│  - Take action button                   │
└─────────────────────────────────────────┘
    ↓
User Takes Action:
├─ Low Stock → Create Purchase Order
├─ Expiry → Remove from Stock
└─ Clear → Mark Alert as Resolved
    ↓
✅ ALERT HANDLED
```

**Tables Queried:** meds, medicine_batches, views
**Alert Types:** Low Stock, Expiry, System Health
**Notification Methods:** Dashboard, Sound, Email, SMS

---

### 6. REPORTING FLOW

```
┌─────────────────────────────────────────────────────────────┐
│              REPORTING & ANALYTICS                          │
└─────────────────────────────────────────────────────────────┘

Admin Navigates to: modules/admin/reports/reports_dashboard.php
    ↓
┌─────────────────────────────────────────┐
│  REPORT SELECTION                       │
│  - Sales Report                         │
│  - Stock Report                         │
│  - Expiry Report                        │
│  - Supplier Report                      │
│  - Employee Report                      │
│  - Customer Report                      │
└─────────────────────────────────────────┘
    ↓
User Selects Report Type
    ↓
┌─────────────────────────────────────────┐
│  FILTER OPTIONS                         │
│  - Date Range                           │
│  - Department/Category                  │
│  - Supplier/Customer                    │
│  - Employee                             │
│  - Status                               │
└─────────────────────────────────────────┘
    ↓
User Applies Filters & Clicks "Generate"
    ↓
┌─────────────────────────────────────────┐
│  DATA AGGREGATION                       │
│  - Query database with filters          │
│  - Calculate metrics                    │
│  - Group data                           │
│  - Sort results                         │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  REPORT GENERATION                      │
│  - Display table view                   │
│  - Generate charts                      │
│  - Calculate totals                     │
│  - Show trends                          │
└─────────────────────────────────────────┘
    ↓
User Options:
├─ View on Screen
├─ Export to PDF
├─ Export to Excel
├─ Print
└─ Email
    ↓
    ├─ PDF Export → modules/admin/reports/export_pdf.php
    │
    └─ Other → Download/Print
    ↓
✅ REPORT GENERATED
```

**Report Types:** Sales, Stock, Expiry, Supplier, Employee, Customer
**Export Formats:** PDF, Excel, Print, Email
**Metrics:** Totals, Averages, Trends, Comparisons

---

## 📊 Dashboard Data Flow

### Admin Dashboard Data Collection

```
Admin Visits: modules/admin/dashboard.php
    ↓
┌─────────────────────────────────────────┐
│  QUERY 1: Total Medicines               │
│  SELECT COUNT(*) FROM meds              │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 2: Low Stock Items               │
│  SELECT COUNT(*) FROM meds              │
│  WHERE Med_Qty <= 10                    │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 3: Today's Sales Count           │
│  SELECT COUNT(*) FROM sales             │
│  WHERE S_Date = CURDATE()               │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 4: Today's Revenue               │
│  SELECT SUM(Total_Amt) FROM sales       │
│  WHERE S_Date = CURDATE()               │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 5: Total Customers               │
│  SELECT COUNT(*) FROM customer          │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 6: Total Employees               │
│  SELECT COUNT(*) FROM employee          │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 7: Total Suppliers               │
│  SELECT COUNT(*) FROM suppliers         │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 8: Top 5 Medicines               │
│  SELECT m.Med_Name, SUM(si.Sale_Qty),   │
│         SUM(si.Tot_Price)               │
│  FROM meds m                            │
│  JOIN sales_items si ON m.Med_ID = ...  │
│  WHERE s.S_Date >= DATE_SUB(...)        │
│  GROUP BY m.Med_ID                      │
│  ORDER BY total_sold DESC LIMIT 5       │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 9: Monthly Revenue Trend         │
│  SELECT DATE_FORMAT(S_Date, '%Y-%m'),   │
│         COUNT(*), SUM(Total_Amt)        │
│  FROM sales                             │
│  WHERE S_Date >= DATE_SUB(...)          │
│  GROUP BY month                         │
│  ORDER BY month ASC                     │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  QUERY 10: Recent Sales                 │
│  SELECT s.Sale_ID, s.S_Date,            │
│         c.C_Fname, e.E_Fname,           │
│         s.Total_Amt                     │
│  FROM sales s                           │
│  LEFT JOIN customer c ON ...            │
│  LEFT JOIN employee e ON ...            │
│  ORDER BY s.Sale_ID DESC LIMIT 10       │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  DASHBOARD RENDERED                     │
│  - KPI Cards (4 metrics)                │
│  - Revenue Chart (6-month trend)        │
│  - Top Medicines (5 items)              │
│  - Recent Transactions (10 items)       │
└─────────────────────────────────────────┘
    ↓
✅ DASHBOARD LOADED
```

---

## 🔐 Security Flow

```
┌─────────────────────────────────────────────────────────────┐
│              SECURITY VALIDATION FLOW                       │
└─────────────────────────────────────────────────────────────┘

Every Page Load:
    ↓
┌─────────────────────────────────────────┐
│  STEP 1: SESSION CHECK                  │
│  require_once 'session_check.php'       │
└─────────────────────────────────────────┘
    ↓
    ├─ Session Not Started → Start Session
    │
    └─ Session Started → Continue
    ↓
┌─────────────────────────────────────────┐
│  STEP 2: AUTHENTICATION CHECK           │
│  is_logged_in()                         │
│  Check: isset($_SESSION['user'])        │
└─────────────────────────────────────────┘
    ↓
    ├─ NOT LOGGED IN → Redirect to Login
    │
    └─ LOGGED IN → Continue
    ↓
┌─────────────────────────────────────────┐
│  STEP 3: SESSION TIMEOUT CHECK          │
│  check_session_timeout()                │
│  Timeout: 30 minutes                    │
└─────────────────────────────────────────┘
    ↓
    ├─ TIMEOUT EXCEEDED → Logout & Redirect
    │
    └─ WITHIN TIMEOUT → Update Activity
    ↓
┌─────────────────────────────────────────┐
│  STEP 4: ROLE VALIDATION                │
│  validate_role_area($area)              │
│  Check: User role matches area          │
└─────────────────────────────────────────┘
    ↓
    ├─ ROLE MISMATCH → Deny Access
    │
    └─ ROLE MATCH → Continue
    ↓
┌─────────────────────────────────────────┐
│  STEP 5: SPECIFIC PERMISSION CHECK      │
│  require_admin()                        │
│  require_pharmacist()                   │
│  require_cashier()                      │
└─────────────────────────────────────────┘
    ↓
    ├─ PERMISSION DENIED → Redirect
    │
    └─ PERMISSION GRANTED → Load Page
    ↓
✅ ACCESS GRANTED
```

---

## 📈 Data Consistency Flow

```
┌─────────────────────────────────────────────────────────────┐
│              DATA CONSISTENCY MAINTENANCE                   │
└─────────────────────────────────────────────────────────────┘

Database Triggers Ensure Consistency:
    ↓
┌─────────────────────────────────────────┐
│  TRIGGER 1: Purchase Insert             │
│  Event: INSERT INTO purchase             │
│  Action: UPDATE meds SET Med_Qty += ...  │
│  Purpose: Auto-increase stock            │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  TRIGGER 2: Sales Items Insert          │
│  Event: INSERT INTO sales_items          │
│  Action: UPDATE meds SET Med_Qty -= ...  │
│  Purpose: Auto-decrease stock            │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  FOREIGN KEY CONSTRAINTS                │
│  - employee.role_id → roles.role_id     │
│  - sales.C_ID → customer.C_ID           │
│  - sales.E_ID → employee.E_ID           │
│  - sales_items.Med_ID → meds.Med_ID     │
│  - sales_items.Sale_ID → sales.Sale_ID  │
│  - purchase.Med_ID → meds.Med_ID        │
│  - purchase.Sup_ID → suppliers.Sup_ID   │
│  - refunds.Sale_ID → sales.Sale_ID      │
│  Purpose: Referential integrity         │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  AUDIT LOGGING                          │
│  - All changes logged to audit_log      │
│  - Stores old and new values            │
│  - Records user and timestamp           │
│  - Enables rollback if needed           │
└─────────────────────────────────────────┘
    ↓
✅ DATA CONSISTENCY MAINTAINED
```

---

## 🎯 Summary of All Flows

| Flow | Start | End | Tables | Triggers | Key Action |
|------|-------|-----|--------|----------|-----------|
| Authentication | Login Page | Dashboard | employee, roles | None | Session creation |
| Stock Entry | Purchase Form | Inventory | purchase, meds, batches | 1 | Stock increase |
| Sales | POS Start | Receipt | sales, sales_items, meds | 1 | Stock decrease |
| Refund | Refund Form | Confirmation | refunds, sales, meds | None | Stock reverse |
| Alerts | System Check | Dashboard | meds, batches, views | None | Notification |
| Reporting | Report Form | Report View | All tables | None | Data aggregation |

