<?php
require '../vendor/autoload.php'; // Include the PhpSpreadsheet library
require '../config.php';          // Load MikroTik configuration
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = '../customer/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to load billing data from the JSON file
function loadBillings() {
    $billingFile = 'billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true);
    }
    return ['billings' => []];
}

// Load data
$customers = loadCustomers();
$billings = loadBillings();

// Get the selected month and year from the GET parameters
$reportMonth = isset($_GET['report_month']) ? $_GET['report_month'] : date('m');
$reportYear = isset($_GET['report_year']) ? $_GET['report_year'] : date('Y');

// Initialize the PhpSpreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the header row for the Excel file
$sheet->setCellValue('A1', 'Billing ID');
$sheet->setCellValue('B1', 'Customer Name');
$sheet->setCellValue('C1', 'Subscription ID');
$sheet->setCellValue('D1', 'Billing Date');
$sheet->setCellValue('E1', 'Due Date');
$sheet->setCellValue('F1', 'Amount');
$sheet->setCellValue('G1', 'Status');

// Filter billing data by the selected month and year
$row = 2; // Start from row 2 because row 1 has the headers
foreach ($billings['billings'] as $billing) {
    $billingMonth = date('m', strtotime($billing['billing_date']));
    $billingYear = date('Y', strtotime($billing['billing_date']));
    
    if ($billingMonth == $reportMonth && $billingYear == $reportYear) {
        $customerName = 'N/A';
        foreach ($customers['customers'] as $customer) {
            if ($customer['id'] == $billing['customer_id']) {
                $customerName = $customer['name'];
                break;
            }
        }

        // Fill the rows with billing data
        $sheet->setCellValue('A' . $row, $billing['billing_id']);
        $sheet->setCellValue('B' . $row, $customerName);
        $sheet->setCellValue('C' . $row, $billing['subscription_id']);
        $sheet->setCellValue('D' . $row, $billing['billing_date']);
        $sheet->setCellValue('E' . $row, $billing['due_date']);
        $sheet->setCellValue('F' . $row, $billing['amount']);
        $sheet->setCellValue('G' . $row, $billing['status']);
        $row++;
    }
}

// Set the filename for the export
$filename = "billing_report_" . $reportMonth . "_" . $reportYear . ".xlsx";

// Set headers to force download of the Excel file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Write the spreadsheet to a file and send it to the user for download
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
