<?php
require '../vendor/autoload.php'; // Load RouterOS API
require '../config.php';          // Load MikroTik configuration

// Function to load subscription data from the JSON file
function loadSubscriptions() {
    $subscriptionFile = '../subscriptions/subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
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

// Load existing subscriptions and billings
$subscriptions = loadSubscriptions();
$billings = loadBillings();

// Current date
$currentDate = date('Y-m-d');

foreach ($subscriptions['subscriptions'] as $subscription) {
    // Check if the subscription is active
    if ($subscription['status'] === 'active') {
        // Calculate billing amount (this could be based on the package price)
        $packageId = $subscription['paket_id'];

        // Here, you would normally fetch the package details to get the price
        // Assuming the package details are stored in a packages.json file
        $packages = json_decode(file_get_contents('../pakets/paket.json'), true);
        $amount = 0;
        

        foreach ($packages['pakets'] as $package) {
            
            if ($package['id'] == $packageId) {
                $amount = $package['price']; // Assuming 'price' is a field in your packages
               
                break;
            }
        }
        

        // Prepare the billing record
        $newBilling = [
            'billing_id' => count($billings['billings']) + 1, // Auto-increment ID
            'customer_id' => $subscription['customer_id'],
            'subscription_id' => $subscription['id'],
            'billing_date' => $currentDate,
            'due_date' => date('Y-m-d', strtotime($currentDate . ' + 30 days')), // Due in 30 days
            'amount' => $amount,
            'status' => 'unpaid', // Set initial status as unpaid
        ];

        // Append the new billing record
        $billings['billings'][] = $newBilling;
    }
}

// Save the updated billing records
saveBillings($billings);

// Optionally, redirect to billing management page or display a success message
header('Location: billing.php'); // Redirect to the billing management page
exit;
?>
