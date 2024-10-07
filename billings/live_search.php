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

// Get filters from the request
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$month = isset($_GET['month']) ? $_GET['month'] : date('m'); // Current month by default
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');     // Current year by default
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Filter billing records by month, year, status, and search query
$filteredBillings = array_filter($billings['billings'], function($billing) use ($searchQuery, $customers, $month, $year, $status) {
    // Filter by month and year
    $billingMonth = date('m', strtotime($billing['billing_date']));
    $billingYear = date('Y', strtotime($billing['billing_date']));

    if ($billingMonth != $month || $billingYear != $year) {
        return false;
    }

    // Filter by status
    if ($status && $billing['status'] !== $status) {
        return false;
    }

    // Search by customer name, subscription ID, or status
    $customerName = 'N/A';
    foreach ($customers['customers'] as $customer) {
        if ($customer['id'] == $billing['customer_id']) {
            $customerName = $customer['name'];
            break;
        }
    }

    return stripos($customerName, $searchQuery) !== false ||
           stripos($billing['subscription_id'], $searchQuery) !== false ||
           stripos($billing['status'], $searchQuery) !== false;
});

// Return filtered results as JSON
header('Content-Type: application/json');
echo json_encode(array_values($filteredBillings));
