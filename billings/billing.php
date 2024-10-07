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

// Load customers at the start of the script
$customers = loadCustomers();

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

        <!-- Filter Form -->
        <div class="mb-4 row">
            <div class="col-md-3">
                <input type="text" class="form-control" id="liveSearch" placeholder="Search by customer, subscription ID, or status">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterMonth">
                    <option value="">-- Select Month --</option>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo sprintf('%02d', $m); ?>" <?php echo date('m') == $m ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterYear">
                    <option value="">-- Select Year --</option>
                    <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo date('Y') == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">-- Select Status --</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
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
            // Load data on initial page load for the current month and year
            loadBillingData();

            // Trigger live search and filters
            $('#liveSearch, #filterMonth, #filterYear, #filterStatus').on('change keyup', function(){
                loadBillingData();
            });

            // Function to load billing data based on filters and search
            function loadBillingData() {
                let query = $('#liveSearch').val();
                let month = $('#filterMonth').val();
                let year = $('#filterYear').val();
                let status = $('#filterStatus').val();

                // AJAX request to live_search.php
                $.ajax({
                    url: 'live_search.php',
                    type: 'GET',
                    data: {
                        search: query,
                        month: month,
                        year: year,
                        status: status
                    },
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
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
