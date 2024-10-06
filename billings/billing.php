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

// Load existing data
$customers = loadCustomers();
$subscriptions = loadSubscriptions();
$billings = loadBillings();

// Handle search functionality
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

        <!-- Search Form -->
        <form action="billing.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by customer, subscription ID, or status" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- Link to Add New Billing -->
        <a href="add_billing.php" class="btn btn-success mb-4">Add New Billing</a>

        <!-- Display Billing Records -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Billing ID</th>
                    <th>Customer Name</th>
                    <th>Subscription ID</th>
                    <th>Billing Date</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($filteredBillings) > 0): ?>
                    <?php foreach ($filteredBillings as $billing): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($billing['billing_id']); ?></td>
                            <td> <?php
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
