<?php
require 'vendor/autoload.php'; // Load RouterOS API via Composer
require 'config.php';          // Load your Mikrotik credentials

use RouterOS\Client;
use RouterOS\Query;

$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);
// Check if the username is passed in the request
if (isset($_POST['username'])) {
    $username = $_POST['username'];
} else {
    // If no username is provided, return an error
    echo json_encode(array("error" => "No username provided."));
    exit();
}


// var_dump($username);
// Query to get all interfaces
$allInterfacesQuery = (new Query("/interface/print"));
$interfaces = $client->query($allInterfacesQuery)->read();

// Initialize variable to store the matching interface
$matching_interface = null;

// Loop through all interfaces to find the one that matches the username
foreach ($interfaces as $interface) {
    if (isset($interface['name']) && strpos($interface['name'], $username) !== false) {
        $matching_interface = $interface['name'];
        break;
    }
}

if ($matching_interface) {
    // Query to fetch traffic data for the user's PPPoE interface
    $trafficQuery = (new Query('/interface/monitor-traffic'))
        ->equal('interface', $matching_interface)
        ->equal('once', true);

    $traffic_data = $client->query($trafficQuery)->read();

    if (!empty($traffic_data)) {
        $traffic_data = $traffic_data[0]; // Get the first set of traffic data
        $rx_bytes = $traffic_data['rx-bits-per-second']; // RX in bits per second
        $tx_bytes = $traffic_data['tx-bits-per-second']; // TX in bits per second

        // Convert bits per second to megabits per second (Mbps)
        $rx_mbps = round($rx_bytes / (1024 * 1024), 2);
        $tx_mbps = round($tx_bytes / (1024 * 1024), 2);

        // Return the result as JSON
        echo json_encode(array(
            "rx" => $rx_mbps,
            "tx" => $tx_mbps,

        ));
    } else {
        echo json_encode(array(
            "error" => "No traffic data available for interface: $matching_interface"
        ));
    }
} else {
    echo json_encode(array(
        "error" => "No matching interface found for username: $username"
    ));
}
?>
