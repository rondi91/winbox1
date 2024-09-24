<?php
require 'vendor/autoload.php'; // Assuming you load the RouterOS API via Composer
require 'config.php';          // Load your configuration (Mikrotik credentials)

use RouterOS\Client;
use RouterOS\Query;

$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

// Fetch the PPPoE username from the AJAX request
$username = $_POST['username'];

// Query to get all interfaces
$allInterfacesQuery = (new Query("/interface/print"));
$interfaces = $client->query($allInterfacesQuery)->read();

// Initialize variable to store the matching interface
$matching_interface = null;

// Loop through all interfaces to find the one that matches the username
foreach ($interfaces as $interface) {
    if (isset($interface['name']) && strpos($interface['name'], $username) !== false) {
        // If the interface name contains the username, we consider it a match
        $matching_interface = $interface['name'];
        break;
    }
}

if ($matching_interface) {
    // Query to fetch traffic data for the user's PPPoE interface
    $trafficQuery = (new Query('/interface/monitor-traffic'))
        ->equal('interface', $matching_interface)
        ->equal('once', true);

    // Execute the query to get traffic data
    $traffic_data = $client->query($trafficQuery)->read();

    // Display the traffic data if available
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
            "tx" => $tx_mbps
        ));
    } else {
        // If traffic data is not available
        echo json_encode(array(
            "error" => "No traffic data available for interface: $matching_interface"
        ));
    }
} else {
    // If no matching interface is found
    echo json_encode(array(
        "error" => "No matching interface found for username: $username"
    ));
}

?>
