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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Customer List</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>PPPoE Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customerData['customers']) > 0): ?>
                    <?php foreach ($customerData['customers'] as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                            <td><?php echo isset($pppoeMap[$customer['pppoe_id']]) ? htmlspecialchars($pppoeMap[$customer['pppoe_id']]) : 'N/A'; ?></td>
                            <td>
                                <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
