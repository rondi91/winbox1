<?php
require 'vendor/autoload.php'; // Load Composer autoload
require 'config.php';          // Load the router configuration

use RouterOS\Client;
use RouterOS\Query;

// Create the Mikrotik client using the configuration from config.php
$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

// Get all PPPoE secrets (users) using print and read()
$secretQuery = new Query('/ppp/secret/print');
$allUsers = $client->query($secretQuery)->read(); // Fetch users with read()

// Get all active PPPoE connections
$activeQuery = new Query('/ppp/active/print');
$activeUsers = $client->query($activeQuery)->read(); // Fetch active users with read()

// Function to find the profile of an active user by matching it with allUsers
function findUserProfile($activeUserName, $allUsers) {
    foreach ($allUsers as $user) {
        if ($user['name'] === $activeUserName) {
            return isset($user['profile']) ? $user['profile'] : '-'; // Return the profile or default to '-'
        }
    }
    return '-'; // Return '-' if no match is found
}

// Process active and inactive users
$activeUserIds = array_column($activeUsers, 'name');
$inactiveUsers = array_filter($allUsers, function($user) use ($activeUserIds) {
    return !in_array($user['name'], $activeUserIds);
});

// Calculate totals
$totalSecrets = count($allUsers);
$totalActiveUsers = count($activeUsers);
$totalInactiveUsers = count($inactiveUsers);

// Get all profiles
$profileQuery = new Query('/ppp/profile/print');
$profiles = $client->query($profileQuery)->read(); // Fetch profiles with read()

// Handle form submissions for editing users
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Handle single user edit
    if ($action === 'edit_single') {
        $userId = $_POST['id'];
        $newProfile = $_POST['profile'];

        // Remove active connection for the user
        $removeQuery = (new Query('/ppp/active/remove'))
            ->equal('.id', $userId);
        $client->query($removeQuery);

        // Update user profile
        $editQuery = (new Query('/ppp/secret/set'))
            ->equal('.id', $userId)
            ->equal('profile', $newProfile);
        $client->query($editQuery);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage PPPoE Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Manage PPPoE Users</h1>

        <!-- Total Stats -->
        <div class="mb-4">
            <h3>Summary</h3>
            <p>Total Secrets (Users): <strong><?php echo $totalSecrets; ?></strong></p>
            <p>Total Active Users: <strong><?php echo $totalActiveUsers; ?></strong></p>
            <p>Total Inactive Users: <strong><?php echo $totalInactiveUsers; ?></strong></p>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="userTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#activeUsers" role="tab" aria-controls="activeUsers" aria-selected="true">Active Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="inactive-tab" data-bs-toggle="tab" href="#inactiveUsers" role="tab" aria-controls="inactiveUsers" aria-selected="false">Inactive Users</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3" id="userTabContent">
            <!-- Active Users Tab -->
            <div class="tab-pane fade show active" id="activeUsers" role="tabpanel" aria-labelledby="active-tab">
                <h3>Active Users</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Profile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeUsers as $index => $user): ?>
                            <tr>
                                <td><?php echo ($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><a href="http://<?php echo htmlspecialchars($user['address']); ?>" target="_blank"><?php echo htmlspecialchars($user['address']); ?></a></td>
                                <!-- Find and display the profile by looking it up in allUsers -->
                                <td><?php echo htmlspecialchars(findUserProfile($user['name'], $allUsers)); ?></td>
                                <td>
                                    <!-- Edit Button (trigger modal) -->
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="<?php echo $user['.id']; ?>"
                                        data-username="<?php echo htmlspecialchars($user['name']); ?>"
                                        data-profile="<?php echo htmlspecialchars(findUserProfile($user['name'], $allUsers)); ?>">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Inactive Users Tab -->
            <div class="tab-pane fade" id="inactiveUsers" role="tabpanel" aria-labelledby="inactive-tab">
                <h3>Inactive Users</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inactiveUsers as $index => $user): ?>
                            <tr>
                                <td><?php echo ($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo isset($user['profile']) ? htmlspecialchars($user['profile']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit PPPoE Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit PPPoE User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="POST" action="manage_pppoe.php">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="edit-username" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="profile">Select New Profile:</label>
                        <select name="profile" id="edit-profile" class="form-control">
                            <?php foreach ($profiles as $profile): ?>
                                <option value="<?php echo $profile['name']; ?>"><?php echo $profile['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="action" value="edit_single">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>
            </div>
          </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Trigger modal and pass data to it
        document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            var profile = button.getAttribute('data-profile');

            var modal = this;
            modal.querySelector('#edit-id').value = id;
            modal.querySelector('#edit-username').value = username;
            modal.querySelector('#edit-profile').value = profile;
        });
    </script>
</body>
</html>
