<?php
// Function to load payment data from the JSON file
function loadPayments() {
    $paymentFile = '../payments/payments.json';
    if (file_exists($paymentFile)) {
        $jsonData = file_get_contents($paymentFile);
        return json_decode($jsonData, true)['payments'];
    }
    return [];
}

// Function to load billing data from the JSON file
function loadBillings() {
    $billingFile = '../billings/billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true)['billings'];
    }
    return [];
}

// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = '../customers/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true)['customers'];
    }
    return [];
}

// Function to load package data from the JSON file
function loadPackages() {
    $packageFile = '../pakets/paket.json';
    if (file_exists($packageFile)) {
        $jsonData = file_get_contents($packageFile);
        return json_decode($jsonData, true)['packages'];
    }
    return [];
}

// Function to load subscription data from the JSON file
function loadSubscriptions() {
    $subscriptionFile = '../subscriptions/subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true)['subscriptions'];
    }
    return [];
}
?>
