<?php
require '../vendor/autoload.php'; // RouterOS API
require '../config.php';          // Load MikroTik configuration

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
$paketId = isset($_GET['id']) ? $_GET['id'] : null;
$paket = null;

// Find the package to edit
foreach ($pakets['pakets'] as $p) {
    if ($p['id'] == $paketId) {
        $paket = $p;
        break;
    }
}

// Redirect if the package is not found
if (!$paket) {
    header('Location: display_paket.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $speed = $_POST['speed'];
    $price = $_POST['price'];

    // Update the package data
    foreach ($pakets['pakets'] as &$p) {
        if ($p['id'] == $paketId) {
            $p['name'] = $name;
            $p['speed'] = $speed;
            $p['price'] = $price;
            break;
        }
    }
    savePakets($pakets); // Save the updated data
    header('Location: display_paket.php'); // Redirect to package list
    exit;
}

// Fetch PPPoE profiles for speed options
$client = new RouterOS\Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

$profileQuery = new RouterOS\Query("/ppp/profile/print");
$pppoeProfiles = $client->query($profileQuery)->read();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Edit Paket</h1>
        <form action="edit_paket.php?id=<?php echo $paketId; ?>" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Package Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($paket['name']); ?>" required>
            </div>

            <!-- Dropdown for selecting PPPoE Profile (Speed) -->
            <div class="mb-3">
                <label for="speed" class="form-label">Speed (Profile)</label>
                <select class="form-select" id="speed" name="speed" required>
                    <?php foreach ($pppoeProfiles as $profile): ?>
                        <option value="<?php echo htmlspecialchars($profile['name']); ?>" <?php echo ($profile['name'] === $paket['speed']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($profile['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Select the PPPoE profile speed.</small>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price (IDR)</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($paket['price']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="display_paket.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
