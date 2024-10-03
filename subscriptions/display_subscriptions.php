<?php
// Function to load subscription data from the JSON file
function loadSubscriptions() {
    $subscriptionFile = 'subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = '../customer/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to load package data from the JSON file
function loadPakets() {
    $paketFile = '../pakets/paket.json';
    if (file_exists($paketFile)) {
        $jsonData = file_get_contents($paketFile);
        return json_decode($jsonData, true);
    }
    return ['pakets' => []];
}

// Load existing subscriptions, customers, and packages
$subscriptions = loadSubscriptions();
$customers = loadCustomers();
$pakets = loadPakets();

// Create mapping for easy access to customer and package names
$customerMap = [];
foreach ($customers['customers'] as $customer) {
    $customerMap[$customer['id']] = $customer['name'];
}

$paketMap = [];
foreach ($pakets['pakets'] as $paket) {
    $paketMap[$paket['id']] = $paket['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Subscription List</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Package Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($subscriptions['subscriptions']) > 0): ?>
                    <?php foreach ($subscriptions['subscriptions'] as $subscription): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subscription['id']); ?></td>
                            <td><?php echo htmlspecialchars($customerMap[$subscription['customer_id']] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($paketMap[$subscription['paket_id']] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($subscription['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($subscription['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($subscription['status']); ?></td>
                            <td>
                                <a href="edit_subscription.php?id=<?php echo $subscription['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_subscription.php?id=<?php echo $subscription['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subscription?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No subscriptions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="add_subscription.php" class="btn btn-primary">Add New Subscription</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
