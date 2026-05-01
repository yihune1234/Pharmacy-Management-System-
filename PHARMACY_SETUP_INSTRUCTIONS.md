# PHARMACIA - Pharmacy Features Setup Instructions

## Quick Start Guide

### Step 1: Run Database Migration

1. Open your browser and navigate to:
   ```
   http://localhost/pharmacy/database/migrations/003_add_pharmacy_features.php
   ```

2. The migration will:
   - Create `prescriptions` table
   - Create `drug_interactions` table
   - Create `payment_methods` table
   - Add expiry management columns to `medicine_batches`
   - Create necessary database views

3. You should see a success message confirming all tables were created

### Step 2: Access New Features from Admin Dashboard

After migration, the following new menu items appear in the admin sidebar:

#### Pharmacy Section
- **Prescriptions** - Manage patient prescriptions
- **Drug Interactions** - Check medicine interactions
- **Expiry Management** - Track medicine expiry dates
- **Payment Methods** - View payment reconciliation

### Step 3: Import Common Drug Interactions (Optional)

To populate the drug interactions database with common interactions:

1. Create a new PHP file in `database/` called `import_interactions.php`:

```php
<?php
require_once 'config/config.php';
require_once 'modules/admin/drug_interactions/interaction_database.php';

$result = import_common_interactions($conn);

echo "Import Results:<br>";
echo "Imported: " . $result['imported'] . " interactions<br>";
echo "Skipped: " . $result['skipped'] . " interactions<br>";
?>
```

2. Run it via browser:
   ```
   http://localhost/pharmacy/database/import_interactions.php
   ```

### Step 4: Create Upload Directory for Prescriptions

1. Create the directory:
   ```bash
   mkdir -p uploads/prescriptions
   chmod 755 uploads/prescriptions
   ```

2. The system will auto-create this directory on first prescription upload if it doesn't exist

### Step 5: Test Each Feature

#### Test Prescriptions
1. Go to **Pharmacy → Prescriptions**
2. Click **Add Prescription**
3. Select a customer, enter doctor name, upload a file
4. Click **Add Prescription**
5. Verify prescription appears in the list

#### Test Drug Interactions
1. Go to **Pharmacy → Drug Interactions**
2. Select two medicines from the dropdowns
3. Click **Check for Interactions**
4. View the interaction details (if exists)
5. Add a new interaction via **Add Interaction** button

#### Test Expiry Management
1. Go to **Pharmacy → Expiry Management**
2. View the expiry statistics dashboard
3. Check batch details and FIFO compliance status
4. Archive expired batches if needed

#### Test Payment Methods
1. Go to **Pharmacy → Payment Methods**
2. Set date range and payment method filters
3. View payment summary and daily totals
4. Check detailed transaction history

---

## File Structure

```
PHARMACIA/
├── modules/admin/
│   ├── prescriptions/
│   │   ├── prescriptions.php
│   │   ├── add_prescription.php
│   │   ├── view_prescription.php
│   │   └── delete_prescription.php
│   ├── drug_interactions/
│   │   ├── checker.php
│   │   ├── check_interaction.php
│   │   └── interaction_database.php
│   ├── inventory/
│   │   └── expiry_management.php
│   └── sales/
│       └── payment_methods.php
├── database/
│   └── migrations/
│       └── 003_add_pharmacy_features.php
├── uploads/
│   └── prescriptions/
└── PHARMACY_FEATURES_GUIDE.md
```

---

## Database Schema Summary

### prescriptions
- Stores patient prescriptions with file uploads
- Links to customer records
- Tracks status and dates

### drug_interactions
- Stores medicine interaction data
- Includes severity levels and recommendations
- Prevents duplicate interactions

### payment_methods
- Tracks payment method per transaction
- Supports 5 payment types
- Includes reconciliation data

### medicine_batches (Enhanced)
- Added `expiry_status` column
- Added `is_fifo_compliant` column
- Enables FIFO/FEFO tracking

---

## Key Features Summary

### ✅ Prescriptions Management
- Upload prescription files
- Track prescription status
- Link to customers
- Search and filter
- View details and download files

### ✅ Drug Interactions
- Check interactions between medicines
- Severity levels (Low, Moderate, High, Critical)
- Pre-loaded common interactions
- Add custom interactions
- View statistics

### ✅ Expiry Management
- Real-time expiry tracking
- FIFO/FEFO compliance
- 30-day expiry alerts
- Prevent selling expired medicines
- Batch archival

### ✅ Payment Methods
- Track payment method per transaction
- Support 5 payment types
- Payment reconciliation
- Daily summaries
- Transaction history

---

## Troubleshooting

### Migration fails with "Table already exists"
- The tables already exist in your database
- This is normal if you've run the migration before
- No action needed

### "uploads/prescriptions" directory error
- Create the directory manually:
  ```bash
  mkdir -p uploads/prescriptions
  chmod 755 uploads/prescriptions
  ```

### Drug interactions not showing
- Run the import_interactions.php script to populate common interactions
- Or add interactions manually via the UI

### Payment methods not tracking
- Ensure payment_methods table was created during migration
- Check that sales are being recorded properly

### Expiry status not updating
- Verify medicine_batches table has the new columns
- Check that batch dates are set correctly

---

## Security Notes

✅ All database operations use prepared statements
✅ File uploads validated by type and size
✅ Admin authentication required for all features
✅ Role-based access control enforced
✅ Input sanitization on all forms
✅ CSRF protection via session validation

---

## Performance Considerations

- Expiry management queries are indexed on Exp_Date
- Drug interactions use unique constraints to prevent duplicates
- Payment reconciliation uses GROUP BY for efficient summaries
- All views are optimized for common queries

---

## Next Steps

1. ✅ Run the migration
2. ✅ Create uploads directory
3. ✅ Import common interactions (optional)
4. ✅ Test each feature
5. ✅ Train staff on new features
6. ✅ Monitor usage and performance

---

## Support

For issues or questions:
- Check PHARMACY_FEATURES_GUIDE.md for detailed documentation
- Review SYSTEM_GUIDE.md for general system information
- Check database logs for SQL errors
- Verify file permissions on uploads directory

---

**Setup Time**: ~5 minutes
**Difficulty**: Easy
**Status**: Ready for Production
