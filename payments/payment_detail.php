<?php
// Load billing and payment data
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

// Get billing ID from query parameter
$billingId = isset($_GET['billing_id']) ? $_GET['billing_id'] : null;

// Load data
$billings = loadBillings();
$customers = loadCustomers();


// Find the billing record by ID
$billing = null;
foreach ($billings['billings'] as $b) {
    if ($b['billing_id'] == $billingId) {
        $billing = $b;
        break;
    }
}

// If no billing is found, display an error
if (!$billing) {
    die("Billing record not found.");
}

// Find the corresponding customer
$customer = null;
foreach ($customers['customers'] as $c) {
    if ($c['id'] == $billing['customer_id']) {
        $customer = $c;
        break;
    }
}

// If no customer is found, display an error
if (!$customer) {
    die("Customer not found.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6">Payment Details for Billing ID: <?php echo $billing['billing_id']; ?></h1>
        
        <table class="table table-bordered">
            <tr>
                <th>Customer Name</th>
                <td><?php echo htmlspecialchars($customer['name']); ?></td>
            </tr>
            <tr>
                <th>Subscription ID</th>
                <td><?php echo htmlspecialchars($billing['subscription_id']); ?></td>
            </tr>
            <tr>
                <th>Billing Date</th>
                <td><?php echo htmlspecialchars($billing['billing_date']); ?></td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td><?php echo htmlspecialchars($billing['due_date']); ?></td>
            </tr>
            <tr>
                <th>Amount</th>
                <td><?php echo htmlspecialchars($billing['amount']); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo htmlspecialchars($billing['status']); ?></td>
            </tr>
            <tr>
                <th>Payment Date</th>
                <td><?php echo htmlspecialchars($billing['billing_date']); ?></td>
            </tr>
            <tr>
                <th>Transaction ID</th>
                <td><?php echo htmlspecialchars($billing['billing_id']); ?></td>
            </tr>
        </table>

        <!-- Back to Billing Page -->
        <a href="../billings/billing.php" class="btn btn-secondary">Back to Billing Management</a>
        <a href="receipt.php?billing_id=<?php echo $billing['billing_id']; ?>" class="btn btn-primary">Generate Receipt</a>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
