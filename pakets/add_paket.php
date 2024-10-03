<?php
require '../vendor/autoload.php'; // RouterOS API
require '../config.php';          // Load MikroTik configuration

use RouterOS\Client;
use RouterOS\Query;

// Function to load package data from the JSON file
function loadPakets() {
    $paketFile = 'paket.json';
    if (file_exists($paketFile)) {
        $jsonData = file_get_contents($paketFile);
        return json_decode($jsonData, true);
    }
    return ['pakets' => []];
}

// Function to save package data to the JSON file
function savePakets($data) {
    $paketFile = 'paket.json';
    file_put_contents($paketFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing pakets
$pakets = loadPakets();

// Fetch PPPoE profiles from MikroTik for speed options
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

$profileQuery = new Query("/ppp/profile/print");
$pppoeProfiles = $client->query($profileQuery)->read();

// Function to check if a package name already exists
function isPackageNameTaken($name, $pakets) {
    foreach ($pakets['pakets'] as $paket) {
        if ($paket['name'] === $name) {
            return true; // Package name already exists
        }
    }
    return false; // Package name is available
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $speed = $_POST['speed'];
    $price = $_POST['price'];

    // Check if the package name is already in use
    if (isPackageNameTaken($name, $pakets)) {
        $error = "The package name already exists. Each package name must be unique.";
    } else {
        // Add new paket to the JSON file
        $newPaket = [
            'id' => count($pakets['pakets']) + 1, // Auto-increment ID
            'name' => $name,
            'speed' => $speed,
            'price' => $price
        ];

        // Append the new paket and save to the JSON file
        $pakets['pakets'][] = $newPaket;
        savePakets($pakets);

        // Redirect to the package list page
        header('Location: display_paket.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Paket</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Add New Paket</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_paket.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Package Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <!-- Dropdown for selecting PPPoE Profile (Speed) -->
            <div class="mb-3">
                <label for="speed" class="form-label">Speed (Profile)</label>
                <select class="form-select" id="speed" name="speed" required>
                    <option value="">-- Select Speed --</option>
                    <?php foreach ($pppoeProfiles as $profile): ?>
                        <option value="<?php echo htmlspecialchars($profile['name']); ?>">
                            <?php echo htmlspecialchars($profile['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Select the PPPoE profile speed.</small>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price (IDR)</label>
                <input type="number" class="form-control" id="price" name="price" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Paket</button>
            <a href="display_paket.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
