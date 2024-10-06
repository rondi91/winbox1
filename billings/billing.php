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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery for AJAX -->
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Billing Management</h1>

        <!-- Live Search Form -->
        <div class="mb-4">
            <input type="text" class="form-control" id="liveSearch" placeholder="Search by customer, subscription ID, or status">
        </div>

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
            <tbody id="billingResults">
                <!-- Results will be displayed here dynamically -->
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function(){
            // Live search function
            $('#liveSearch').on('keyup', function(){
                let query = $(this).val();

                // AJAX request to live_search.php
                $.ajax({
                    url: 'live_search.php',
                    type: 'GET',
                    data: { search: query },
                    success: function(response){
                        // Clear previous results
                        $('#billingResults').empty();

                        // Check if there are results
                        if(response.length > 0) {
                            $.each(response, function(index, billing){
                                let customerName = 'N/A';

                                // Find the customer name
                                <?php foreach ($customers['customers'] as $customer): ?>
                                    if(billing.customer_id == "<?php echo $customer['id']; ?>") {
                                        customerName = "<?php echo $customer['name']; ?>";
                                    }
                                <?php endforeach; ?>

                                // Append new rows to the table
                                $('#billingResults').append(`
                                    <tr>
                                        <td>${billing.billing_id}</td>
                                        <td>${customerName}</td>
                                        <td>${billing.subscription_id}</td>
                                        <td>${billing.billing_date}</td>
                                        <td>${billing.due_date}</td>
                                        <td>${billing.amount}</td>
                                        <td>${billing.status}</td>
                                        <td>
                                            <a href="edit_billing.php?id=${billing.billing_id}" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="billing.php?delete_id=${billing.billing_id}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this billing record?');">Delete</a>
                                            ${billing.status === 'unpaid' ? `<a href="../payments/payment_detail.php?billing_id=${billing.billing_id}" class="btn btn-success btn-sm">Pay Now</a>` : ''}
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            // No results found
                            $('#billingResults').append('<tr><td colspan="8" class="text-center">No billing records found.</td></tr>');
                        }
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
