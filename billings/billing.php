<?php
require '../vendor/autoload.php'; // Load RouterOS API
require '../config.php';          // Load MikroTik configuration


 if (isset($_GET['import_success']) && $_GET['import_success'] == 1): ?>
    <div class="alert alert-success">Excel file imported successfully!</div>
<?php endif; 


// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = '../customers/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
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

// Load customers at the start of the script
$customers = loadCustomers();
$billings = loadBillings();


// Handle deletion by month and year
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_by_month') {
    $deleteMonth = $_POST['delete_month'];
    $deleteYear = $_POST['delete_year'];

    // Filter out billing records that match the selected month and year
    $billings['billings'] = array_filter($billings['billings'], function($billing) use ($deleteMonth, $deleteYear) {
        $billingMonth = date('m', strtotime($billing['billing_date']));
        $billingYear = date('Y', strtotime($billing['billing_date']));

        // Keep only records that do not match the selected month and year
        return !($billingMonth == $deleteMonth && $billingYear == $deleteYear);
    });

    // Save the updated billings back to the JSON file
    saveBillings($billings);

    // Redirect to the billing page after deletion
    header('Location: billing.php');
    exit;
}


// Other existing logic...

// Handle report generation
if (isset($_GET['report_month']) && isset($_GET['report_year'])) {
    $reportMonth = $_GET['report_month'];
    $reportYear = $_GET['report_year'];

    // Initialize report variables
    $totalBillings = 0;
    $totalAmount = 0;
    $totalPaid = 0;
    $totalUnpaid = 0;

    // Filter billing records by the selected month and year
    $reportData = array_filter($billings['billings'], function($billing) use ($reportMonth, $reportYear) {
        $billingMonth = date('m', strtotime($billing['billing_date']));
        $billingYear = date('Y', strtotime($billing['billing_date']));
        return $billingMonth == $reportMonth && $billingYear == $reportYear;
    });

    // Calculate report totals
    foreach ($reportData as $billing) {
        $totalBillings++;
        $totalAmount += $billing['amount'];
        if ($billing['status'] === 'paid') {
            $totalPaid++;
        } else {
            $totalUnpaid++;
        }
    }

    // Display the report
    echo "<div class='alert alert-info mt-4'>";
    echo "<h4>Monthly Report for " . date('F', mktime(0, 0, 0, $reportMonth, 1)) . " $reportYear</h4>";
    echo "<p>Total Billings: $totalBillings</p>";
    echo "<p>Total Amount Billed: $$totalAmount</p>";
    echo "<p>Total Paid: $totalPaid</p>";
    echo "<p>Total Unpaid: $totalUnpaid</p>";
    echo "</div>";
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
         <!-- Delete by Month and Year Form -->
         <form action="billing.php" method="POST" class="mb-4">
            <input type="hidden" name="action" value="delete_by_month">
            <div class="row">
                <div class="col-md-2">
                    <select class="form-select" name="delete_month" required>
                        <option value="">-- Select Month --</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo sprintf('%02d', $m); ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="delete_year" required>
                        <option value="">-- Select Year --</option>
                        <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger">Delete by Month</button>
                </div>
            </div>
        </form>


                <!-- Generate Monthly Report Form -->
        <form action="billing.php" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <select class="form-select" name="report_month" required>
                        <option value="">-- Select Month --</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo sprintf('%02d', $m); ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="report_year" required>
                        <option value="">-- Select Year --</option>
                        <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>

                        <!-- Form to Upload and Import Excel File -->
                        <form action="import_excel.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="file" name="excel_file" class="form-control" required accept=".xlsx, .xls">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Import Excel</button>
                </div>
            </div>
        </form>


        <!-- Export Report to Excel Button -->
        <a href="export_excel.php?report_month=<?php echo $reportMonth; ?>&report_year=<?php echo $reportYear; ?>" class="btn btn-success mb-4">Export to Excel</a>




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
                        console.log(response); // Log the response to the console
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
                                            ${billing.status === 'unpaid' ? `<a href="billing_detail.php?billing_id=${billing.billing_id}" class="btn btn-success btn-sm">Pay Now</a>` : `<a href="../payments/payment_detail.php?billing_id=${billing.billing_id}" class="btn btn-info btn-sm">Detail Payment</a>`}
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
