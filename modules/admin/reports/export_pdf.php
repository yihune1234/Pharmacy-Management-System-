<?php
require_once __DIR__ . '/../../../config/config.php';

function export_to_pdf($data, $filename = 'report_' . date('Y-m-d') . '.pdf') {
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Create new PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false);
    
    // Set document info
    $pdf->SetCreator('PHARMACIA Pharmacy Management System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Pharmacy Management Report - ' . date('F j, Y'));
    
    // Add page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('Arial', '', 12);
    
    // Header
    $pdf->Cell(0, 10, 'PHARMACIA Pharmacy Management System', 0, 0, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 18);
    $pdf->Cell(0, 10, 'Report Generated: ' . date('F j, Y'), 0, 0, 'C');
    $pdf->Ln(15);
    
    // Content based on data type
    if (isset($data['type']) {
        switch ($data['type']) {
            case 'daily_sales':
                $pdf->SetFont('Arial', 14, 'B');
                $pdf->Cell(0, 10, 'Daily Sales Report', 0, 0, 'C');
                $pdf->Ln(15);
                $pdf->SetFont('Arial', '', 12);
                
                if (isset($data['date_from']) {
                    $pdf->Cell(0, 10, 'Period: ' . $data['date_from'] . ' to ' . $data['date_to'], 0, 0, 'C');
                    $pdf->Ln(15);
                }
                
                if (isset($data['sales_data']) {
                    foreach ($data['sales_data'] as $sale) {
                    $pdf->Cell(0, 10, $sale['date'], 0, 0, 'L');
                    $pdf->Cell(30, 10, $sale['bills'], 0, 0, 'L');
                    $pdf->Cell(40, 10, 'Rs. ' . number_format($sale['total']), 0, 0, 'L');
                    $pdf->Ln(10);
                }
                break;
                
            case 'monthly_revenue':
                $pdf->SetFont('Arial', 14, 'B');
                $pdf->Cell(0, 10, 'Monthly Revenue Report', 0, 0, 'C');
                $pdf->Ln(15);
                $pdf->SetFont('Arial', '', 12);
                
                if (isset($data['month']) {
                    $pdf->Cell(0, 10, 'Period: ' . $data['month'], 0, 0, 'C');
                    $pdf->Ln(15);
                }
                
                if (isset($data['revenue_data'])) {
                    foreach ($data['revenue_data'] as $rev) {
                        $pdf->Cell(0, 10, $rev['month'], 0, 0, 'L');
                        $pdf->Cell(0, 10, 'Rs. ' . number_format($rev['revenue']), 0, 0, 'L');
                        $pdf->Ln(10);
                    }
                }
                break;
                
            case 'employee_performance':
                $pdf->SetFont('Arial', 14, 'B');
                $pdf->Cell(0, 10, 'Employee Performance Report', 0, 0, 'C');
                $pdf->Ln(15);
                $pdf->SetFont('Arial', '', 12);
                
                if (isset($data['employees'])) {
                    foreach ($data['employees'] as $emp) {
                        $pdf->Cell(0, 10, $emp['name'], 0, 0, 'L');
                        $pdf->Cell(30, 10, $emp['sales'] . ' sales', 0, 0, 'L');
                        $pdf->Cell(40, 10, 'Rs. ' . number_format($emp['total_sales']), 0, 0, 'L');
                        $pdf->Ln(10);
                    }
                }
                break;
                
            case 'top_medicines':
                $pdf->SetFont('Arial', 14, 'B');
                $pdf->Cell(0, 10, 'Top Selling Medicines', 0, 0, 'C');
                $pdf->Ln(15);
                $pdf->SetFont('Arial', '', 12);
                
                if (isset($data['medicines'])) {
                    foreach ($data['medicines'] as $med) {
                        $pdf->Cell(0, 10, $med['name'], 0, 0, 'L');
                        $pdf->Cell(30, 10, $med['quantity'] . ' units', 0, 0, 'L');
                        $pdf->Cell(40, 10, 'Rs. ' . number_format($med['revenue']), 0, 0, 'L');
                        $pdf->Ln(10);
                    }
                }
                break;
        }
        
        $pdf->Output($filename, 'F');
        exit;
    }
}

function export_to_excel($data, $filename = 'report_' . date('Y-m-d') . '.xlsx') {
    require_once __DIR__ . '/vendor/autoload.php';
        
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()->setCreator('PHARMACIA Pharmacy Management System');
        $spreadsheet->getProperties()->setTitle('Pharmacy Management Report - ' . date('F j, Y'));
        $spreadsheet->getProperties()->setDescription('Pharmacy Management System Report generated on ' . date('F j, Y'));
        $spreadsheet->setActiveSheetIndex(0);
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report Data');
        
        // Add headers
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Description');
        $sheet->setCellValue('C1', 'Amount');
        $sheet->setCellValue('D1', 'Count');
        
        $row = 2;
        
        // Add data based on type
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'daily_sales':
                    foreach ($data['sales_data'] as $sale) {
                    $sheet->setCellValue('A' . $row, $sale['date']);
                    $sheet->setCellValue('B' . $row, $sale['bills'] . ' bills');
                    $sheet->setCellValue('C' . $row, 'Rs. ' . number_format($sale['total']));
                    $sheet->setCellValue('D' . $row, $sale['total_sales']));
                    $row++;
                }
                break;
                
            case 'monthly_revenue':
                foreach ($data['revenue_data'] as $rev) {
                    $sheet->setCellValue('A' . $row, $rev['month']);
                    $sheet->setCellValue('B' . $rev['month']);
                    $sheet->setCellValue('C' . $row, 'Rs. ' . number_format($rev['revenue']));
                    $row++;
                }
                break;
                
                case 'employee_performance':
                    foreach ($data['employees'] as $emp) {
                        $sheet->setCellValue('A' . $row, $emp['name']);
                        $sheet->setCellValue('B' . $emp['sales'] . ' sales');
                        $sheet->setCellValue('C' . 'Rs. ' . number_format($emp['total_sales']));
                        $row++;
                }
                break;
                
                case 'top_medicines':
                    foreach ($data['medicines'] as $med) {
                        $sheet->setCellValue('A' . $row, $med['name']);
                        $sheet->setCellValue('B' . $med['quantity'] . ' units');
                        $sheet->setCellValue('C' . 'Rs. ' . number_format($med['revenue']));
                        $row++;
                }
                break;
            }
            
            // Add summary row
            if (!empty($data['summary'])) {
                $sheet->setCellValue('A' . ($row + 2), 'TOTAL');
                $sheet->setCellValue('B' . ($row + 2), $data['summary']['total_bills'] ?? 0);
                $sheet->setCellValue('C' . 'Rs. ' . number_format($data['summary']['total_revenue'] ?? 0));
                $sheet->setCellValue('D' . $data['summary']['total_sales'] ?? 0));
            }
            
            // Auto-size columns
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filename);
            exit;
        }
    }
}

// Handle export requests
if (isset($_GET['export']) {
    $export_type = $_GET['export'] ?? 'daily_sales';
    $date_from = $_GET['date_from'] ?? date('Y-m-01');
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    
    switch ($export_type) {
        case 'daily_sales':
            $daily_sales = $conn->query("
                SELECT S_Date as date, COUNT(*) as Total_Bills, SUM(Total_Amt) as Total_Sales
                FROM view_daily_sales
                WHERE S_Date BETWEEN '$date_from' AND '$date_to'
                ORDER BY S_Date DESC
            ");
            
            $data = [
                'type' => 'daily_sales',
                'date_from' => $date_from,
                'date_to' => $date_to,
                'sales_data' => []
            ];
            
            while($row = $daily_sales->fetch_assoc()) {
                $data['sales_data'][] = [
                    'date' => $row['date'],
                    'bills' => $row['Total_Bills'],
                    'total' => $row['Total_Sales']
                ];
            }
            
            export_to_pdf($data, 'daily_sales_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'monthly_revenue':
            $monthly_revenue = $conn->query("
                SELECT DATE_FORMAT(S_Date, '%Y-%m') as Month, 
                       SUM(Total_Amt) as Revenue
                FROM sales
                WHERE S_Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(S_Date, '%Y-%m')
                ORDER BY Month DESC
            ");
            
            $data = [
                'type' => 'monthly_revenue',
                'date_from' => $date_from,
                'date_to' => $date_to,
                'revenue_data' => []
            ];
            
            while($row = $monthly_revenue->fetch_assoc()) {
                $data['revenue_data'][] = [
                    'month' => $row['Month'],
                    'revenue' => $row['Revenue']
                ];
            }
            
            export_to_pdf($data, 'monthly_revenue_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'employee_performance':
            $employee_performance = $conn->query("
                SELECT e.E_Fname, e.E_Lname, COUNT(s.Sale_ID) as Total_Sales, SUM(s.Total_Amt) as Total_Sales_Amount
                FROM employee e
                LEFT JOIN sales s ON e.E_ID = s.E_ID AND s.Refunded = 0
                GROUP BY e.E_ID, e.E_Fname, e.E_Lname
                ORDER BY Total_Sales_Amount DESC
            ");
            
            $data = [
                'type' => 'employee_performance',
                'employees' => []
            ];
            
            while($row = $employee_performance->fetch_assoc()) {
                $data['employees'][] = [
                    'name' => $row['E_Fname'] . ' ' . $row['E_Lname'],
                    'sales' => $row['Total_Sales'],
                    'total_sales_amount' => $row['Total_Sales_Amount']
                ];
            }
            
            export_to_pdf($data, 'employee_performance_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'top_medicines':
            $top_medicines = $conn->query("
                SELECT m.Med_Name, SUM(si.Sale_Qty) as Total_Sold, 
                       SUM(si.Tot_Price) as Total_Revenue
                FROM meds m
                JOIN sales_items si ON m.Med_ID = si.Med_ID
                JOIN sales s ON si.Sale_ID = s.Sale_ID AND s.Refunded = 0
                GROUP BY m.Med_ID, m.Med_Name
                ORDER BY Total_Sold DESC
                LIMIT 10
            ");
            
            $data = [
                'type' => 'top_medicines',
                'medicines' => []
            ];
            
            while($row = $top_medicines->fetch_assoc()) {
                $data['medicines'][] = [
                    'name' => $row['Med_Name'],
                    'quantity' => $row['Total_Sold'],
                    'revenue' => $row['Total_Revenue']
                ];
            }
            
            export_to_pdf($data, 'top_medicines_' . date('Y-m-d') . '.pdf');
            break;
    }
}
?>
