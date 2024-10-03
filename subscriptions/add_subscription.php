<?php
require '../vendor/autoload.php'; // RouterOS API
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

// Function to load package data from the JSON file
function loadPakets() {
    $paketFile = '../pakets/paket.json';
    if (file_exists($paketFile)) {
        $jsonData = file_get_contents($paketFile);
        return json_decode($jsonData, true);
    }
    return ['pakets' => []];
}

// Function to load subscription data from the JSON file
function loadSubscriptions() {
    $subscriptionFile = 'subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

// Function to save subscription data to the JSON file
function saveSubscriptions($data) {
    $subscriptionFile = 'subscriptions.json';
    file_put_contents($subscriptionFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing data
$customers = loadCustomers();
$pakets = loadPakets();
$subscriptions = loadSubscriptions();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = $_POST['customer_id'];
    $paketId = $_POST['paket_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];

    // Check if the customer already has an active subscription
    foreach ($subscriptions['subscriptions'] as $subscription) {
        if ($subscription['customer_id'] === $customerId && $subscription['status'] === 'active') {
            $error = "The selected customer already has an active subscription.";
            break;
        }
    }

    // If no active subscription, create a new subscription
    if (!isset($error)) {
        // Create new subscription
        $newSubscription = [
            'id' => count($subscriptions['subscriptions']) + 1, // Auto-increment ID
            'customer_id' => $customerId,
            'paket_id' => $paketId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ];

        // Append the new subscription and save to the JSON file
        $subscriptions['subscriptions'][] = $newSubscription;
        saveSubscriptions($subscriptions);

        // Redirect to the subscription list page
        header('Location: display_subscriptions.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Add New Subscription</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_subscription.php" method="POST">
            <div class="mb-3">
                <label for="customer_id" class="form-label">Customer</label>
                <select class="form-select" id="customer_id" name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers['customers'] as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['id']); ?>">
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="paket_id" class="form-label">Package</label>
                <select class="form-select" id="paket_id" name="paket_id" required>
                    <option value="">-- Select Package --</option>
                    <?php foreach ($pakets['pakets'] as $paket): ?>
                        <option value="<?php echo htmlspecialchars($paket['id']); ?>">
                            <?php echo htmlspecialchars($paket['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Add Subscription</button>
            <a href="display_subscriptions.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
