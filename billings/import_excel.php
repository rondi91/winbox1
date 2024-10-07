<?php
require '../vendor/autoload.php'; // Include PhpSpreadsheet
require '../config.php';          // Load MikroTik configuration
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

// Function to save billing data to the JSON file
function saveBillings($data) {
    $billingFile = 'billing.json';
    file_put_contents($billingFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    // Load the uploaded Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(); // Convert the sheet to an array

  
    // Load existing billing data
    $customers = loadCustomers();
    $billings = loadBillings();

    // Loop through each row and import data
    foreach ($rows as $index => $row) {
        // Skip the header row (assuming headers are present in the first row)
        if ($index == 0) {
            continue;
        }

        // Assume the following columns:
        // 0 = Customer Name, 1 = Subscription ID, 2 = Billing Date, 3 = Due Date, 4 = Amount, 5 = Status

        $customerName = $row[0];
        $subscriptionId = $row[1];
        $billingDate = $row[2];
        $dueDate = $row[3];
        $amount = $row[4];
        $status = $row[5];

      


        // Find the customer ID based on the customer name
        $customerId = null;
        foreach ($customers['customers'] as $customer) {
            if (strtolower($customer['name']) === strtolower($customerName)) {
                $customerId = $customer['id'];
                break;
            }
        }
        
        // If the customer is not found, you could skip or create a new customer record
        if ($customerId === null) {
            continue; // Skip the record if customer is not found
        }

        // Add the new billing record
        $newBilling = [
            'billing_id' => count($billings['billings']) + 1, // Auto-increment billing ID
            'customer_id' => $customerId,
            'subscription_id' => $subscriptionId,
            'billing_date' => $billingDate,
            'due_date' => $dueDate,
            'amount' => $amount,
            'status' => strtolower($status) === 'paid' ? 'paid' : 'unpaid', // Normalize status
        ];

        // Append the new billing record
        $billings['billings'][] = $newBilling;
    }

    // Save the updated billing data back to the JSON file
    saveBillings($billings);

    // Redirect back to billing page with success message
    header('Location: billing.php?import_success=1');
    exit;
}
?>
