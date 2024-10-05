<?php
// Load the necessary JSON files
function loadBillings() {
    $billingFile = '../billings/billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true);
    }
    return ['billings' => []];
}

function loadCustomers() {
    $customerFile = '../customer/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

function loadPackages() {
    $packageFile = '../pakets/paket.json';
    if (file_exists($packageFile)) {
        $jsonData = file_get_contents($packageFile);
        return json_decode($jsonData, true);
    }
    return ['pakets' => []];
}

function loadPayments() {
    $paymentFile = 'payments.json';
    if (file_exists($paymentFile)) {
        $jsonData = file_get_contents($paymentFile);
        return json_decode($jsonData, true);
    }
    return ['payments' => []];
}
function subscriptions() {
    $subscribtionFile = '../subscriptions/subscriptions.json';
    if (file_exists($subscribtionFile)) {
        $jsonData = file_get_contents($subscribtionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

// Load all necessary data
$billings = loadBillings();
$customers = loadCustomers();
$packages = loadPackages();
$payments = loadPayments();
$subscriptions = subscriptions();


// var_dump($packages);
// die();

// Get billing ID from query parameters
$billingId = isset($_GET['billing_id']) ? $_GET['billing_id'] : null;
$billing = null;

// Find the billing record to display
foreach ($billings['billings'] as $bill) {
    if ($bill['billing_id'] == $billingId) {
        $billing = $bill;
        break;
    }
}

// Redirect if the billing record is not found
if (!$billing) {
    header('Location: billing.php'); // Redirect to the billing management page
    exit;
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new payment record
    $newPayment = [
        'payment_id' => count($payments['payments']) + 1, // Auto-increment ID
        'billing_id' => $billingId,
        'payment_date' => date('Y-m-d'), // Current date
        'amount' =>$_POST['amount'],
        'payment_method' => $_POST['payment_method'],
    ];

    // Append the new payment and save to the JSON file
    $payments['payments'][] = $newPayment;
    // Save payments to the JSON file
    file_put_contents('payments.json', json_encode($payments, JSON_PRETTY_PRINT));
    
    // Update the billing status to 'paid'
    $billing['status'] = 'paid';
    
    // Update the billing record in the billing JSON file
    foreach ($billings['billings'] as &$bill) {
        if ($bill['billing_id'] == $billingId) {
            $bill = $billing; // Update billing record
            break;
        }
    }
    file_put_contents('../billings/billing.json', json_encode($billings, JSON_PRETTY_PRINT)); // Save updated billing
    header('Location: payments.php'); // Redirect to the billing management page
    exit;
}

// Format date function
function formatDate($date) {
    return date('d F Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Payment Details</h1>

        <div class="mb-4">
            <h5>Billing Information</h5>
            <p><strong>Billing ID:</strong> <?php echo htmlspecialchars($billing['billing_id']); ?></p>
            <p><strong>Customer Name:</strong>
                <?php
                    $customerName = 'N/A'; // Default value
                    foreach ($customers['customers'] as $customer) {
                        if ($customer['id'] == $billing['customer_id']) {
                            $customerName = $customer['name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($customerName);
                ?>
            </p>
            <p><strong>Subscription ID:</strong> <?php echo htmlspecialchars($billing['subscription_id']); ?></p>
            <p><strong>Package Price:</strong>
                <?php
                   // Fetch the package price based on the paket_id in the subscription record
                $packagePrice = 'N/A'; // Default value if not found

                // Find the subscription record associated with the billing
                $subscription = null;
                foreach ($subscriptions['subscriptions'] as $sub) {
                    if ($sub['id'] == $billing['subscription_id']) {
                        $subscription = $sub;
                        break;
                    }
                }

                // Now, if we found the subscription, get the package price
                if ($subscription) {
                    $paketId = $subscription['paket_id']; // Get the paket_id from the subscription
// var_dump($paketId);
// die();
                    // Look for the corresponding package
                    foreach ($packages['pakets'] as $package) {
                        if ($package['id'] == $paketId) {
                            $packagePrice = isset($package['price']) ? $package['price'] : 'N/A'; // Get the price if it exists
                            
                            break;
                        }
                    }
                }

                ?>
                <?php echo htmlspecialchars($packagePrice); ?>
            </p>
            <p><strong>Billing Date:</strong> <?php echo formatDate($billing['billing_date']); ?></p>
            <p><strong>Due Date:</strong> <?php echo formatDate($billing['due_date']); ?></p>
            <p><strong>Amount:</strong> $<?php echo htmlspecialchars($packagePrice); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($billing['status']); ?></p>
        </div>

        <form action="payment_detail.php?billing_id=<?php echo htmlspecialchars($billingId); ?>" method="POST">
            <div class="mb-3">
                <input type="hidden"  name="amount" value="<?php echo htmlspecialchars($packagePrice);  ?>">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Confirm Payment</button>
            <a href="billing.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
