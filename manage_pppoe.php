<?php
require 'vendor/autoload.php'; // Memuat Composer autoload

use RouterOS\Client;
use RouterOS\Query;

$mikrotikIp = '192.168.9.1'; // Ganti dengan IP Mikrotik Anda
$mikrotikUsername = 'rondi'; // Ganti dengan username Mikrotik Anda
$mikrotikPassword = '21184662'; // Ganti dengan password Mikrotik Anda

// Konfigurasi client dengan parameter yang benar
$client = new Client([
    'host' => $mikrotikIp,
    'user' => $mikrotikUsername,
    'pass' => $mikrotikPassword,
]);

// Menghubungkan ke Mikrotik
try {
    $client->connect(); // Koneksi ke Mikrotik
} catch (Exception $e) {
    die('Koneksi gagal: ' . $e->getMessage());
}

// Mengambil data PPPoE
$query = new Query('/ppp/active/print');
$activeClients = $client->query($query)->read(); // Mengambil klien PPPoE aktif

$query = new Query('/ppp/secret/print');
$allClients = $client->query($query)->read(); // Mengambil semua klien PPPoE
// var_dump($allClients);
// Memisahkan klien aktif dan tidak aktif
$inactiveClients = array_filter($allClients, function ($client) use ($activeClients) {
    foreach ($activeClients as $active) {
        if ($active['name'] === $client['name']) {
            return false; // Jika klien ditemukan di daftar aktif, berarti dia aktif
        }
    }
    return true; // Jika tidak ditemukan, berarti tidak aktif
});

// Menutup koneksi setelah selesai
// $client->disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage PPPoE</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Manage PPPoE</h1>

        <h2>Active PPPoE Clients</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Service</th>
                    <th>IP address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeClients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                        <td><?php echo htmlspecialchars($client['service']); ?></td>
                        <td><?php echo htmlspecialchars($client['address']); ?></td>
                        <td>
                            <!-- Edit Button (trigger modal) -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                            data-id="<?php echo $client['.id']; ?>" 
                            data-username="<?php echo htmlspecialchars($client['name']); ?>" 
                            data-password="<?php echo htmlspecialchars($client['service']); ?>">
                                Edit
                            </button>
                            <!-- Delete Button -->
                            <form method="POST" action="manage_pppoe.php" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?php echo $client['.id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Inactive PPPoE Clients</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inactiveClients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                        <td><?php echo htmlspecialchars($client['password']); ?></td>
                        <td>
                            <!-- Edit Button (trigger modal) -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                            data-id="<?php echo $client['.id']; ?>" 
                            data-username="<?php echo htmlspecialchars($client['name']); ?>" 
                            data-password="<?php echo htmlspecialchars($client['password']); ?>">
                                Edit
                            </button>
                            <!-- Delete Button -->
                            <form method="POST" action="manage_pppoe.php" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?php echo $client['.id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
            <form method="POST" action="manage_pppoe.php" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="edit-username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="edit-password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Trigger modal and pass data to it
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var username = button.data('username');
            var password = button.data('password');

            var modal = $(this);
            modal.find('#edit-id').val(id);
            modal.find('#edit-username').val(username);
            modal.find('#edit-password').val(password);
        });
    </script>
</body>
</html>
