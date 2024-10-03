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

// Function to save package data to the JSON file
function savePakets($data) {
    $paketFile = 'paket.json';
    file_put_contents($paketFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing pakets
$pakets = loadPakets();
$paketId = isset($_GET['id']) ? $_GET['id'] : null;

if ($paketId !== null) {
    // Find the index of the package to delete
    foreach ($pakets['pakets'] as $index => $paket) {
        if ($paket['id'] == $paketId) {
            // Remove the package from the array
            array_splice($pakets['pakets'], $index, 1);
            savePakets($pakets); // Save the updated data back to the file
            break;
        }
    }
}

// Redirect back to the package list after deletion
header('Location: display_paket.php');
exit;
?>
