<?php
// Include RouterOS API library (adjust the path based on your setup)
require 'vendor/autoload.php'; // Or wherever your RouterOS API is located
require 'config.php';          // Load the router configuration

use RouterOS\Client;
use RouterOS\Query;

// Get the username from the query parameter
$username = isset($_GET['username']) ? $_GET['username'] : '';

if (!$username) {
    die("No username provided!");
}

// Create a RouterOS client instance
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

// Query to get user profile and max speed from PPPoE secret
$secretQuery = new Query("/ppp/secret/print");
$allUsers = $client->query($secretQuery)->read();
$userDetails = null;

foreach ($allUsers as $user) {
    if ($user['name'] === $username) {
        $userDetails = $user;
        break;
    }
}

// If the user doesn't exist, show an error
if (!$userDetails) {
    die("User not found!");
}



// Extract user details
$profileData = $userDetails['profile'] ?? 'Unknown';

if (!empty($profileData)) {

    $userProfile = $profileData;

    // Query to fetch profile details (where max speed is stored)
    $profileDetailsQuery = (new Query("/ppp/profile/print"))
        ->where('name', $userProfile);
    $profileDetails = $client->query($profileDetailsQuery)->read();

    if (!empty($profileDetails)) {
        $maxSpeed = $profileDetails[0]['rate-limit']; // Assuming the rate-limit contains the max speed
        
        // If rate-limit is in format "rx/tx", extract the max speed
        $rateLimits = explode("/", $maxSpeed);
        $rxLimit = (isset($rateLimits[0])) ? (int)$rateLimits[0] : 0;
        $txLimit = (isset($rateLimits[1])) ? (int)$rateLimits[1] : 0;
        
        // Assuming you want the higher of the two values
        $maxSpeed = max($rxLimit, $txLimit);

        // echo json_encode(array("maxSpeed" => $maxSpeed));
    } else {
        echo json_encode(array("error" => "No profile details found."));
    }
} else {
    echo json_encode(array("error" => "No PPPoE user found."));
}


// $maxSpeed = isset($userDetails['rate-limit']) ? $userDetails['rate-limit'] : 'Not Set'; // Adjust based on actual structure

// // Assuming max-speed is "rx/tx" format, get only rx
// $maxSpeedArr = explode('/', $maxSpeed);
// $rxLimit = $maxSpeedArr[0] ?? '0';
// $txLimit = $maxSpeedArr[1] ?? '0';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Details for <?php echo htmlspecialchars($username); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Traffic Details for <?php echo htmlspecialchars($username); ?></h1>
    <p>Profile: <?php echo htmlspecialchars($profileData); ?></p>
    <p>Max Speed (Rx/Tx): <?php echo htmlspecialchars($rxLimit . ' / ' . $txLimit); ?></p>

    <!-- Canvas for Traffic Gauge -->
    <h5>Download Speed (Rx)</h5>
    <canvas id="downloadGauge" width="10" height="10"></canvas>
    
    <h5>Upload Speed (Tx)</h5>
    <canvas id="uploadGauge" width="10" height="10"></canvas>

    <div id="trafficDetails"></div>

    <script>
        var maxRx = <?php echo (int)$rxLimit; ?>; // Max download speed from PHP
        var maxTx = <?php echo (int)$txLimit; ?>; // Max upload speed from PHP

        // Initialize the download doughnut gauge
        var downloadCtx = document.getElementById('downloadGauge').getContext('2d');
        var downloadGaugeChart = new Chart(downloadCtx, {
            type: 'doughnut',
            data: {
                labels: ['Used', 'Remaining'],
                datasets: [{
                    data: [0, maxRx], // Initial values
                    backgroundColor: ['#4caf50', '#e0e0e0'],
                    hoverBackgroundColor: ['#66bb6a', '#e0e0e0']
                }]
            },
            options: {
                circumference: 360,
                rotation: -90,
                cutoutPercentage: 70,
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        // Initialize the upload doughnut gauge
        var uploadCtx = document.getElementById('uploadGauge').getContext('2d');
        var uploadGaugeChart = new Chart(uploadCtx, {
            type: 'doughnut',
            data: {
                labels: ['Used', 'Remaining'],
                datasets: [{
                    data: [0, maxTx], // Initial values
                    backgroundColor: ['#f44336', '#e0e0e0'],
                    hoverBackgroundColor: ['#ef5350', '#e0e0e0']
                }]
            },
            options: {
                circumference: 360,
                rotation: -90,
                cutoutPercentage: 70,
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        // Function to fetch real-time traffic data (adjust according to your setup)
        function fetchTrafficData() {
            var username = "<?php echo htmlspecialchars($username); ?>";

            // Send AJAX request to fetch real-time traffic data from PHP (via RouterOS API)
            fetch('get_traffic_details.php?username=' + username)
            .then(response => response.json())
            .then(data => {
                if (data.rx && data.tx) {
                    // Update the gauges with the new traffic data
                    downloadGaugeChart.data.datasets[0].data = [data.rx, maxRx - data.rx];
                    downloadGaugeChart.update();

                    uploadGaugeChart.data.datasets[0].data = [data.tx, maxTx - data.tx];
                    uploadGaugeChart.update();
                }
            })
            .catch(error => console.error('Error fetching traffic data:', error));
        }

        // Fetch traffic data every 2 seconds (real-time updates)
        setInterval(fetchTrafficData, 2000);
    </script>
</body>
</html>
