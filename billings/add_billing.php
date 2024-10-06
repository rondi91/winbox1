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

// Function to load subscription data from the JSON file
function loadSubscriptions() {
    $subscriptionFile = '../subscriptions/subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

// Function to save billing data to the JSON file
function saveBillings($data) {
    $billingFile = 'billing.json';
    file_put_contents($billingFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing data
$customers = loadCustomers();
$subscriptions = loadSubscriptions();

// Handle form submission for creating a new billing record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billings = loadBillings();
    $newBilling = [
        'billing_id' => count($billings['billings']) + 1, // Auto-increment ID
        'customer_id' => $_POST['customer_id'],
        'subscription_id' => $_POST['subscription_id'],
        'billing_date' => $_POST['billing_date'],
        'due_date' => $_POST['due_date'],
        'amount' => $_POST['amount'],
        'status' => $_POST['status'],
    ];

    // Append the new billing and save to the JSON file
    $billings['billings'][] = $newBilling;
    saveBillings($billings);
    header('Location: billing.php'); // Redirect to billing page
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Add Billing</h1>

        <!-- Add New Billing Form -->
        <form action="add_billing.php" method="POST" class="mb-4">
            <div class="mb-3">
                <label for="customer_id" class="form-label">Customer</label>
                <select class="form-select" id="customer_id" name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers['customers'] as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['id']); ?>">
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="subscription_id" class="form-label">Subscription</label>
                <select class="form-select" id="subscription_id" name="subscription_id" required>
                    <option value="">-- Select Subscription --</option>
                    <?php foreach ($subscriptions['subscriptions'] as $subscription): ?>
                        <option value="<?php echo htmlspecialchars($subscription['id']); ?>">
                            <?php echo htmlspecialchars($subscription['id']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="billing_date" class="form-label">Billing Date</label>
                <input type="date" class="form-control" id="billing_date" name="billing_date" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Billing</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
