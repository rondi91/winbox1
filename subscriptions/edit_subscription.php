<?php
// Load existing subscriptions
$subscriptions = loadSubscriptions();
$subscriptionId = isset($_GET['id']) ? $_GET['id'] : null;
$subscription = null;

// Find the subscription to edit
foreach ($subscriptions['subscriptions'] as $s) {
    if ($s['id'] == $subscriptionId) {
        $subscription = $s;
        break;
    }
}

// Redirect if the subscription is not found
if (!$subscription) {
    header('Location: display_subscriptions.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = $_POST['customer_id'];
    $paketId = $_POST['paket_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];

    // Update the subscription data
    foreach ($subscriptions['subscriptions'] as &$s) {
        if ($s['id'] == $subscriptionId) {
            $s['customer_id'] = $customerId;
            $s['paket_id'] = $paketId;
            $s['start_date'] = $startDate;
            $s['end_date'] = $endDate;
            $s['status'] = $status;
            break;
        }
    }
    saveSubscriptions($subscriptions); // Save the updated data
    header('Location: display_subscriptions.php'); // Redirect to subscription list
    exit;
}

// Load customers and packages
$customers = loadCustomers();
$pakets = loadPakets();

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
                <select class="form-select" id="customer_id" name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers['customers'] as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['id']); ?>" <?php echo ($customer['id'] === $subscription['customer_id']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo htmlspecialchars($paket['id']); ?>" <?php echo ($paket['id'] === $subscription['paket_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($paket['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($subscription['start_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($subscription['end_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" <?php echo ($subscription['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($subscription['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="display_subscriptions.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
