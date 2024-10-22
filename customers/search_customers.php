<?php
require '../vendor/autoload.php'; // RouterOS API
require '../config.php';          // Load MikroTik configuration

use RouterOS\Client;
use RouterOS\Query;

// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = 'customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Load the customers from the JSON file
$customerData = loadCustomers();

// Fetch PPPoE accounts from MikroTik
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

$pppoeQuery = new Query("/ppp/secret/print");
$pppoeAccounts = $client->query($pppoeQuery)->read();

// Create a mapping from PPPoE ID to PPPoE username
$pppoeMap = [];
foreach ($pppoeAccounts as $pppoe) {
    $pppoeMap[$pppoe['.id']] = $pppoe['name'];
}

// Search functionality
$searchTerm = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Filter customers based on search term
if ($searchTerm !== '') {
    $customerData['customers'] = array_filter($customerData['customers'], function($customer) use ($searchTerm) {
        return strpos(strtolower($customer['name']), $searchTerm) !== false ||
               strpos(strtolower($customer['email']), $searchTerm) !== false ||
               strpos(strtolower($customer['phone']), $searchTerm) !== false;
    });
}

// Display filtered customers
if (count($customerData['customers']) > 0) {
    foreach ($customerData['customers'] as $customer) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($customer['id']) . '</td>';
        echo '<td>' . htmlspecialchars($customer['name']) . '</td>';
        echo '<td>' . htmlspecialchars($customer['email']) . '</td>';
        echo '<td>' . htmlspecialchars($customer['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($customer['address']) . '</td>';
        echo '<td>' . (isset($pppoeMap[$customer['pppoe_id']]) ? htmlspecialchars($pppoeMap[$customer['pppoe_id']]) : 'N/A') . '</td>';
        echo '<td>';
        echo '<a href="edit_customer.php?id=' . htmlspecialchars($customer['id']) . '" class="btn btn-primary btn-sm">Edit</a>';
        echo '<a href="delete_customer.php?id=' . htmlspecialchars($customer['id']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this customer?\');">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" class="text-center">No customers found.</td></tr>';
}
?>
