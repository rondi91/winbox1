<?php
require '../vendor/autoload.php'; // RouterOS API
require '../config.php';          // Load MikroTik configuration
require '../paths.php';              // Include the file paths

use RouterOS\Client;
use RouterOS\Query;

// Function to load customer data from the JSON file
function loadCustomers() {
    global $customersFilePath; // Use the global variable
    $customerFile = $customersFilePath;
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to save customer data to the JSON file
function saveCustomers($data) {
    global $customersFilePath; // Use the global variable
    $customerFile = $customersFilePath;
    file_put_contents($customerFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Function to load paket data from paket.json
function loadPakets() {
    global $paketFilePath;
    $paketFile = $paketFilePath;
    if (file_exists($paketFile)) {
        $jsonData = file_get_contents($paketFile);
        return json_decode($jsonData, true)['pakets'];
    }
    return [];
}

// Function to load subscriptions data from subscriptions.json
function loadSubscriptions() {
    global $subscriptionsFilePath ;
    $subscriptionFile = $subscriptionsFilePath;
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true)['subscriptions'];
    }
    return [];
}

// Function to save subscription data to subscriptions.json
function saveSubscriptions($data) {
    global $subscriptionsFilePath ;
    $subscriptionFile = $subscriptionsFilePath;
    file_put_contents($subscriptionFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing customers, packages, and subscriptions
$customers = loadCustomers();
$subscriptions = loadSubscriptions();
$pakets = loadPakets();

// Connect to MikroTik
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

// Function to create a PPPoE account
function createPppoeAccount($client, $username, $password, $profile) {
    $query = (new Query('/ppp/secret/add'))
        ->equal('name', $username)
        ->equal('password', $password)
        ->equal('profile', $profile); // Set profile according to selected package

    return $client->query($query)->read();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $pppoeUsername = $_POST['pppoe_username']; // PPPoE username entered in the form
    $pppoePassword = $_POST['pppoe_password']; // PPPoE password entered in the form
    $paketId = $_POST['paket_id']; // Selected package

    // Fetch the selected package profile
    foreach ($pakets as $paket) {
        if ($paket['id'] == $paketId) {
            $profile = $paket['speed']; // Assume 'name' of the package is the profile in MikroTik
            break;
        }
    }

    // var_dump($profile);
    // die();

    // Create the PPPoE account in MikroTik
    $pppoeResult = createPppoeAccount($client, $pppoeUsername, $pppoePassword, $profile);

    if (isset($pppoeResult['!trap'])) {
        $error = "Failed to create PPPoE account: " . $pppoeResult['!trap'][0]['message'];
    } else {
        // Add new customer to the JSON file
        $newCustomer = [
            'id' => count($customers['customers']) + 1, // Auto-increment ID
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'pppoe_id' => $pppoeUsername, // Save PPPoE username
            'paket_id' => $paketId // Save selected package ID
        ];

        // Append the new customer and save to the JSON file
        $customers['customers'][] = $newCustomer;
        saveCustomers($customers);

        // Save subscription data to subscriptions.json
        $newSubscription = [
            'id' => count($subscriptions) + 1,
            'customer_id' => $newCustomer['id'],  // Associate with the new customer
            'paket_id' => $paketId,
            'start_date' => date('Y-m-d'), // Current date as the start date
            'end_date' => date('Y-m-d', strtotime('+1 month')), // Example: 1 month subscription
            'status' => 'active'
        ];

        // Append the new subscription and save to subscriptions.json
        $subscriptions[] = $newSubscription;
        saveSubscriptions(['subscriptions' => $subscriptions]);

        // Redirect to the customer list page
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
    <title>Add New Customer</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Include jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Add New Customer</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_customer.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>

            <!-- Input fields for PPPoE username and password -->
            <div class="mb-3">
                <label for="pppoe_username" class="form-label">PPPoE Username</label>
                <input type="text" class="form-control" id="pppoe_username" name="pppoe_username" required>
            </div>
            <div class="mb-3">
                <label for="pppoe_password" class="form-label">PPPoE Password</label>
                <input type="password" class="form-control" id="pppoe_password" name="pppoe_password" required>
            </div>

            <!-- Dropdown for selecting Paket -->
            <div class="mb-3">
                <label for="paket_id" class="form-label">Select Package</label>
                <select class="form-select" id="paket_id" name="paket_id" required>
                    <option value="">-- Select Paket --</option>
                    <?php foreach ($pakets as $paket): ?>
                        <option value="<?php echo htmlspecialchars($paket['id']); ?>">
                            <?php echo htmlspecialchars($paket['name']); ?> - Rp. <?php echo htmlspecialchars($paket['price']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Add Customer</button>
            <a href="display_customers.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Include Bootstrap JS and Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize Select2 on the Paket select dropdown
        $('#paket_id').select2({
            placeholder: "Select a Package",
            allowClear: true
        });
    </script>
</body>
</html>
