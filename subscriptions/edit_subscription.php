<?php
// Function to load existing data
function loadCustomers() {
    $customerFile = '../customer/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

function loadPakets() {
    $paketFile = '../pakets/paket.json';
    if (file_exists($paketFile)) {
        $jsonData = file_get_contents($paketFile);
        return json_decode($jsonData, true);
    }
    return ['pakets' => []];
}

function loadSubscriptions() {
    $subscriptionFile = 'subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

function saveSubscriptions($data) {
    $subscriptionFile = 'subscriptions.json';
    file_put_contents($subscriptionFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing data
$customers = loadCustomers();
$pakets = loadPakets();
$subscriptions = loadSubscriptions();

// Get subscription ID from query parameters
$subscriptionId = isset($_GET['id']) ? $_GET['id'] : null;
$subscription = null;

// Find the subscription to edit
foreach ($subscriptions['subscriptions'] as $s) {
    if ($s['id'] == $subscriptionId) {
        $subscription = $s;
        break;
    }
}
// Create mapping for easy access to customer and package names
$customerMap = [];
foreach ($customers['customers'] as $customer) {
    $customerMap[$customer['id']] = $customer['name'];
}

// Redirect if the subscription is not found
if (!$subscription) {
    header('Location: display_subscriptions.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPaketId = $_POST['paket_id']; // Only allow changing the package

    // Update the subscription data
    foreach ($subscriptions['subscriptions'] as &$s) {
        if ($s['id'] == $subscriptionId) {
            $s['paket_id'] = $newPaketId; // Update only the package ID
            break;
        }
    }
    saveSubscriptions($subscriptions); // Save the updated data
    header('Location: display_subscriptions.php'); // Redirect to subscription list
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Edit Subscription</h1>
        <form action="edit_subscription.php?id=<?php echo $subscriptionId; ?>" method="POST">
            <div class="mb-3">
                <label for="customer_id" class="form-label">Customer</label>
                <input type="text" class="form-control" id="customer_id" value="<?php echo htmlspecialchars($customerMap[$subscription['customer_id']] ?? 'N/A'); ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="paket_id" class="form-label">Package</label>
                <select class="form-select" id="paket_id" name="paket_id" required>
                    <option value="">-- Select Package --</option>
                    <?php foreach ($pakets['pakets'] as $paket): ?>
                        <option value="<?php echo htmlspecialchars($paket['id']); ?>" <?php echo ($paket['id'] === $subscription['paket_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($paket['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($subscription['start_date']); ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($subscription['end_date']); ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" class="form-control" id="status" value="<?php echo htmlspecialchars($subscription['status']); ?>" readonly>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="display_subscriptions.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
