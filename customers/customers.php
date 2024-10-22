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

// Search functionality
$searchTerm = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// If a search term is entered, filter the customers based on it
if ($searchTerm !== '') {
    $customerData['customers'] = array_filter($customerData['customers'], function($customer) use ($searchTerm) {
        return strpos(strtolower($customer['name']), $searchTerm) !== false ||
               strpos(strtolower($customer['email']), $searchTerm) !== false ||
               strpos(strtolower($customer['phone']), $searchTerm) !== false;
    });
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        .sidebar {
            min-width: 200px;
            height: 100vh;
            background-color: #f8f9fa;
            padding: 15px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Menu</h2>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../dashboard/dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../customers/customers.php">Customers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../paket/paket.php">Packages</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../billings/billing.php">Billing</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../payments/payments.php">Payments</a>
            </li>
        </ul>
    </div>
    <div class="main-content">
    <h1 class="display-6 text-center">Customer List</h1>

    <!-- Search Form (now without form submit) -->
    <div class="input-group mb-3">
        <input type="text" id="search" class="form-control" placeholder="Search by Name, Email, or Phone">
    </div>

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
        <tbody id="customer-list">
    <?php foreach ($customerData['customers'] as $customer): ?>
        <tr>
            <td><?= $customer['id'] ?></td>
            <td><?= $customer['name'] ?></td>
            <td><?= $customer['email'] ?></td>
            <td><?= $customer['phone'] ?></td>
            <td><?= $customer['address'] ?></td>
            <td><?= isset($pppoeMap[$customer['pppoe_id']]) ? $pppoeMap[$customer['pppoe_id']] : 'N/A' ?></td>
            <td>
                <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="delete_customer.php?id=<?= $customer['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

    </table>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
    document.getElementById('search').addEventListener('input', function() {
        const searchQuery = this.value;

        // Send AJAX request to fetch filtered customers
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'search_customers.php?search=' + encodeURIComponent(searchQuery), true);
        xhr.onload = function() {
            if (this.status === 200) {
                // Update the table body with the received data
                document.getElementById('customer-list').innerHTML = this.responseText;
            }
        };
        xhr.send();
    });
</script>

    </script>
</body>
</html>
