<?php
// Function to load subscription data from the JSON file
function loadSubscriptions() {
    $subscriptionFile = 'subscriptions.json';
    if (file_exists($subscriptionFile)) {
        $jsonData = file_get_contents($subscriptionFile);
        return json_decode($jsonData, true);
    }
    return ['subscriptions' => []];
}

// Function to save subscription data to the JSON file
function saveSubscriptions($data) {
    $subscriptionFile = 'subscriptions.json';
    file_put_contents($subscriptionFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load existing subscriptions
$subscriptions = loadSubscriptions();
$subscriptionId = isset($_GET['id']) ? $_GET['id'] : null;

if ($subscriptionId !== null) {
    // Find the index of the subscription to delete
    foreach ($subscriptions['subscriptions'] as $index => $subscription) {
        if ($subscription['id'] == $subscriptionId) {
            // Remove the subscription from the array
            array_splice($subscriptions['subscriptions'], $index, 1);
            saveSubscriptions($subscriptions); // Save the updated data back to the file
            break;
        }
    }
}

// Redirect back to the subscription list after deletion
header('Location: display_subscriptions.php');
exit;
?>
