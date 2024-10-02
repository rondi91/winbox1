<?php
// Function to load customer data from the JSON file
function loadCustomers() {
    $customerFile = 'customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to save customer data to the JSON file
function saveCustomers($data) {
    $customerFile = 'customers.json';
    file_put_contents($customerFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load customer data
$customers = loadCustomers();
$customerId = isset($_GET['id']) ? $_GET['id'] : null;
$customer = null;

// Find the customer to edit
foreach ($customers['customers'] as $c) {
    if ($c['id'] == $customerId) {
        $customer = $c;
        break;
    }
}

// If the customer is not found, redirect back
if (!$customer) {
    header('Location: display_customers.php');
    exit;
}

// If the form is submitted, update the customer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($customers['customers'] as &$c) {
        if ($c['id'] == $customerId) {
            $c['name'] = $_POST['name'];
            $c['email'] = $_POST['email'];
            $c['phone'] = $_POST['phone'];
            $c['address'] = $_POST['address'];
            $c['pppoe_username'] = $_POST['pppoe_username'];
            break;
        }
    }
    saveCustomers($customers);
    header('Location: display_customers.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Edit Customer</h1>
        <form action="edit_customer.php?id=<?php echo $customerId; ?>" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="pppoe_username" class="form-label">PPPoE Username (Unique ID)</label>
                <input type="text" class="form-control" id="pppoe_username" name="pppoe_username" value="<?php echo htmlspecialchars($customer['pppoe_username']); ?>" required>
                <small class="form-text text-muted">This should be the unique PPPoE ID from MikroTik.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="display_customers.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
