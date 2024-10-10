<?php
require '../vendor/autoload.php'; // RouterOS API
require '../config.php';          // Load MikroTik configuration

use RouterOS\Client;
use RouterOS\Query;

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

// Redirect if the customer is not found
if (!$customer) {
    header('Location: display_customers.php');
    exit;
}

// Fetch PPPoE accounts from MikroTik
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

$pppoeQuery = new Query("/ppp/secret/print");
$pppoeAccounts = $client->query($pppoeQuery)->read();

// Function to check if the PPPoE ID is already assigned to another customer
function isPppoeIdTaken($pppoe_id, $customerId, $customers) {
    foreach ($customers['customers'] as $customer) {
        // Check if PPPoE ID is used by another customer (not the one being edited)
        if ($customer['pppoe_id'] === $pppoe_id && $customer['id'] != $customerId) {
            return true;
        }
    }
    return false;
}

// If the form is submitted, update the customer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $pppoe_id = $_POST['pppoe_id']; // PPPoE ID selected from the dropdown

    // Check if the PPPoE ID is already in use by another customer
    if (isPppoeIdTaken($pppoe_id, $customerId, $customers)) {
        $error = "The selected PPPoE ID is already assigned to another customer.";
    } else {
        // Update the customer data
        foreach ($customers['customers'] as &$c) {
            if ($c['id'] == $customerId) {
                $c['name'] = $name;
                $c['email'] = $email;
                $c['phone'] = $phone;
                $c['address'] = $address;
                $c['pppoe_id'] = $pppoe_id;
                break;
            }
        }
        saveCustomers($customers);
        header('Location: display_customers.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Select2 CSS for searchable dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Edit Customer</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
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

            <!-- Dropdown for selecting the PPPoE Username but submitting the PPPoE ID -->
            <div class="mb-3">
                <label for="pppoe_id" class="form-label">PPPoE Username</label>
                <select class="form-select" id="pppoe_id" name="pppoe_id" required>
                    
                    <?php foreach ($pppoeAccounts as $pppoe): ?>
                        <option value="<?php echo htmlspecialchars($pppoe['.id']); ?>" <?php echo ($pppoe['.id'] === $customer['pppoe_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pppoe['name']); ?> (<?php echo htmlspecialchars($pppoe['profile']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Select the PPPoE username. This will save the unique ID.</small>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="display_customers.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Include Bootstrap JS and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize Select2 on the PPPoE select dropdown
        $('#pppoe_id').select2({
            placeholder: "Select a PPPoE User",
            allowClear: true
        });
    </script>
</body>
</html>
