<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$customer_id = $_GET['id'] ?? 0;

if ($customer_id == 0) {
    set_flash_message("Invalid customer ID.", "error");
    header("Location: view_new.php");
    exit();
}

// Get customer details
$customer_sql = "SELECT * FROM customer WHERE C_ID = $customer_id";
$customer_result = $conn->query($customer_sql);
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    set_flash_message("Customer not found.", "error");
    header("Location: view_new.php");
    exit();
}

// Get customer invoices
$invoices_sql = "SELECT s.*, 
                        COUNT(si.Med_ID) as Item_Count,
                        e.E_Fname as Employee_Name
                 FROM sales s
                 LEFT JOIN sales_items si ON s.Sale_ID = si.Sale_ID
                 LEFT JOIN employee e ON s.E_ID = e.E_ID
                 WHERE s.C_ID = $customer_id
                 GROUP BY s.Sale_ID
                 ORDER BY s.S_Date DESC, s.S_Time DESC";

$invoices_result = $conn->query($invoices_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Invoices - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Customer Invoices</h2>
            <p class="text-slate-500 mt-1 font-medium">
                <?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?> - All Sales Invoices
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="customer_history.php?id=<?php echo $customer_id; ?>" class="bg-white text-slate-600 px-6 py-3 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Purchase History
            </a>
            <a href="view_new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Customers
            </a>
        </div>
    </div>

    <!-- Customer Overview -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-8 rounded-3xl border border-purple-200 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <div class="text-sm text-purple-600 font-medium mb-1">Customer</div>
                <div class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></div>
                <div class="text-sm text-slate-600">ID: #<?php echo str_pad($customer['C_ID'], 5, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div>
                <div class="text-sm text-purple-600 font-medium mb-1">Contact</div>
                <div class="text-sm text-slate-900"><?php echo htmlspecialchars($customer['C_Phno']); ?></div>
                <div class="text-sm text-slate-600"><?php echo htmlspecialchars($customer['C_Mail'] ?? 'N/A'); ?></div>
            </div>
            <div>
                <div class="text-sm text-purple-600 font-medium mb-1">Total Invoices</div>
                <div class="text-2xl font-bold text-slate-900"><?php echo $invoices_result ? $invoices_result->num_rows : 0; ?></div>
            </div>
            <div>
                <div class="text-sm text-purple-600 font-medium mb-1">Total Value</div>
                <div class="text-2xl font-bold text-slate-900">Rs. <?php 
                    $total_value = 0;
                    if ($invoices_result) {
                        $invoices_result->data_seek(0);
                        while($invoice = $invoices_result->fetch_assoc()) {
                            $total_value += $invoice['Total_Amt'];
                        }
                        $invoices_result->data_seek(0);
                    }
                    echo number_format($total_value, 0);
                ?></div>
            </div>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-8 py-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Sales Invoices</h3>
            <p class="text-slate-600 mt-2">Complete invoice history with detailed breakdown</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Processed By</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($invoices_result && $invoices_result->num_rows > 0): ?>
                        <?php while($invoice = $invoices_result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-slate-900">#INV-<?php echo str_pad($invoice['Sale_ID'], 6, '0', STR_PAD_LEFT); ?></div>
                                    <div class="text-xs text-slate-400">Sale ID: #<?php echo str_pad($invoice['Sale_ID'], 6, '0', STR_PAD_LEFT); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div><?php echo date('M j, Y', strtotime($invoice['S_Date'])); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo date('h:i A', strtotime($invoice['S_Time'])); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?php echo $invoice['Item_Count']; ?> items
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-900">Rs. <?php echo number_format($invoice['Total_Amt'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($invoice['Employee_Name'] ?? 'System'); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($invoice['Refunded']): ?>
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">Refunded</span>
                                    <?php else: ?>
                                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button onclick="viewInvoice(<?php echo $invoice['Sale_ID']; ?>)" 
                                            class="text-purple-600 hover:text-purple-800 font-bold text-sm mr-3">
                                        View
                                    </button>
                                    <button onclick="printInvoice(<?php echo $invoice['Sale_ID']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                        Print
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                No invoices found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div id="invoiceModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900">Invoice Details</h3>
                <button onclick="closeInvoiceModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="invoiceContent">
                <!-- Invoice content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function viewInvoice(saleId) {
            // Load invoice details into modal
            document.getElementById('invoiceContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto mb-4"></div>
                    <p class="text-slate-600">Loading invoice details...</p>
                </div>
            `;
            
            fetch(`../sales/receipt.php?sale_id=${saleId}`)
                .then(response => response.text())
                .then(html => {
                    // Extract the receipt content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const receipt = doc.querySelector('.receipt');
                    if (receipt) {
                        document.getElementById('invoiceContent').innerHTML = receipt.innerHTML;
                    }
                })
                .catch(error => {
                    document.getElementById('invoiceContent').innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-red-600">Error loading invoice details</p>
                        </div>
                    `;
                });
            
            document.getElementById('invoiceModal').classList.remove('hidden');
        }
        
        function printInvoice(saleId) {
            window.open(`../sales/receipt.php?sale_id=${saleId}`, '_blank', 'width=800,height=600');
        }
        
        function closeInvoiceModal() {
            document.getElementById('invoiceModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('invoiceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInvoiceModal();
            }
        });
    </script>

    <!-- Closing tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>
