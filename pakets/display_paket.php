<?php
// Function to load package data from the JSON file
function loadPakets() {
    $paketFile = 'paket.json';
    if (file_exists($paketFile)) {
        $jsonData = file_get_contents($paketFile);
        return json_decode($jsonData, true);
    }
    return ['pakets' => []];
}

// Load existing pakets
$pakets = loadPakets();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="display-6 text-center">Package List</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Package Name</th>
                    <th>Speed</th>
                    <th>Price (IDR)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pakets['pakets']) > 0): ?>
                    <?php foreach ($pakets['pakets'] as $paket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($paket['id']); ?></td>
                            <td><?php echo htmlspecialchars($paket['name']); ?></td>
                            <td><?php echo htmlspecialchars($paket['speed']); ?></td>
                            <td><?php echo number_format($paket['price'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="edit_paket.php?id=<?php echo $paket['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_paket.php?id=<?php echo $paket['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this package?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No packages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="add_paket.php" class="btn btn-primary">Add New Paket</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
