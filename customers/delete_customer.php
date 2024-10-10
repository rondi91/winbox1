<?php
// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = 'customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to save customer data to the JSON file
function saveCustomers($data) {
    $customerFile = 'customers.json';
    file_put_contents($customerFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing customers
$customers = loadCustomers();
$customerId = isset($_GET['id']) ? $_GET['id'] : null;

if ($customerId !== null) {
    // Find the index of the customer to delete
    foreach ($customers['customers'] as $index => $customer) {
        if ($customer['id'] == $customerId) {
            // Remove the customer from the array
            array_splice($customers['customers'], $index, 1);
            saveCustomers($customers); // Save the updated data back to the file
            break;
        }
    }
}

// Redirect back to the customer list after deletion
header('Location: display_customers.php');
exit;
?>
