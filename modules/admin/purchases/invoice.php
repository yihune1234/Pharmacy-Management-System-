<?php
require_once __DIR__ . '/../../../config/config.php';

$purchase_id = $_GET['id'] ?? 0;

if ($purchase_id == 0) {
    die("Invalid Purchase ID");
}

// Get purchase details
$sql = "SELECT p.*, s.Sup_Name, s.Sup_Add, s.Sup_Phno, s.Sup_Mail, m.Med_Name
        FROM purchase p
        LEFT JOIN suppliers s ON p.Sup_ID = s.Sup_ID
        LEFT JOIN meds m ON p.Med_ID = m.Med_ID
        WHERE p.P_ID = $purchase_id";

$result = $conn->query($sql);
$purchase = $result->fetch_assoc();

if (!$purchase) {
    die("Purchase not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice #<?php echo $purchase['P_ID']; ?> - PHARMACIA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .invoice { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .company { font-size: 24px; font-weight: bold; color: #1e40af; }
        .title { font-size: 18px; color: #6b7280; margin-top: 5px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .info-box { background: #f9fafb; padding: 20px; border-radius: 8px; }
        .info-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .info-value { font-size: 16px; font-weight: 600; color: #1f2937; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #3b82f6; color: white; padding: 15px; text-align: left; font-weight: 600; }
        .items-table td { padding: 15px; border-bottom: 1px solid #e5e7eb; }
        .items-table .total-row { font-weight: bold; background: #f9fafb; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; }
        .btn { background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #2563eb; }
        .status { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status.paid { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class="invoice">
        <!-- Header -->
        <div class="header">
            <div class="company">PHARMACIA</div>
            <div class="title">Purchase Invoice</div>
        </div>

        <!-- Invoice Info -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <div class="info-label">Invoice Number</div>
                <div class="info-value">#PUR-<?php echo str_pad($purchase['P_ID'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div>
                <div class="info-label">Date</div>
                <div class="info-value"><?php echo date('F j, Y', strtotime($purchase['Pur_Date'])); ?></div>
            </div>
            <div>
                <span class="status paid">COMPLETED</span>
            </div>
        </div>

        <!-- Supplier & Medicine Info -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Supplier Information</div>
                <div class="info-value"><?php echo htmlspecialchars($purchase['Sup_Name']); ?></div>
                <div style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                    <?php echo htmlspecialchars($purchase['Sup_Add'] ?? 'N/A'); ?><br>
                    Phone: <?php echo htmlspecialchars($purchase['Sup_Phno'] ?? 'N/A'); ?><br>
                    Email: <?php echo htmlspecialchars($purchase['Sup_Mail'] ?? 'N/A'); ?>
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-label">Medicine Information</div>
                <div class="info-value"><?php echo htmlspecialchars($purchase['Med_Name']); ?></div>
                <div style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                    <?php if ($purchase['Mfg_Date']): ?>
                        Mfg Date: <?php echo date('M j, Y', strtotime($purchase['Mfg_Date'])); ?><br>
                    <?php endif; ?>
                    <?php if ($purchase['Exp_Date']): ?>
                        Exp Date: <?php echo date('M j, Y', strtotime($purchase['Exp_Date'])); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Cost per Unit</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($purchase['Med_Name']); ?></td>
                    <td><?php echo $purchase['P_Qty']; ?> units</td>
                    <td>Rs. <?php echo number_format($purchase['P_Cost'], 2); ?></td>
                    <td>Rs. <?php echo number_format($purchase['P_Cost'] * $purchase['P_Qty'], 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Total Amount:</td>
                    <td>Rs. <?php echo number_format($purchase['P_Cost'] * $purchase['P_Qty'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Notes -->
        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong>Note:</strong> This purchase has been automatically recorded and inventory has been updated. Stock levels have been increased by the purchased quantity.
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top: 10px; font-size: 12px;">This is a computer-generated invoice. No signature required.</p>
        </div>

        <!-- Print Button -->
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" class="btn">🖨️ Print Invoice</button>
            <button onclick="window.close()" class="btn">Close</button>
        </div>
    </div>
</body>
</html>
