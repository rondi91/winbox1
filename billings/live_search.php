<?php
require '../vendor/autoload.php'; // Load RouterOS API
require '../config.php';          // Load MikroTik configuration

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

// Load existing data
$customers = loadCustomers();
$billings = loadBillings();

// Handle live search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredBillings = $billings['billings'];

// If there's a search query, filter the results
if (!empty($searchQuery)) {
    $filteredBillings = array_filter($billings['billings'], function($billing) use ($searchQuery, $customers) {
        $customerName = 'N/A';
        foreach ($customers['customers'] as $customer) {
            if ($customer['id'] == $billing['customer_id']) {
                $customerName = $customer['name'];
                break;
            }
        }

        // Check if the search query matches customer name, subscription ID, or status
        return stripos($customerName, $searchQuery) !== false ||
               stripos($billing['subscription_id'], $searchQuery) !== false ||
               stripos($billing['status'], $searchQuery) !== false;
    });
}

// Return filtered results as JSON
header('Content-Type: application/json');
echo json_encode(array_values($filteredBillings));
