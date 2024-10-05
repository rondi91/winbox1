<?php
// Function to load payment data from the JSON file
function loadPayments() {
    $paymentFile = 'payments.json';
    if (file_exists($paymentFile)) {
        $jsonData = file_get_contents($paymentFile);
        return json_decode($jsonData, true);
    }
    return ['payments' => []];
}

// Function to load billing data from the JSON file
function loadBillings() {
    $billingFile = '../billings/billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true);
    }
    return ['billings' => []];
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
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
        header('Location: payments.php'); // Redirect to the same page
        exit;
    }
}

// Handle deletion of a payment record
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $payments['payments'] = array_filter($payments['payments'], function($payment) use ($deleteId) {
        return $payment['payment_id'] != $deleteId;
    });
    savePayments($payments);
    header('Location: payments.php'); // Redirect to the same page
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Payment Management</h1>

        <!-- Add New Payment Form -->
        <form action="../payments/payments.php" method="POST" class="mb-4">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label for="billing_id" class="form-label">Billing ID</label>
                <select class="form-select" id="billing_id" name="billing_id" required>
                    <option value="">-- Select Billing --</option>
                    <?php foreach ($billings['billings'] as $billing): ?>
                        <option value="<?php echo htmlspecialchars($billing['billing_id']); ?>">
                            <?php echo htmlspecialchars($billing['billing_id']); ?> - <?php echo htmlspecialchars($billing['amount']); ?>
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
        </form>

        <!-- Display Payment Records -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Billing ID</th>
                    <th>Payment Date</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payments['payments']) > 0): ?>
                    <?php foreach ($payments['payments'] as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['billing_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($payment['amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td>
                                <a href="payments.php?delete_id=<?php echo $payment['payment_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this payment record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
