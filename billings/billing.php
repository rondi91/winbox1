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

// Load existing data
$customers = loadCustomers();
$subscriptions = loadSubscriptions();
$billings = loadBillings();

// Handle form submission for creating a new billing record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
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
        header('Location: billing.php'); // Redirect to the same page
        exit;
    }
}

// Handle deletion of a billing record
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $billings['billings'] = array_filter($billings['billings'], function($billing) use ($deleteId) {
        return $billing['billing_id'] != $deleteId;
    });
    saveBillings($billings);
    header('Location: billing.php'); // Redirect to the same page
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Billing Management</h1>

        <!-- Add New Billing Form -->
        <form action="billing.php" method="POST" class="mb-4">
            <input type="hidden" name="action" value="add">
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

        <!-- Display Billing Records -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Billing ID</th>
                    <th>Customer name</th>
                    <th>Subscription ID</th>
                    <th>Billing Date</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($billings['billings']) > 0): ?>
                    <?php foreach ($billings['billings'] as $billing): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($billing['billing_id']); ?></td>
                            <td> <?php
                                    // Find the customer name from the customers array
                                    $customerName = 'N/A'; // Default value
                                    foreach ($customers['customers'] as $customer) {
                                        if ($customer['id'] == $billing['customer_id']) {
                                            $customerName = $customer['name'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($customerName);
                                ?></td>
                            <td><?php echo htmlspecialchars($billing['subscription_id']); ?></td>
                            <td><?php echo htmlspecialchars($billing['billing_date']); ?></td>
                            <td><?php echo htmlspecialchars($billing['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($billing['amount']); ?></td>
                            <td><?php echo htmlspecialchars($billing['status']); ?></td>
                            <td>
                                <a href="edit_billing.php?id=<?php echo $billing['billing_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="billing.php?delete_id=<?php echo $billing['billing_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this billing record?');">Delete</a>
                                <?php if ($billing['status'] === 'unpaid'): ?>
                                    <a href="../payments/payment_detail.php?billing_id=<?php echo $billing['billing_id']; ?>" class="btn btn-success btn-sm">Pay Now</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No billing records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
