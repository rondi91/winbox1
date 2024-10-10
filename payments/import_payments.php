<?php
require 'vendor/autoload.php'; // Include Composer's autoloader
include 'load_data.php'; // Include the data loading functions

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if a file is uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    // Validate the uploaded file
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();

        // Prepare to collect new payment data
        $newPayments = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells, even if they are not set

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue(); // Get cell value
            }

            // Assuming your Excel has the following columns:
            // Payment ID, Billing ID, Payment Date, Amount, Payment Method
            if (!empty($rowData[0]) && $rowData[0] !== 'Payment ID') { // Skip header
                $newPayments[] = [
                    'payment_id' => $rowData[0],
                    'billing_id' => $rowData[1],
                    'payment_date' => $rowData[2],
                    'amount' => $rowData[3],
                    'payment_method' => $rowData[4],
                ];
            }
        }

        // Load existing payments
        $payments = loadPayments();

        // Append new payments to existing data
        foreach ($newPayments as $newPayment) {
            $payments[] = $newPayment; // Append new payment to the existing array
        }

        // Save the updated payments data to JSON
        savePayments(['payments' => $payments]);

        // Redirect back with a success message
        header('Location: payments.php?import_success=1');
        exit;
    } else {
        echo "Error uploading file.";
    }
}
?>
