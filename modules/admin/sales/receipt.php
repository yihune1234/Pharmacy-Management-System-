<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';

$sale_id = $_GET['sale_id'] ?? 0;

if ($sale_id == 0) {
    die("Invalid Sale ID");
}

// Get sale details
$sql = "SELECT s.*, c.C_Fname, c.C_Lname, c.C_Phno, c.C_Mail, e.E_Fname as Employee_Name
        FROM sales s
        LEFT JOIN customer c ON s.C_ID = c.C_ID
        LEFT JOIN employee e ON s.E_ID = e.E_ID
        WHERE s.Sale_ID = $sale_id";

$result = $conn->query($sql);
$sale = $result->fetch_assoc();

if (!$sale) {
    die("Sale not found");
}

// Get sale items
$items_sql = "SELECT si.*, m.Med_Name
             FROM sales_items si
             LEFT JOIN meds m ON si.Med_ID = m.Med_ID
             WHERE si.Sale_ID = $sale_id";

$items_result = $conn->query($items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Receipt #<?php echo $sale_id; ?> - PHARMACIA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .receipt { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; text-align: center; }
        .company { font-size: 28px; font-weight: bold; color: #1e40af; margin-bottom: 5px; }
        .title { font-size: 16px; color: #6b7280; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .info-value { font-size: 16px; font-weight: 600; color: #1f2937; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #3b82f6; color: white; padding: 12px; text-align: left; font-weight: 600; }
        .items-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .items-table .total-row { font-weight: bold; background: #f9fafb; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; }
        .btn { background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .status { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status.completed { background: #d1fae5; color: #065f46; }
        .barcode { font-family: 'Courier New', monospace; font-size: 24px; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company">PHARMACIA</div>
            <div class="title">PHARMACY MANAGEMENT SYSTEM</div>
            <div class="title">Sales Receipt</div>
        </div>

        <!-- Receipt Info -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <div class="info-label">Receipt Number</div>
                <div class="info-value">#SALE-<?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div>
                <div class="info-label">Date & Time</div>
                <div class="info-value"><?php echo date('F j, Y h:i A', strtotime($sale['S_Date'] . ' ' . $sale['S_Time'])); ?></div>
            </div>
            <div>
                <span class="status completed">COMPLETED</span>
            </div>
        </div>

        <!-- Customer & Employee Info -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Customer Information</div>
                <div class="info-value"><?php echo htmlspecialchars($sale['C_Fname'] . ' ' . $sale['C_Lname']); ?></div>
                <div style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                    <?php echo htmlspecialchars($sale['C_Phno'] ?? 'N/A'); ?><br>
                    <?php echo htmlspecialchars($sale['C_Mail'] ?? 'N/A'); ?>
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-label">Processed By</div>
                <div class="info-value"><?php echo htmlspecialchars($sale['Employee_Name'] ?? 'System'); ?></div>
                <div style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                    Employee ID: <?php echo $sale['E_ID']; ?>
                </div>
            </div>
        </div>

        <!-- Barcode -->
        <div class="barcode">
            *<?php echo str_pad($sale_id, 12, '0', STR_PAD_LEFT); ?>*
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items_result && $items_result->num_rows > 0): ?>
                    <?php while($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['Med_Name']); ?></td>
                            <td><?php echo $item['Sale_Qty']; ?></td>
                            <td>Rs. <?php echo number_format($item['Tot_Price'] / $item['Sale_Qty'], 2); ?></td>
                            <td>Rs. <?php echo number_format($item['Tot_Price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Subtotal:</td>
                    <td>Rs. <?php echo number_format($sale['Total_Amt'], 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Tax (0%):</td>
                    <td>Rs. 0.00</td>
                </tr>
                <tr class="total-row" style="background: #3b82f6; color: white;">
                    <td colspan="3" style="text-align: right; font-size: 18px;">TOTAL:</td>
                    <td style="font-size: 18px;">Rs. <?php echo number_format($sale['Total_Amt'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Info -->
        <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <strong>Payment Method:</strong> Cash | <strong>Amount Paid:</strong> Rs. <?php echo number_format($sale['Total_Amt'], 2); ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your purchase!</strong></p>
            <p style="margin-top: 10px; font-size: 12px;">This is a computer-generated receipt. Please keep for your records.</p>
            <p style="margin-top: 5px; font-size: 10px; color: #9ca3af;">PHARMACIA Pharmacy Management System</p>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" class="btn">🖨️ Print Receipt</button>
            <button onclick="window.close()" class="btn">Close</button>
            <a href="pos_new.php" class="btn">New Sale</a>
            <button onclick="processRefund()" class="btn btn-danger">↩️ Process Refund</button>
        </div>
    </div>

    <script>
        function processRefund() {
            if (confirm('Are you sure you want to process a refund for this sale? This will mark the sale as refunded and reverse the inventory changes.')) {
                // In a real implementation, this would:
                // 1. Mark the sale as refunded in the database
                // 2. Create a refund record
                // 3. Reverse the stock changes (add back the quantities)
                // 4. Generate a refund receipt
                
                alert('Refund functionality would be implemented here. This would reverse the sale and restore inventory.');
            }
        }
    </script>
</body>
</html>
