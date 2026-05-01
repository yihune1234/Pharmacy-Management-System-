# PHARMACIA - Quick Reference Guide

## 🚀 Quick Start

### Default Credentials
```
Username: admin
Password: admin123
```

### Access URLs
```
Login:     http://localhost/Pharmacy-Management-System/
Admin:     http://localhost/Pharmacy-Management-System/modules/admin/dashboard.php
Pharmacist: http://localhost/Pharmacy-Management-System/modules/pharmacist/dashboard.php
Cashier:   http://localhost/Pharmacy-Management-System/modules/cashier/dashboard.php
```

---

## 👥 User Roles & Permissions

### Admin
- ✅ Full system access
- ✅ All CRUD operations
- ✅ Employee management
- ✅ Supplier management
- ✅ Advanced reporting
- ✅ Alert management

### Pharmacist
- ✅ Inventory viewing
- ✅ POS operations
- ✅ Stock inquiries
- ✅ Customer management (limited)
- ❌ Employee management
- ❌ Supplier management

### Cashier
- ✅ POS operations
- ✅ Customer lookup
- ✅ Sales processing
- ❌ Inventory management
- ❌ Employee management
- ❌ Supplier management

---

## 📊 Dashboard Metrics

### Admin Dashboard
| Metric | Query | Purpose |
|--------|-------|---------|
| Stock Assets | COUNT(*) FROM meds | Total medicines |
| Today's Revenue | SUM(Total_Amt) FROM sales WHERE S_Date=TODAY | Daily income |
| Patient Base | COUNT(*) FROM customer | Total customers |
| System Alerts | COUNT(*) FROM meds WHERE Med_Qty<=10 | Low stock items |
| Top Medicines | Top 5 by sales volume | Best sellers |
| Revenue Trend | 6-month aggregation | Growth tracking |

### Pharmacist Dashboard
| Metric | Query | Purpose |
|--------|-------|---------|
| Today's Volume | COUNT(*) FROM sales WHERE S_Date=TODAY | Dispensing count |
| Global Stock | COUNT(*) FROM meds | Total medicines |
| Critical Alert | COUNT(*) FROM meds WHERE Med_Qty<=10 | Low stock items |
| Dispensing Velocity | 7-day trend | Weekly pattern |

### Cashier Dashboard
| Metric | Query | Purpose |
|--------|-------|---------|
| Today's Tickets | COUNT(*) FROM sales WHERE E_ID=ME | Personal sales |
| Terminal Revenue | SUM(Total_Amt) FROM sales WHERE E_ID=ME | Personal income |
| Patients Served | COUNT(DISTINCT C_ID) FROM sales WHERE E_ID=ME | Unique customers |
| Recent Transactions | Last 5 sales by ME | Activity log |

---

## 🗂️ Module Directory

### Admin Modules
```
modules/admin/
├── dashboard.php              → System overview
├── inventory/
│   ├── view.php              → List medicines
│   ├── add.php               → Add medicine
│   ├── edit.php              → Edit medicine
│   ├── delete.php            → Remove medicine
│   └── alerts.php            → Low stock alerts
├── sales/
│   ├── pos_new.php           → Main POS interface
│   ├── pos1.php              → Customer selection
│   ├── pos2.php              → Medicine selection
│   ├── receipt.php           → Invoice generation
│   ├── view_new.php          → Sales history
│   ├── refunds.php           → Refund processing
│   └── delete.php            → Cancel sales
├── purchases/
│   ├── view_new.php          → Purchase orders
│   ├── add_new.php           → Create purchase
│   ├── invoice.php           → Purchase invoice
│   └── supplier_report.php   → Supplier performance
├── customers/
│   ├── view_new.php          → Customer list
│   ├── add_new.php           → Register customer
│   ├── edit_new.php          → Update customer
│   ├── customer_history.php  → Purchase history
│   └── loyalty_report.php    → Loyalty analytics
├── employees/
│   ├── view_new.php          → Staff list
│   ├── add_new.php           → Hire employee
│   ├── edit_new.php          → Update employee
│   ├── activity_logs.php     → Employee activity
│   └── salary_tracking.php   → Salary management
├── suppliers/
│   ├── view_new.php          → Supplier list
│   ├── add_new.php           → Register supplier
│   ├── edit_new.php          → Update supplier
│   └── balance_report.php    → Payment balance
├── reports/
│   ├── reports_dashboard.php → Reports hub
│   ├── sales_report.php      → Sales analytics
│   ├── stock_report.php      → Inventory report
│   ├── expiry_report.php     → Expiry tracking
│   └── export_pdf.php        → PDF export
└── alerts/
    ├── alerts.php            → Alert dashboard
    ├── clear_alerts.php      → Clear alerts
    └── test_alert.php        → Test alerts
```

### Pharmacist Modules
```
modules/pharmacist/
├── dashboard.php             → Clinical overview
├── inventory/
│   └── view.php             → Read-only inventory
├── sales/
│   ├── pos1.php             → Customer selection
│   ├── pos2.php             → Medicine selection
│   └── delete_pos.php       → Cancel transaction
└── customers/
    ├── view.php             → Customer lookup
    └── add.php              → Register customer
```

### Cashier Modules
```
modules/cashier/
├── dashboard.php            → Sales terminal
├── sales/
│   ├── pos1.php            → Customer selection
│   ├── pos2.php            → Medicine selection
│   └── delete_pos.php      → Cancel transaction
└── customers/
    ├── view.php            → Customer lookup
    └── add.php             → Register customer
```

---

## 🗄️ Database Tables

### Core Tables (13)

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| roles | User roles | role_id, role_name |
| employee | Staff members | E_ID, E_Username, E_Password, role_id |
| customer | Patients | C_ID, C_Fname, Loyalty_Points, Loyalty_Tier |
| meds | Medicines | Med_ID, Med_Name, Med_Qty, Med_Price |
| suppliers | Vendors | Sup_ID, Sup_Name, Credit_Limit, Rating |
| sales | Transactions | Sale_ID, S_Date, Total_Amt, C_ID, E_ID |
| sales_items | Line items | Med_ID, Sale_ID, Sale_Qty, Tot_Price |
| medicine_batches | Batch tracking | Batch_ID, Med_ID, Exp_Date, Batch_Qty |
| purchase | Purchase orders | P_ID, Med_ID, Sup_ID, P_Qty, P_Cost |
| refunds | Refund records | Refund_ID, Sale_ID, Refund_Amount |
| activity_logs | User actions | Log_ID, user_id, action, created_at |
| audit_log | Change tracking | Log_ID, table_name, old_values, new_values |

### Database Views (4)

| View | Purpose | Query |
|------|---------|-------|
| view_daily_sales | Daily aggregation | Sales count & total by date |
| view_low_stock | Low stock items | Medicines below min level |
| view_expiry_alerts | Expiry tracking | Medicines expiring in 30 days |
| view_sales_details | Detailed sales | Sales with customer/employee/medicine |

---

## 🔄 Key Workflows

### Purchase Flow
```
Admin → Add Purchase → Select Supplier & Medicine → Enter Qty & Cost
→ Database Insert → Trigger Fires → Stock Increases → Activity Logged
```

### Sales Flow
```
Cashier → Launch POS → Select Customer → Select Medicines → Add to Cart
→ Checkout → Validate Stock → Database Insert → Trigger Fires
→ Stock Decreases → Loyalty Points Added → Receipt Generated
```

### Refund Flow
```
Cashier → Select Sale → Enter Refund Reason → Validate
→ Database Insert → Stock Reversed → Loyalty Points Reversed
→ Activity Logged → Confirmation
```

---

## 🔐 Security Features

### Authentication
- ✅ Session-based login
- ✅ Argon2ID password hashing
- ✅ 30-minute timeout
- ✅ Activity tracking

### Authorization
- ✅ Role-based access control
- ✅ Area-specific validation
- ✅ Permission checking

### Data Protection
- ✅ Prepared statements
- ✅ CSRF protection
- ✅ Input validation
- ✅ Activity logging
- ✅ Audit trail

---

## 📊 Key Queries

### Get Total Sales Today
```sql
SELECT COUNT(*) as count, SUM(Total_Amt) as revenue 
FROM sales 
WHERE S_Date = CURDATE();
```

### Get Low Stock Items
```sql
SELECT * FROM view_low_stock;
```

### Get Expiring Medicines
```sql
SELECT * FROM view_expiry_alerts;
```

### Get Top Selling Medicines
```sql
SELECT m.Med_Name, SUM(si.Sale_Qty) as total_sold, SUM(si.Tot_Price) as revenue 
FROM meds m 
JOIN sales_items si ON m.Med_ID = si.Med_ID 
GROUP BY m.Med_ID 
ORDER BY total_sold DESC LIMIT 5;
```

### Get Customer Purchase History
```sql
SELECT s.Sale_ID, s.S_Date, s.Total_Amt, m.Med_Name, si.Sale_Qty 
FROM sales s 
JOIN sales_items si ON s.Sale_ID = si.Sale_ID 
JOIN meds m ON si.Med_ID = m.Med_ID 
WHERE s.C_ID = ? 
ORDER BY s.S_Date DESC;
```

### Get Employee Activity
```sql
SELECT * FROM activity_logs 
WHERE user_id = ? 
ORDER BY created_at DESC;
```

---

## 🎨 UI Components

### Design System
- **Framework**: Tailwind CSS
- **Icons**: Font Awesome 6.4.0
- **Charts**: Chart.js
- **Fonts**: Outfit (Google Fonts)

### Color Scheme
- **Primary**: Blue (#2563eb)
- **Success**: Emerald (#10b981)
- **Warning**: Amber (#f59e0b)
- **Danger**: Rose (#f43f5e)
- **Neutral**: Slate (#64748b)

### Common Components
- Premium Cards (rounded, shadow, border)
- Stat Icons (colored backgrounds)
- Navigation Links (hover effects)
- Buttons (primary, secondary, gradient)
- Tables (striped, hover effects)
- Forms (input fields, validation)
- Charts (line, bar, pie)

---

## 🔧 Configuration Files

### config/config.php
```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''
DB_NAME = 'pharmacy_db'
DB_PORT = 3306
```

### config/security.php
- CSRF token generation
- Input validation helpers
- Password hashing functions
- Session hardening

### includes/session_check.php
- Session validation
- Role checking
- Timeout management
- Permission validation

### includes/alerts.php
- Flash message system
- Error/success/warning messages

### includes/activity_logger.php
- Activity logging functions
- Audit trail recording

---

## 📱 Responsive Design

### Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

### Grid System
- 1 column on mobile
- 2 columns on tablet
- 3-4 columns on desktop

---

## 🚨 Common Issues & Solutions

### Issue: Login Fails
**Solution**: Check database connection and verify admin user exists
```sql
SELECT * FROM employee WHERE E_Username = 'admin';
```

### Issue: Stock Not Updating
**Solution**: Verify triggers are created
```sql
SHOW TRIGGERS;
```

### Issue: Session Timeout Too Quick
**Solution**: Adjust timeout in session_check.php (default: 1800 seconds)

### Issue: Alerts Not Showing
**Solution**: Check alert system and verify low stock items exist
```sql
SELECT * FROM view_low_stock;
```

---

## 📈 Performance Tips

1. **Database Indexing**: Add indexes on frequently queried columns
2. **Pagination**: Implement pagination for large lists
3. **Caching**: Use Redis for session storage
4. **Query Optimization**: Use EXPLAIN to analyze queries
5. **Image Optimization**: Compress images before upload

---

## 🔄 Maintenance Tasks

### Daily
- Monitor alerts
- Check low stock items
- Review sales reports

### Weekly
- Review employee activity
- Check supplier balances
- Verify inventory accuracy

### Monthly
- Generate financial reports
- Review customer loyalty
- Analyze sales trends
- Check expiry dates

### Quarterly
- Database optimization
- Security audit
- Performance review
- Backup verification

---

## 📞 Support Resources

### Documentation
- README.md - Project overview
- SYSTEM_GUIDE.md - System documentation
- DEEP_ANALYSIS.md - Detailed analysis
- SYSTEM_FLOWS.md - Workflow documentation

### Database
- ER_Diagram.png - Entity relationship diagram
- RelationalModel.png - Relational model diagram

### Screenshots
- Screenshots/ folder - UI screenshots for all roles

---

## 🎯 Next Steps

1. **Setup**: Configure database and run installer
2. **Test**: Login with default credentials
3. **Explore**: Navigate through all modules
4. **Customize**: Adjust settings and preferences
5. **Train**: Teach users how to use system
6. **Monitor**: Track usage and performance
7. **Maintain**: Regular backups and updates

