<?php
require '../vendor/autoload.php'; // Load RouterOS API
require '../config.php';          // Load MikroTik configuration

// Function to load billing data from the JSON file
function loadBillings() {
    $billingFile = '../billings/billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true);
    }
    return ['billings' => []];
}

// Function to load payment data from the JSON file
function loadPayments() {
    $paymentFile = 'payments.json';
    if (file_exists($paymentFile)) {
        $jsonData = file_get_contents($paymentFile);
        return json_decode($jsonData, true);
    }
    return ['payments' => []];
}

// Function to save payment data to the JSON file
function savePayments($data) {
    $paymentFile = 'payments.json';
    file_put_contents($paymentFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing payments and billings
$payments = loadPayments();
$billings = loadBillings();

// Handle form submission for creating a new payment record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newPayment = [
        'payment_id' => count($payments['payments']) + 1, // Auto-increment ID
        'billing_id' => $_POST['billing_id'],
        'payment_date' => date('Y-m-d'), // Current date
        'amount' => $_POST['amount'],
        'payment_method' => $_POST['payment_method'],
    ];

    // Append the new payment and save to the JSON file
    $payments['payments'][] = $newPayment;
    savePayments($payments);
    header('Location: payments.php'); // Redirect to the payments page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Add New Payment</h1>

        <!-- Add New Payment Form -->
        <form action="add_payment.php" method="POST" class="mb-4">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label for="billing_id" class="form-label">Billing ID</label>
                <select class="form-select" id="billing_id" name="billing_id" required>
                    <option value="">-- Select Billing --</option>
                    <?php foreach ($billings['billings'] as $billing): ?>
                        <option value="<?php echo htmlspecialchars($billing['billing_id']); ?>">
                            <?php echo htmlspecialchars($billing['billing_id']); ?> - 
                            <?php echo htmlspecialchars($billing['amount']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Payment</button>
            <a href="payments.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
