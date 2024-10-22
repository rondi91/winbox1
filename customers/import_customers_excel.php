<?php
require '../vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Function to load customers from the JSON file
function loadCustomers() {
    $customerFile = 'customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to save customers to the JSON file
function saveCustomers($customers) {
    $customerFile = 'customers.json';
    file_put_contents($customerFile, json_encode($customers, JSON_PRETTY_PRINT));
}

// Check if a file is uploaded
if (isset($_FILES['excelFile'])) {
    $fileTmpPath = $_FILES['excelFile']['tmp_name'];

    // Load the Excel file
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();

    // Load existing customers
    $customers = loadCustomers();

    // Read rows from the Excel file
    $row = 2; // Start reading from the second row (assuming the first row is the header)
    while ($sheet->getCell('A' . $row)->getValue() !== null) {
        $name = $sheet->getCell('A' . $row)->getValue();
        $email = $sheet->getCell('B' . $row)->getValue();
        $phone = $sheet->getCell('C' . $row)->getValue();
        $address = $sheet->getCell('D' . $row)->getValue();
        $pppoeId = $sheet->getCell('E' . $row)->getValue();

        // Add each customer to the customers array
        $customers['customers'][] = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'pppoe_id' => $pppoeId
        ];

        $row++;
    }

    // Save the updated customers back to the JSON file
    saveCustomers($customers);

    // Redirect back to the customer page with success message
    header("Location: customers.php?import=success");
    exit;
}
?>
