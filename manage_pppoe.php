<?php
require 'vendor/autoload.php'; // Load Composer autoload
require 'config.php';          // Load the router configuration

use RouterOS\Client;
use RouterOS\Query;

$client = new Client([
    'host' => $mikrotikConfig['host'],
    'user' => $mikrotikConfig['user'],
    'pass' => $mikrotikConfig['pass'],
]);

// Fetch PPPoE secrets (users)
$secretQuery = new Query('/ppp/secret/print');
$allUsers = $client->query($secretQuery)->read(); // Fetch all users

// Fetch active PPPoE connections
$activeQuery = new Query('/ppp/active/print');
$activeUsers = $client->query($activeQuery)->read(); // Fetch active users

// Fetch all profiles
$profileQuery = new Query('/ppp/profile/print');
$profiles = $client->query($profileQuery)->read(); // Fetch profiles

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
                <!-- Live Search for Active Users -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" id="activeSearch" class="form-control" placeholder="Search Active Users by Username">
                    </div>
                </form>

                <h3>Active Users</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><a href="javascript:void(0)" onclick="sortTable(0, 'activeUsersTableBody')">No</a></th>
                            <th><a href="javascript:void(0)" onclick="sortTable(1, 'activeUsersTableBody')">Username</a></th>
                            <th><a href="javascript:void(0)" onclick="sortTable(2, 'activeUsersTableBody')">IP Address</a></th>
                            <th><a href="javascript:void(0)" onclick="sortTable(3, 'activeUsersTableBody')">Profile</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activeUsersTableBody">
                        <?php foreach ($activeUsers as $index => $user): ?>
                            <tr>
                                <td><?php echo ($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><a href="http://<?php echo htmlspecialchars($user['address']); ?>" target="_blank"><?php echo htmlspecialchars($user['address']); ?></a></td>
                                <td><?php echo htmlspecialchars(findUserProfile($user['name'], $allUsers)); ?></td>
                                <td>
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
                <!-- Live Search for Inactive Users -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" id="inactiveSearch" class="form-control" placeholder="Search Inactive Users by Username">
                    </div>
                </form>

                <h3>Inactive Users</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><a href="javascript:void(0)" onclick="sortTable(0, 'inactiveUsersTableBody')">No</a></th>
                            <th><a href="javascript:void(0)" onclick="sortTable(1, 'inactiveUsersTableBody')">Username</a></th>
                            <th><a href="javascript:void(0)" onclick="sortTable(2, 'inactiveUsersTableBody')">Profile</a></th>
                        </tr>
                    </thead>
                    <tbody id="inactiveUsersTableBody">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Live Search for Active Users
        $('#activeSearch').on('input', function() {
            var query = $(this).val();
            $.ajax({
                url: 'search_users.php',
                type: 'POST',
                data: { searchType: 'active', searchQuery: query },
                success: function(data) {
                    $('#activeUsersTableBody').html(data);
                }
            });
        });

        // Live Search for Inactive Users
        $('#inactiveSearch').on('input', function() {
            var query = $(this).val();
            $.ajax({
                url: 'search_users.php',
                type: 'POST',
                data: { searchType: 'inactive', searchQuery: query },
                success: function(data) {
                    $('#inactiveUsersTableBody').html(data);
                }
            });
        });

        // Sorting function for tables
        function sortTable(columnIndex, tableBodyId) {
            var tableBody = document.getElementById(tableBodyId);
            var rows = Array.from(tableBody.getElementsByTagName("tr"));

            // Toggle sorting order
            var isAscending = tableBody.getAttribute("data-sort-order") === "asc";
            tableBody.setAttribute("data-sort-order", isAscending ? "desc" : "asc");

            rows.sort(function(a, b) {
                var cellA = a.getElementsByTagName("td")[columnIndex].innerText.toLowerCase();
                var cellB = b.getElementsByTagName("td")[columnIndex].innerText.toLowerCase();

                if (cellA < cellB) return isAscending ? -1 : 1;
                if (cellA > cellB) return isAscending ? 1 : -1;
                return 0;
            });

            // Clear existing rows and append sorted rows
            tableBody.innerHTML = "";
            rows.forEach(function(row) {
                tableBody.appendChild(row);
            });
        }

        // Trigger modal and pass data to it
        document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            var profile = button.getAttribute('data-profile');

            var modal = this;
            modal.querySelector('#edit-id').value = id;
            modal.querySelector('#edit-username').value = username;

            // Pre-select the old profile in the dropdown
            var profileDropdown = modal.querySelector('#edit-profile');
            profileDropdown.value = profile;  // Set the dropdown to the user's current profile
        });
    </script>
</body>
</html>
