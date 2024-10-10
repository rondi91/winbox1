<?php

// Load data from JSON files (the same functions as defined in payments.php)
function loadPayments() {
    $paymentFile = 'payments.json';
    if (file_exists($paymentFile)) {
        $jsonData = file_get_contents($paymentFile);
        return json_decode($jsonData, true);
    }
    return ['payments' => []];
}

function loadBillings() {
    $billingFile = '../billings/billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true);
    }
    return ['billings' => []];
}

function loadCustomers() {
    $customerFile = '../customers/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

function loadSubscriptions() {
    $subscriptionFile = '../subscriptions/subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

function loadPackages() {
    $packageFile = '../pakets/paket.json';
    if (file_exists($packageFile)) {
        $jsonData = file_get_contents($packageFile);
        return json_decode($jsonData, true);
    }
    return ['packages' => []];
}

// Load the data
$payments = loadPayments();
$billings = loadBillings();
$customers = loadCustomers();
$subscriptions = loadSubscriptions();
$packages = loadPackages();

// Get the search and filter parameters
$searchQuery = isset($_GET['search']) ? strtolower($_GET['search']) : '';
$filterMonth = isset($_GET['month']) ? $_GET['month'] : '';
$filterYear = isset($_GET['year']) ? $_GET['year'] : '';
$filterPaymentMethod = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';

// Filter payments based on criteria
$filteredPayments = array_filter($payments['payments'], function($payment) use ($searchQuery, $filterMonth, $filterYear, $filterPaymentMethod, $billings, $customers) {
    // Find related customer name for search purposes
    $customerName = '';
    foreach ($billings['billings'] as $billing) {
        if ($billing['billing_id'] == $payment['billing_id']) {
            foreach ($customers['customers'] as $customer) {
                if ($customer['id'] == $billing['customer_id']) {
                    $customerName = strtolower($customer['name']);
                    break 2;
                }
            }
        }
    }

    // Apply filters
    $matchesSearch = empty($searchQuery) || strpos(strtolower($payment['billing_id']), $searchQuery) !== false || strpos($customerName, $searchQuery) !== false;
    $matchesMonth = empty($filterMonth) || date('m', strtotime($payment['payment_date'])) == $filterMonth;
    $matchesYear = empty($filterYear) || date('Y', strtotime($payment['payment_date'])) == $filterYear;
    $matchesPaymentMethod = empty($filterPaymentMethod) || $payment['payment_method'] === $filterPaymentMethod;

    return $matchesSearch && $matchesMonth && $matchesYear && $matchesPaymentMethod;
});

// Generate HTML for filtered payments
$output = '';
if (count($filteredPayments) > 0) {
    foreach ($filteredPayments as $payment) {
        $billingId = htmlspecialchars($payment['billing_id']);
        $paymentId = htmlspecialchars($payment['payment_id']);
        $paymentDate = htmlspecialchars($payment['payment_date']);
        $amount = htmlspecialchars($payment['amount']);
        $paymentMethod = htmlspecialchars($payment['payment_method']);

        // Find customer name
        $customerName = 'N/A';
        foreach ($billings['billings'] as $billing) {
            if ($billing['billing_id'] == $billingId) {
                foreach ($customers['customers'] as $customer) {
                    if ($customer['id'] == $billing['customer_id']) {
                        $customerName = htmlspecialchars($customer['name']);
                        break 2;
                    }
                }
            }
        }

        // Find package name
        $packageName = 'N/A';
        foreach ($billings['billings'] as $billing) {
            if ($billing['billing_id'] == $billingId) {
                foreach ($subscriptions['subscriptions'] as $subscription) {
                    if ($subscription['id'] == $billing['subscription_id']) {
                        foreach ($packages['pakets'] as $package) {
                            if ($package['id'] == $subscription['paket_id']) {
                                $packageName = htmlspecialchars($package['name']);
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        $output .= "<tr>
                        <td>{$paymentId}</td>
                        <td>{$billingId}</td>
                        <td>{$customerName}</td>
                        <td>{$packageName}</td>
                        <td>{$paymentDate}</td>
                        <td>{$amount}</td>
                        <td>{$paymentMethod}</td>
                        <td>
                            <a href='payments.php?delete_id={$paymentId}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this payment record?\");'>Delete</a>
                        </td>
                    </tr>";
    }
} else {
    $output .= '<tr><td colspan="8" class="text-center">No payment records found.</td></tr>';
}

echo $output;
?>
