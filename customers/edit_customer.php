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

// Connect to MikroTik
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

// Function to get PPPoE account info from MikroTik
function getPppoeAccount($client, $pppoeId) {
    $query = new Query('/ppp/secret/print');
    $query->where('.id', $pppoeId);
    $response = $client->query($query)->read();
    
    
    if (isset($response[0])) {
        return $response[0];  // Return the PPPoE account details
    }
    return null;
}
$pppoeallQuery = new Query("/ppp/secret/print");
$pppoeallAccounts = $client->query($pppoeallQuery)->read();

// var_dump($pppoeallAccounts);
// die();

// Fetch PPPoE account info from MikroTik based on the saved PPPoE ID
$pppoeAccount = getPppoeAccount($client, $customer['pppoe_id']);


// If the PPPoE account is found, populate the username for editing
$pppoeUsername = isset($pppoeAccount['name']) ? $pppoeAccount['name'] : '';
$pppoePassword = '';  // Passwords are typically not retrievable, so it will be left empty

// If the form is submitted, update the customer and PPPoE account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $pppoeUsername = $_POST['pppoe_username']; // PPPoE username entered in the form
    $pppoePassword = $_POST['pppoe_password']; // PPPoE password entered in the form
    $pppoeId = $_POST['pppoe_id']; // PPPoE ID from the dropdown

    // Update the PPPoE account in MikroTik
    $updateQuery = (new Query('/ppp/secret/set'))
        ->equal('.id', $pppoeId)
        ->equal('name', $pppoeUsername);

    // Only update the password if a new one is entered
    if (!empty($pppoePassword)) {
        $updateQuery->equal('password', $pppoePassword);
    }

    $updateResult = $client->query($updateQuery)->read();

    if (isset($updateResult['!trap'])) {
        $error = "Failed to update PPPoE account: " . $updateResult['!trap'][0]['message'];
    } else {
        // Update the customer data in the JSON file
        foreach ($customers['customers'] as &$c) {
            if ($c['id'] == $customerId) {
                $c['name'] = $name;
                $c['email'] = $email;
                $c['phone'] = $phone;
                $c['address'] = $address;
                $c['pppoe_id'] = $pppoeId;  // Save the PPPoE ID
                $c['pppoe_username'] = $pppoeUsername;  // Save the new PPPoE username
                break;
            }
        }

        saveCustomers($customers);
        header('Location: display_customers.php');
        exit;
    }
}

// var_dump($pppoeAccount);
// die()

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

            <!-- Input fields for PPPoE username and password -->
            <div class="mb-3">
                <label for="pppoe_username" class="form-label">PPPoE Username</label>
                <input type="text" class="form-control" id="pppoe_username" name="pppoe_username" value="<?php echo htmlspecialchars($pppoeUsername); ?>" required>
            </div>
            <div class="mb-3">
                <label for="pppoe_password" class="form-label">PPPoE Password</label>
                <input type="password" class="form-control" id="pppoe_password" name="pppoe_password" placeholder="Enter new password (if changing)">
            </div>
   
           <!-- Dropdown for selecting the PPPoE Username but submitting the PPPoE ID -->
           <div class="mb-3">
                <label for="pppoe_id" class="form-label">PPPoE Username</label>
                <select class="form-select" id="pppoe_id" name="pppoe_id" required>

                
                    <?php foreach ($pppoeallAccounts as $pppoe): ?>
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

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
