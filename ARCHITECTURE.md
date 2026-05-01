# PHARMACIA - System Architecture

## 🏗️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     CLIENT LAYER (Browser)                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  HTML5 + Tailwind CSS + JavaScript + Chart.js           │   │
│  │  Responsive Design (Mobile, Tablet, Desktop)            │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              ↓ HTTP/HTTPS
┌─────────────────────────────────────────────────────────────────┐
│                   WEB SERVER LAYER (Apache/Nginx)               │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  PHP 7.4+ / 8.0+                                         │   │
│  │  Session Management                                      │   │
│  │  Request Routing                                         │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                  APPLICATION LAYER (PHP)                        │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Authentication Module (modules/auth/)                  │   │
│  │  ├─ Login validation                                    │   │
│  │  ├─ Session creation                                   │   │
│  │  └─ Logout handling                                    │   │
│  │                                                          │   │
│  │  Role-Based Modules:                                    │   │
│  │  ├─ Admin (modules/admin/)                             │   │
│  │  ├─ Pharmacist (modules/pharmacist/)                   │   │
│  │  └─ Cashier (modules/cashier/)                         │   │
│  │                                                          │   │
│  │  Core Features:                                         │   │
│  │  ├─ Inventory Management                               │   │
│  │  ├─ Sales/POS System                                   │   │
│  │  ├─ Purchase Management                                │   │
│  │  ├─ Customer Management                                │   │
│  │  ├─ Employee Management                                │   │
│  │  ├─ Supplier Management                                │   │
│  │  ├─ Reporting & Analytics                              │   │
│  │  └─ Alert System                                       │   │
│  │                                                          │   │
│  │  Security Layer:                                        │   │
│  │  ├─ Session validation (session_check.php)             │   │
│  │  ├─ CSRF protection (security.php)                     │   │
│  │  ├─ Input validation (security.php)                    │   │
│  │  ├─ Activity logging (activity_logger.php)             │   │
│  │  └─ Flash messages (alerts.php)                        │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              ↓ SQL Queries
┌─────────────────────────────────────────────────────────────────┐
│                   DATABASE LAYER (MySQL/MariaDB)                │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Core Tables (13):                                       │   │
│  │  ├─ roles, employee, customer, meds                     │   │
│  │  ├─ suppliers, sales, sales_items                       │   │
│  │  ├─ medicine_batches, purchase, refunds                 │   │
│  │  ├─ activity_logs, audit_log                            │   │
│  │                                                          │   │
│  │  Database Views (4):                                    │   │
│  │  ├─ view_daily_sales                                   │   │
│  │  ├─ view_low_stock                                     │   │
│  │  ├─ view_expiry_alerts                                 │   │
│  │  └─ view_sales_details                                 │   │
│  │                                                          │   │
│  │  Triggers (2):                                          │   │
│  │  ├─ trg_after_purchase_insert (stock increase)         │   │
│  │  └─ trg_after_sales_items_insert (stock decrease)      │   │
│  │                                                          │   │
│  │  Constraints:                                           │   │
│  │  ├─ Foreign Keys (referential integrity)               │   │
│  │  ├─ Unique Constraints (no duplicates)                 │   │
│  │  └─ Primary Keys (unique identification)               │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📁 Directory Structure

```
Pharmacy-Management-System-/
│
├── config/                          # Configuration files
│   ├── config.php                  # Database connection
│   ├── security.php                # Security functions
│   └── ca.pem                      # SSL certificate (for cloud DB)
│
├── database/                        # Database setup
│   ├── install.php                 # Installation script
│   ├── populate_sample_data.php    # Sample data
│   ├── ER_Diagram.png              # Entity relationship diagram
│   ├── RelationalModel.png         # Relational model
│   └── installed.lock              # Installation lock file
│
├── includes/                        # Shared includes
│   ├── session_check.php           # Session validation middleware
│   ├── alerts.php                  # Flash message system
│   └── activity_logger.php         # Activity logging
│
├── modules/                         # Main application modules
│   ├── auth/                       # Authentication
│   │   ├── login.php              # Login page
│   │   ├── logout.php             # Logout handler
│   │   └── installed.lock         # Auth lock file
│   │
│   ├── admin/                      # Admin panel (full access)
│   │   ├── dashboard.php          # Admin dashboard
│   │   ├── sidebar.php            # Navigation sidebar
│   │   ├── database_setup.php     # Database setup
│   │   │
│   │   ├── inventory/             # Inventory management
│   │   │   ├── view.php          # List medicines
│   │   │   ├── add.php           # Add medicine
│   │   │   ├── edit.php          # Edit medicine
│   │   │   ├── delete.php        # Delete medicine
│   │   │   ├── update.php        # Update stock
│   │   │   └── alerts.php        # Low stock alerts
│   │   │
│   │   ├── sales/                 # Sales & POS
│   │   │   ├── pos_new.php       # Main POS interface
│   │   │   ├── pos1.php          # Customer selection
│   │   │   ├── pos2.php          # Medicine selection
│   │   │   ├── pos_cart.php      # Cart management
│   │   │   ├── receipt.php       # Invoice generation
│   │   │   ├── view_new.php      # Sales history
│   │   │   ├── delete.php        # Cancel sales
│   │   │   ├── refunds.php       # Refund processing
│   │   │   └── items_view.php    # Sales items detail
│   │   │
│   │   ├── purchases/             # Purchase management
│   │   │   ├── view_new.php      # Purchase orders
│   │   │   ├── add_new.php       # Create purchase
│   │   │   ├── delete.php        # Cancel purchase
│   │   │   ├── invoice.php       # Purchase invoice
│   │   │   ├── supplier_report.php
│   │   │   └── get_medicine_stock.php
│   │   │
│   │   ├── customers/             # Customer management
│   │   │   ├── view_new.php      # Customer list
│   │   │   ├── add_new.php       # Register customer
│   │   │   ├── edit_new.php      # Update customer
│   │   │   ├── delete.php        # Delete customer
│   │   │   ├── customer_history.php
│   │   │   ├── customer_invoices.php
│   │   │   └── loyalty_report.php
│   │   │
│   │   ├── employees/             # Employee management
│   │   │   ├── view_new.php      # Staff list
│   │   │   ├── add_new.php       # Hire employee
│   │   │   ├── edit_new.php      # Update employee
│   │   │   ├── delete.php        # Remove employee
│   │   │   ├── change_password.php
│   │   │   ├── activity_logs.php
│   │   │   └── salary_tracking.php
│   │   │
│   │   ├── suppliers/             # Supplier management
│   │   │   ├── view_new.php      # Supplier list
│   │   │   ├── add_new.php       # Register supplier
│   │   │   ├── edit_new.php      # Update supplier
│   │   │   ├── delete.php        # Delete supplier
│   │   │   ├── balance_report.php
│   │   │   └── supplier_purchases.php
│   │   │
│   │   ├── reports/               # Reporting & analytics
│   │   │   ├── reports_dashboard.php
│   │   │   ├── sales_report.php
│   │   │   ├── stock_report.php
│   │   │   ├── expiry_report.php
│   │   │   └── export_pdf.php
│   │   │
│   │   └── alerts/                # Alert system
│   │       ├── alerts.php        # Alert dashboard
│   │       ├── AlertSystem.php   # Alert logic
│   │       ├── clear_alerts.php  # Clear alerts
│   │       └── test_alert.php    # Test alerts
│   │
│   ├── pharmacist/                # Pharmacist panel (limited access)
│   │   ├── dashboard.php         # Pharmacist dashboard
│   │   ├── dashboard_secure.php  # Secure dashboard
│   │   ├── sidebar.php           # Navigation
│   │   │
│   │   ├── inventory/
│   │   │   └── view.php         # Read-only inventory
│   │   │
│   │   ├── sales/
│   │   │   ├── pos1.php         # Customer selection
│   │   │   ├── pos2.php         # Medicine selection
│   │   │   ├── delete.php       # Delete sale
│   │   │   └── delete_pos.php   # Cancel transaction
│   │   │
│   │   ├── customers/
│   │   │   ├── view.php         # Customer lookup
│   │   │   └── add.php          # Register customer
│   │   │
│   │   └── employees/
│   │       └── change_password.php
│   │
│   └── cashier/                   # Cashier panel (sales only)
│       ├── dashboard.php         # Cashier dashboard
│       ├── sidebar.php           # Navigation
│       │
│       ├── sales/
│       │   ├── pos1.php         # Customer selection
│       │   ├── pos2.php         # Medicine selection
│       │   ├── delete.php       # Delete sale
│       │   └── delete_pos.php   # Cancel transaction
│       │
│       └── customers/
│           ├── view.php         # Customer lookup
│           └── add.php          # Register customer
│
├── assets/                         # Static assets
│   ├── css/                       # Stylesheets
│   │   ├── design-system.css    # Design system
│   │   ├── form.css             # Form styles
│   │   ├── header.css           # Header styles
│   │   ├── login.css            # Login styles
│   │   ├── nav.css              # Navigation styles
│   │   └── table.css            # Table styles
│   │
│   └── images/                    # Images
│       ├── logo.png             # Main logo
│       ├── admin/               # Admin images
│       │   ├── alert.png
│       │   ├── carticon1.png
│       │   └── emp.png
│       ├── common/              # Common images
│       │   ├── inventory.png
│       │   └── moneyicon.png
│       └── pharmacist/          # Pharmacist images
│           └── pharm1.png
│
├── logs/                          # Application logs
│   └── alerts.log               # Alert logs
│
├── Screenshots/                   # UI screenshots
│   ├── admin-*.png              # Admin screenshots
│   ├── pharmacist-*.png         # Pharmacist screenshots
│   └── ...
│
├── .env                          # Environment variables
├── .gitignore                    # Git ignore rules
├── index.php                     # Entry point
├── README.md                     # Project readme
├── SYSTEM_GUIDE.md              # System documentation
├── DEEP_ANALYSIS.md             # Deep analysis
├── SYSTEM_FLOWS.md              # System flows
├── QUICK_REFERENCE.md           # Quick reference
└── ARCHITECTURE.md              # This file
```

---

## 🔐 Security Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                          │
└─────────────────────────────────────────────────────────────┘

Layer 1: NETWORK SECURITY
├─ HTTPS/SSL encryption
├─ Security headers
└─ CORS policies

Layer 2: APPLICATION SECURITY
├─ Authentication
│  ├─ Session-based login
│  ├─ Argon2ID password hashing
│  └─ 30-minute timeout
├─ Authorization
│  ├─ Role-based access control
│  ├─ Area-specific validation
│  └─ Permission checking
├─ Input Validation
│  ├─ Sanitization
│  ├─ Type checking
│  └─ Length validation
└─ CSRF Protection
   ├─ Token generation
   └─ Token verification

Layer 3: DATABASE SECURITY
├─ Prepared Statements
│  └─ Parameterized queries
├─ Foreign Keys
│  └─ Referential integrity
├─ Constraints
│  ├─ Unique constraints
│  └─ Check constraints
└─ Triggers
   └─ Automatic validation

Layer 4: AUDIT & MONITORING
├─ Activity Logging
│  ├─ User actions
│  ├─ IP address
│  └─ Timestamp
├─ Audit Trail
│  ├─ Change tracking
│  ├─ Before/after values
│  └─ User identification
└─ Alert System
   ├─ Low stock alerts
   ├─ Expiry alerts
   └─ System health
```

---

## 🔄 Data Flow Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    DATA FLOW DIAGRAM                        │
└─────────────────────────────────────────────────────────────┘

USER INPUT
    ↓
┌─────────────────────────────────────────┐
│  VALIDATION LAYER                       │
│  - Input sanitization                   │
│  - Type checking                        │
│  - Length validation                    │
│  - Business logic validation            │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  AUTHORIZATION LAYER                    │
│  - Session check                        │
│  - Role validation                      │
│  - Permission check                     │
│  - Area validation                      │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  BUSINESS LOGIC LAYER                   │
│  - Process data                         │
│  - Calculate values                     │
│  - Apply rules                          │
│  - Prepare for storage                  │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  DATABASE LAYER                         │
│  - Prepared statements                  │
│  - Parameterized queries                │
│  - Transaction management               │
│  - Trigger execution                    │
└─────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────┐
│  AUDIT LAYER                            │
│  - Activity logging                     │
│  - Change tracking                      │
│  - Timestamp recording                  │
│  - User identification                  │
└─────────────────────────────────────────┘
    ↓
RESPONSE TO USER
```

---

## 🎯 Module Interaction Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  MODULE INTERACTIONS                        │
└─────────────────────────────────────────────────────────────┘

AUTHENTICATION MODULE
    ↓
    ├─→ ADMIN MODULE
    │   ├─→ Inventory Module
    │   ├─→ Sales Module
    │   ├─→ Purchase Module
    │   ├─→ Customer Module
    │   ├─→ Employee Module
    │   ├─→ Supplier Module
    │   ├─→ Reports Module
    │   └─→ Alerts Module
    │
    ├─→ PHARMACIST MODULE
    │   ├─→ Inventory Module (read-only)
    │   ├─→ Sales Module
    │   └─→ Customer Module
    │
    └─→ CASHIER MODULE
        ├─→ Sales Module
        └─→ Customer Module

SHARED COMPONENTS
├─→ Session Check (all modules)
├─→ Alerts System (all modules)
├─→ Activity Logger (all modules)
└─→ Security Functions (all modules)

DATABASE
├─→ All modules query/update
├─→ Triggers maintain consistency
├─→ Views provide aggregated data
└─→ Audit log tracks changes
```

---

## 📊 Database Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  DATABASE SCHEMA                            │
└─────────────────────────────────────────────────────────────┘

CORE ENTITIES
├─ roles (1)
│  └─ role_id, role_name
│
├─ employee (N)
│  ├─ E_ID (PK)
│  ├─ role_id (FK → roles)
│  └─ E_Username, E_Password, E_Fname, E_Lname, ...
│
├─ customer (N)
│  ├─ C_ID (PK)
│  └─ C_Fname, C_Lname, Loyalty_Points, Loyalty_Tier, ...
│
├─ meds (N)
│  ├─ Med_ID (PK)
│  └─ Med_Name, Med_Qty, Med_Price, Category, ...
│
└─ suppliers (N)
   ├─ Sup_ID (PK)
   └─ Sup_Name, Sup_Add, Credit_Limit, Rating, ...

TRANSACTION ENTITIES
├─ sales (N)
│  ├─ Sale_ID (PK)
│  ├─ C_ID (FK → customer)
│  ├─ E_ID (FK → employee)
│  └─ S_Date, S_Time, Total_Amt, Refunded, ...
│
├─ sales_items (N)
│  ├─ Med_ID (FK → meds)
│  ├─ Sale_ID (FK → sales)
│  └─ Sale_Qty, Tot_Price
│
├─ purchase (N)
│  ├─ P_ID (PK)
│  ├─ Med_ID (FK → meds)
│  ├─ Sup_ID (FK → suppliers)
│  ├─ Batch_ID (FK → medicine_batches)
│  └─ P_Qty, P_Cost, Pur_Date, Payment_Status, ...
│
├─ medicine_batches (N)
│  ├─ Batch_ID (PK)
│  ├─ Med_ID (FK → meds)
│  ├─ Supplier_ID (FK → suppliers)
│  └─ Batch_Number, Batch_Qty, Mfg_Date, Exp_Date, ...
│
└─ refunds (N)
   ├─ Refund_ID (PK)
   ├─ Sale_ID (FK → sales)
   ├─ Employee_ID (FK → employee)
   └─ Refund_Amount, Refund_Reason, Refund_Date, ...

AUDIT ENTITIES
├─ activity_logs (N)
│  ├─ Log_ID (PK)
│  ├─ user_id (FK → employee)
│  └─ action, description, ip_address, user_agent, created_at
│
└─ audit_log (N)
   ├─ Log_ID (PK)
   ├─ user_id (FK → employee)
   └─ table_name, action, record_id, old_values, new_values, ...

VIEWS
├─ view_daily_sales
├─ view_low_stock
├─ view_expiry_alerts
└─ view_sales_details

TRIGGERS
├─ trg_after_purchase_insert (stock increase)
└─ trg_after_sales_items_insert (stock decrease)
```

---

## 🔌 Integration Points

```
┌─────────────────────────────────────────────────────────────┐
│                  INTEGRATION ARCHITECTURE                   │
└─────────────────────────────────────────────────────────────┘

CURRENT INTEGRATIONS
├─ Database (MySQL/MariaDB)
├─ Web Server (Apache/Nginx)
├─ PHP Runtime
├─ Session Storage (File-based)
└─ File System (Logs, Uploads)

POTENTIAL INTEGRATIONS
├─ Payment Gateway
│  └─ Stripe, PayPal, etc.
├─ SMS Gateway
│  └─ Twilio, AWS SNS, etc.
├─ Email Service
│  └─ SendGrid, AWS SES, etc.
├─ Barcode Scanner
│  └─ USB/Bluetooth devices
├─ Accounting Software
│  └─ QuickBooks, Xero, etc.
├─ Mobile App
│  └─ iOS/Android apps
├─ API Endpoints
│  └─ REST API for third-party
├─ Cache Layer
│  └─ Redis, Memcached
├─ Session Storage
│  └─ Redis, Database
└─ Backup Service
   └─ AWS S3, Google Cloud, etc.
```

---

## 🚀 Deployment Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  DEPLOYMENT ARCHITECTURE                    │
└─────────────────────────────────────────────────────────────┘

DEVELOPMENT
├─ Local Machine
├─ XAMPP/WAMP/LAMP
├─ localhost:80
└─ File-based sessions

STAGING
├─ Staging Server
├─ Apache/Nginx
├─ SSL Certificate
├─ Database Backup
└─ Performance Testing

PRODUCTION
├─ Production Server(s)
├─ Load Balancer
├─ SSL/TLS Encryption
├─ Database Replication
├─ Redis Cache
├─ Centralized Logging
├─ Monitoring & Alerts
└─ Automated Backups

SCALABILITY CONSIDERATIONS
├─ Horizontal Scaling
│  ├─ Multiple app servers
│  ├─ Load balancer
│  └─ Shared session storage
├─ Vertical Scaling
│  ├─ Larger server
│  ├─ More RAM
│  └─ Better CPU
├─ Database Scaling
│  ├─ Read replicas
│  ├─ Sharding
│  └─ Caching layer
└─ Performance Optimization
   ├─ Query optimization
   ├─ Indexing
   ├─ Caching
   └─ CDN for static assets
```

---

## 📈 Performance Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  PERFORMANCE OPTIMIZATION                   │
└─────────────────────────────────────────────────────────────┘

CACHING STRATEGY
├─ Database Query Cache
│  └─ Cache frequently accessed data
├─ Session Cache
│  └─ Store session in Redis
├─ Page Cache
│  └─ Cache static pages
└─ API Response Cache
   └─ Cache API responses

DATABASE OPTIMIZATION
├─ Indexing
│  ├─ Primary keys
│  ├─ Foreign keys
│  └─ Frequently queried columns
├─ Query Optimization
│  ├─ Use EXPLAIN
│  ├─ Avoid N+1 queries
│  └─ Use JOINs efficiently
├─ Connection Pooling
│  └─ Reuse database connections
└─ Partitioning
   └─ Partition large tables

APPLICATION OPTIMIZATION
├─ Code Optimization
│  ├─ Minimize loops
│  ├─ Reduce function calls
│  └─ Use efficient algorithms
├─ Asset Optimization
│  ├─ Minify CSS/JS
│  ├─ Compress images
│  └─ Use CDN
└─ Lazy Loading
   ├─ Load data on demand
   └─ Pagination

MONITORING & PROFILING
├─ Query Profiling
│  └─ Identify slow queries
├─ Application Profiling
│  └─ Identify bottlenecks
├─ Server Monitoring
│  ├─ CPU usage
│  ├─ Memory usage
│  └─ Disk I/O
└─ User Experience Monitoring
   ├─ Page load time
   ├─ Response time
   └─ Error rates
```

---

## 🔄 Scalability Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  SCALABILITY DESIGN                         │
└─────────────────────────────────────────────────────────────┘

CURRENT STATE (Single Server)
┌──────────────────────────────┐
│  Web Server (PHP)            │
│  ├─ Application Code         │
│  ├─ Session Storage (File)   │
│  └─ Static Assets            │
└──────────────────────────────┘
         ↓
┌──────────────────────────────┐
│  Database Server (MySQL)     │
│  ├─ All Tables               │
│  ├─ Views                    │
│  └─ Triggers                 │
└──────────────────────────────┘

SCALABLE STATE (Multi-Server)
┌──────────────────────────────┐
│  Load Balancer               │
│  ├─ Distribute traffic       │
│  └─ Health checks            │
└──────────────────────────────┘
         ↓
    ┌────┴────┐
    ↓         ↓
┌────────┐ ┌────────┐
│App 1   │ │App 2   │
│(PHP)   │ │(PHP)   │
└────────┘ └────────┘
    ↓         ↓
    └────┬────┘
         ↓
┌──────────────────────────────┐
│  Session Cache (Redis)       │
│  ├─ Shared sessions          │
│  └─ Fast access              │
└──────────────────────────────┘
         ↓
┌──────────────────────────────┐
│  Database Cluster            │
│  ├─ Primary (Write)          │
│  ├─ Replicas (Read)          │
│  └─ Backup                   │
└──────────────────────────────┘
```

