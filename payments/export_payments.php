<?php
include 'load_data.php'; // Include the data loading functions

$payments = loadPayments(); // Load payments data

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=payments.csv');

$output = fopen('php://output', 'w');

// Set the column headers
fputcsv($output, array('Payment ID', 'Billing ID', 'Customer Name', 'Package', 'Payment Date', 'Amount', 'Payment Method'));

// Loop through the data and write to the file
foreach ($payments as $payment) {
    fputcsv($output, array(
        $payment['payment_id'],
        $payment['billing_id'],
        $payment['customer_name'], // You may need to find this from other loaded data
        $payment['package'], // You may need to find this from other loaded data
        $payment['payment_date'],
        $payment['amount'],
        $payment['payment_method']
    ));
}

fclose($output);
exit;
