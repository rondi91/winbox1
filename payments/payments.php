<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Payment Management</h1>

        <!-- Filter Form -->
        <div class="mb-4 row">
            <div class="col-md-3">
                <input type="text" class="form-control" id="liveSearch" placeholder="Search by Billing ID or Customer Name">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterMonth">
                    <option value="">-- Select Month --</option>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo sprintf('%02d', $m); ?>">
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterYear">
                    <option value="">-- Select Year --</option>
                    <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterPaymentMethod">
                    <option value="">-- Select Payment Method --</option>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
        </div>

        <!-- Add New Payment Button -->
        <a href="add_payment.php" class="btn btn-success mb-4">Add New Payment</a>

        <!-- Display Payment Records -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Billing ID</th>
                    <th>Customer Name</th>
                    <th>Package</th>
                    <th>Payment Date</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="paymentResults">
                <!-- Results will be displayed here dynamically -->
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function(){
            // Load initial payment data for the current month and year
            loadPayments();

            // Trigger live search and filters
            $('#liveSearch, #filterMonth, #filterYear, #filterPaymentMethod').on('change keyup', function(){
                loadPayments();
            });

            // Function to load payments based on filters and search query
            function loadPayments() {
                let search = $('#liveSearch').val();
                let month = $('#filterMonth').val();
                let year = $('#filterYear').val();
                let paymentMethod = $('#filterPaymentMethod').val();

                // AJAX request to live_search.php
                $.ajax({
                    url: 'live_search.php',
                    type: 'GET',
                    data: {
                        search: search,
                        month: month,
                        year: year,
                        payment_method: paymentMethod
                    },
                    success: function(response){
                        // Populate the payment results table
                        $('#paymentResults').html(response);
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
