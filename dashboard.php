<?php
// File paths for payments.json, billing.json, subscriptions.json, customers.json, and paket.json
$paymentsFilePath = 'payments/payments.json';
$billingsFilePath = 'billings/billing.json';
$subscriptionsFilePath = 'subscriptions/subscriptions.json';
$customersFilePath = 'customer/customers.json';
$paketFilePath = 'pakets/paket.json';

// Load the payments data
$paymentsData = json_decode(file_get_contents($paymentsFilePath), true)['payments'];

// Load the billing data
$billingData = json_decode(file_get_contents($billingsFilePath), true)['billings'];

// Load the subscriptions data
$subscriptionsData = json_decode(file_get_contents($subscriptionsFilePath), true)['subscriptions'];

// Load the customers data
$customersData = json_decode(file_get_contents($customersFilePath), true)['customers'];

// Load the paket data
$paketData = json_decode(file_get_contents($paketFilePath), true)['pakets'];

// Initialize counters
$totalPaymentsThisMonth = 0;
$totalPaymentsThisYear = 0;
$totalPaid = 0;
$totalUnpaid = 0;
$totalOmset = 0;

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Calculate total payments this month and this year
foreach ($paymentsData as $payment) {
    $paymentDate = date('Y-m-d', strtotime($payment['payment_date']));
    $paymentYear = date('Y', strtotime($paymentDate));
    $paymentMonth = date('m', strtotime($paymentDate));
    
    if ($paymentYear == $currentYear) {
        $totalPaymentsThisYear += $payment['amount'];
        if ($paymentMonth == $currentMonth) {
            $totalPaymentsThisMonth += $payment['amount'];
        }
    }
}

// Calculate total paid and unpaid bills
foreach ($billingData as $billing) {
    if ($billing['status'] == 'paid') {
        $totalPaid++;
    } else {
        $totalUnpaid++;
    }
}

// Calculate total omset from active subscriptions
foreach ($subscriptionsData as $subscription) {
    if ($subscription['status'] == 'active') {
        // Get the package associated with this subscription
        foreach ($paketData as $paket) {
            if ($paket['id'] == $subscription['paket_id']) {
                $totalOmset += $paket['price'];
                break;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        .dashboard {
            width: 100%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .dashboard-item {
            margin: 10px 0;
            font-size: 18px;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h2>Dashboard</h2>
    <div class="dashboard-item">
        <strong>Total Payments This Month:</strong> Rp. <?php echo number_format($totalPaymentsThisMonth, 0, ',', '.'); ?>
    </div>
    <div class="dashboard-item">
        <strong>Total Payments This Year:</strong> Rp. <?php echo number_format($totalPaymentsThisYear, 0, ',', '.'); ?>
    </div>
    <div class="dashboard-item">
        <strong>Total Paid Bills:</strong> <?php echo $totalPaid; ?>
    </div>
    <div class="dashboard-item">
        <strong>Total Unpaid Bills:</strong> <?php echo $totalUnpaid; ?>
    </div>
    <div class="dashboard-item">
        <strong>Total Omset from Active Subscriptions:</strong> Rp. <?php echo number_format($totalOmset, 0, ',', '.'); ?>
    </div>
</div>

</body>
</html>
