# PHARMACIA - Critical Pharmacy Features Implementation Guide

## Overview
This document describes the critical pharmacy features implemented in PHARMACIA, including prescriptions management, drug interactions checking, expiry management with FIFO/FEFO compliance, and payment method tracking.

---

## 1. Prescriptions Management

### Location
- **Module Path**: `modules/admin/prescriptions/`
- **Files**:
  - `prescriptions.php` - List and manage prescriptions
  - `add_prescription.php` - Add/edit prescriptions
  - `view_prescription.php` - View prescription details
  - `delete_prescription.php` - Delete prescriptions

### Database Table
```sql
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    prescription_date DATE NOT NULL,
    file_path VARCHAR(255),
    status ENUM('Active', 'Completed', 'Expired', 'Archived') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer(C_ID) ON DELETE CASCADE
);
```

### Features
- ✅ Upload prescription files (PDF, JPG, PNG, DOC, DOCX)
- ✅ Track prescription status (Active, Completed, Expired, Archived)
- ✅ Link prescriptions to customers
- ✅ Store doctor information
- ✅ Add notes and comments
- ✅ Search and filter prescriptions
- ✅ View prescription details with file download

### Usage
1. Navigate to **Pharmacy → Prescriptions** in the admin sidebar
2. Click **Add Prescription** to create new prescription
3. Select patient, enter doctor name, and upload prescription file
4. Set status and add notes
5. View, edit, or delete prescriptions as needed

---

## 2. Drug Interactions Checker

### Location
- **Module Path**: `modules/admin/drug_interactions/`
- **Files**:
  - `checker.php` - Drug interaction checker interface
  - `check_interaction.php` - Add/edit interactions
  - `interaction_database.php` - Common drug interactions library

### Database Table
```sql
CREATE TABLE drug_interactions (
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
    UNIQUE KEY unique_interaction (med_id_1, med_id_2)
);
```

### Features
- ✅ Check interactions between any two medicines
- ✅ Severity levels: Low, Moderate, High, Critical
- ✅ Pre-loaded common drug interactions database
- ✅ Add custom interactions
- ✅ View interaction statistics
- ✅ Search and filter interactions
- ✅ Recommendations for managing interactions

### Common Interactions Included
- Aspirin + Ibuprofen (High severity)
- Warfarin + Aspirin (Critical severity)
- Metformin + Contrast Dye (High severity)
- ACE Inhibitors + Potassium Supplements (High severity)
- Statins + Fibrates (Moderate severity)
- And 5+ more common interactions

### Usage
1. Navigate to **Pharmacy → Drug Interactions**
2. Select two medicines to check for interactions
3. Click **Check for Interactions**
4. View severity level and recommendations
5. Add new interactions via **Add Interaction** button

### Importing Common Interactions
```php
require_once 'interaction_database.php';
$result = import_common_interactions($conn);
echo "Imported: " . $result['imported'] . " interactions";
```

---

## 3. Inventory Expiry Management with FIFO/FEFO

### Location
- **Module Path**: `modules/admin/inventory/expiry_management.php`

### Database Enhancements
```sql
ALTER TABLE medicine_batches 
ADD COLUMN expiry_status ENUM('Valid', 'Expiring Soon', 'Expired') DEFAULT 'Valid',
ADD COLUMN is_fifo_compliant BOOLEAN DEFAULT TRUE;
```

### Features
- ✅ Real-time expiry status tracking
- ✅ FIFO (First In, First Out) compliance verification
- ✅ FEFO (First Expired, First Out) prioritization
- ✅ 30-day expiry alerts
- ✅ Prevent selling expired medicines
- ✅ Batch-level expiry management
- ✅ Automatic archival of expired stock
- ✅ Expiry statistics dashboard

### Expiry Status Categories
- **Valid**: Expires in more than 30 days
- **Expiring Soon**: Expires within 30 days
- **Expired**: Already expired

### FIFO/FEFO Implementation
The system ensures:
1. Oldest batches (by manufacturing date) are sold first
2. Batches closest to expiry are prioritized
3. Expired medicines cannot be sold
4. Compliance status is tracked per batch

### Usage
1. Navigate to **Pharmacy → Expiry Management**
2. View expiry statistics at the top
3. Check batch details including:
   - Manufacturing date
   - Expiration date
   - Days remaining
   - FIFO compliance status
4. Archive expired batches
5. Monitor near-expiry stock for priority sales

### POS Integration
The Point of Sale system automatically:
- Highlights near-expiry stock
- Prevents selling expired medicines
- Prioritizes FEFO batches for sale

---

## 4. Payment Methods & Reconciliation

### Location
- **Module Path**: `modules/admin/sales/payment_methods.php`

### Database Table
```sql
CREATE TABLE payment_methods (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    method ENUM('Cash', 'Card', 'Mobile Money', 'Check', 'Credit') DEFAULT 'Cash',
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(100),
    transaction_id VARCHAR(100),
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(Sale_ID) ON DELETE CASCADE
);
```

### Supported Payment Methods
1. **Cash** - Direct cash payment
2. **Card** - Credit/Debit card payment
3. **Mobile Money** - Mobile payment services
4. **Check** - Cheque payment
5. **Credit** - Credit account payment

### Features
- ✅ Track payment method per transaction
- ✅ Payment reconciliation by method
- ✅ Daily payment summaries
- ✅ Payment status tracking
- ✅ Transaction reference tracking
- ✅ Date range filtering
- ✅ Payment statistics and analytics
- ✅ Refund tracking

### Payment Reconciliation Views
The system provides:
- **Summary by Payment Method**: Total transactions and amounts per method
- **Daily Payment Summary**: Daily totals and transaction counts
- **Detailed Transactions**: Complete payment transaction history
- **Payment Statistics**: Average transaction value, totals, etc.

### Usage
1. Navigate to **Pharmacy → Payment Methods**
2. View payment statistics dashboard
3. Filter by date range and payment method
4. Review payment summary by method
5. Check daily payment totals
6. View detailed transaction history
7. Track payment status (Completed, Pending, Failed, Refunded)

### Integration with POS
When processing sales:
1. Select payment method at checkout
2. Enter transaction reference (if applicable)
3. System records payment method and amount
4. Payment status tracked automatically
5. Reconciliation data updated in real-time

---

## 5. Database Migration

### Running the Migration
```bash
# Navigate to database migrations folder
cd database/migrations/

# Run the migration script
php 003_add_pharmacy_features.php
```

### What Gets Created
1. **prescriptions** table
2. **drug_interactions** table
3. **payment_methods** table
4. Enhanced **medicine_batches** table with expiry columns
5. **view_expiry_management** - Expiry tracking view
6. **view_payment_reconciliation** - Payment reconciliation view
7. **view_drug_interaction_alerts** - Interaction alerts view

---

## 6. Security & Best Practices

### Prepared Statements
All database operations use prepared statements to prevent SQL injection:
```php
$stmt = $conn->prepare("INSERT INTO prescriptions (customer_id, doctor_name, prescription_date, file_path, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $customer_id, $doctor_name, $prescription_date, $file_path, $status, $notes);
$stmt->execute();
```

### File Upload Security
- Allowed file types: PDF, JPG, PNG, DOC, DOCX
- Files stored in `uploads/prescriptions/` directory
- Unique file naming with timestamp and uniqid
- File validation before upload

### Access Control
- All modules require admin authentication
- Role-based access validation
- Session checking on every page

### Data Validation
- Input sanitization using `real_escape_string()`
- Type casting for numeric values
- Required field validation
- Date format validation

---

## 7. API Integration Points

### Checking Drug Interactions Programmatically
```php
// Check if interaction exists
$stmt = $conn->prepare("
    SELECT * FROM drug_interactions 
    WHERE (med_id_1 = ? AND med_id_2 = ?) 
    OR (med_id_1 = ? AND med_id_2 = ?)
");
$stmt->bind_param("iiii", $med_1, $med_2, $med_2, $med_1);
$stmt->execute();
$result = $stmt->get_result();
$interaction = $result->fetch_assoc();
```

### Getting Expiry Status
```php
// Get all near-expiry batches
$result = $conn->query("
    SELECT * FROM view_expiry_management 
    WHERE expiry_status IN ('Expired', 'Expiring Soon')
    ORDER BY days_to_expiry ASC
");
```

### Payment Reconciliation Query
```php
// Get payment summary by method
$result = $conn->query("
    SELECT method, COUNT(*) as count, SUM(amount) as total
    FROM payment_methods
    WHERE DATE(created_at) = CURDATE()
    GROUP BY method
");
```

---

## 8. Troubleshooting

### Issue: Migration fails
**Solution**: Ensure database connection is working and tables don't already exist

### Issue: File upload not working
**Solution**: Check `uploads/prescriptions/` directory permissions (should be 755)

### Issue: Drug interactions not showing
**Solution**: Run the import_common_interactions() function to populate database

### Issue: Expiry status not updating
**Solution**: Ensure medicine_batches table has the new expiry columns

---

## 9. Future Enhancements

Potential improvements for future versions:
- [ ] Email notifications for expiring medicines
- [ ] Barcode scanning for batch verification
- [ ] Integration with pharmacy management APIs
- [ ] Automated refund processing
- [ ] Advanced payment gateway integration
- [ ] Prescription renewal reminders
- [ ] Drug interaction alerts in POS
- [ ] Batch-level inventory tracking
- [ ] Supplier-specific interaction warnings
- [ ] Payment reconciliation reports

---

## 10. Support & Documentation

For additional help:
- Check SYSTEM_GUIDE.md for general system information
- Review SECURITY_IMPLEMENTATION_SUMMARY.md for security details
- Consult SYSTEM_FLOWS.md for workflow documentation

---

**Last Updated**: 2024
**Version**: 1.0
**Status**: Production Ready
