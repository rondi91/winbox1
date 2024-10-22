<?php
require '../vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = 'customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Load customer data
$customers = loadCustomers();

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the headers for the Excel columns
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Name');
$sheet->setCellValue('C1', 'Email');
$sheet->setCellValue('D1', 'Phone');
$sheet->setCellValue('E1', 'Address');
$sheet->setCellValue('F1', 'PPPoE Username');

// Add data to the Excel file
$row = 2; // Start from the second row
foreach ($customers['customers'] as $customer) {
    $sheet->setCellValue('A' . $row, $customer['id']);
    $sheet->setCellValue('B' . $row, $customer['name']);
    $sheet->setCellValue('C' . $row, $customer['email']);
    $sheet->setCellValue('D' . $row, $customer['phone']);
    $sheet->setCellValue('E' . $row, $customer['address']);
    $sheet->setCellValue('F' . $row, isset($customer['pppoe_id']) ? $customer['pppoe_id'] : 'N/A');
    $row++;
}

// Create Excel file and set download headers
$fileName = 'customer_data.xlsx';
$writer = new Xlsx($spreadsheet);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
?>
