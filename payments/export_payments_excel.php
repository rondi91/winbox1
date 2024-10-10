<?php
require '../vendor/autoload.php'; // Include Composer's autoloader
include 'load_data.php'; // Include the data loading functions

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Load payments data
$payments = loadPayments();
$billings = loadBillings();
$customers = loadCustomers();
$packages = loadPackages();
$subscriptions = loadSubscriptions();

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$sheet->setCellValue('A1', 'Payment ID');
$sheet->setCellValue('B1', 'Billing ID');
$sheet->setCellValue('C1', 'Customer Name');
$sheet->setCellValue('D1', 'Package');
$sheet->setCellValue('E1', 'Payment Date');
$sheet->setCellValue('F1', 'Amount');
$sheet->setCellValue('G1', 'Payment Method');

// Populate data in the sheet
$row = 2; // Start from the second row
foreach ($payments as $payment) {
    // Find related customer name
    $customerName = 'N/A'; // Default value
    foreach ($billings as $billing) {
        if ($billing['billing_id'] == $payment['billing_id']) {
            foreach ($customers as $customer) {
                if ($customer['id'] == $billing['customer_id']) {
                    $customerName = $customer['name'];
                    break 2; // Break out of both loops
                }
            }
        }
    }

    // Find related package name
    $packageName = 'N/A'; // Default value
    foreach ($billings as $billing) {
        if ($billing['billing_id'] == $payment['billing_id']) {
            foreach ($subscriptions as $subscription) {
                if ($subscription['id'] == $billing['subscription_id']) {
                    foreach ($packages as $package) {
                        if ($package['id'] == $subscription['paket_id']) {
                            $packageName = $package['name'];
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
        }
    }

    // Write data to the spreadsheet
    $sheet->setCellValue('A' . $row, $payment['payment_id']);
    $sheet->setCellValue('B' . $row, $payment['billing_id']);
    $sheet->setCellValue('C' . $row, $customerName);
    $sheet->setCellValue('D' . $row, $packageName);
    $sheet->setCellValue('E' . $row, $payment['payment_date']);
    $sheet->setCellValue('F' . $row, $payment['amount']);
    $sheet->setCellValue('G' . $row, $payment['payment_method']);
    $row++;
}

// Set the filename and save the Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'payments_export_' . date('Y-m-d_H-i-s') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save and output the file
$writer->save('php://output');
exit;
