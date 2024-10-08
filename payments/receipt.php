<?php
require '../vendor/autoload.php'; // Include necessary dependencies if needed

// Load billing and customer data
function loadBillings() {
    $billingFile = '../billings/billing.json';
    if (file_exists($billingFile)) {
        $jsonData = file_get_contents($billingFile);
        return json_decode($jsonData, true);
    }
    return ['billings' => []];
}

function loadCustomers() {
    $customerFile = '../customer/customers.json';
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to convert number to words (for Indonesian currency)
function terbilang($number) {
    $words = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
    ];
    if ($number < 12) {
        return $words[$number];
    } elseif ($number < 20) {
        return terbilang($number - 10) . ' Belas';
    } elseif ($number < 100) {
        return terbilang(floor($number / 10)) . ' Puluh ' . terbilang($number % 10);
    } elseif ($number < 1000) {
        return terbilang(floor($number / 100)) . ' Ratus ' . terbilang($number % 100);
    } elseif ($number < 1000000) {
        return terbilang(floor($number / 1000)) . ' Ribu ' . terbilang($number % 1000);
    }
    return 'Jumlah terlalu besar';
}

// Get billing ID from query parameter
$billingId = isset($_GET['billing_id']) ? $_GET['billing_id'] : null;

// Load data
$billings = loadBillings();
$customers = loadCustomers();

// Find the billing record by ID
$billing = null;
foreach ($billings['billings'] as $b) {
    if ($b['billing_id'] == $billingId) {
        $billing = $b;
        break;
    }
}

// If no billing is found, display an error
if (!$billing) {
    die("Billing record not found.");
}

// Find the corresponding customer
$customer = null;
foreach ($customers['customers'] as $c) {
    if ($c['id'] == $billing['customer_id']) {
        $customer = $c;
        break;
    }
}

// Convert amount to words (for Indonesian currency)
$amount = intval($billing['amount']);
$amountWords = strtoupper(terbilang($amount)) . ' RUPIAH';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .receipt {
            width: 100mm;
            height: 80mm;
            margin: 0 auto;
            padding: 10mm;
            border: 1px solid #ddd;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        h1, h2, h4 {
            text-align: center;
            margin: 5px 0;
            font-size: 12px;
        }
        .details {
            margin-bottom: 10px;
        }
        .details th, .details td {
            padding: 2px 5px;
            text-align: left;
            font-size: 10px;
        }
        .total {
            font-size: 12px;
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #888;
        }
        .highlight {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h1>STRUK PEMBAYARAN TAGIHAN WIFI</h1>

        <table class="details">
            <tr>
                <th>Tanggal Bayar:</th>
                <td>2024-10-03</td>
            </tr>
            <tr>
                <th>No. Pelanggan:</th>
                <td><?php echo htmlspecialchars($customer['id']); ?></td>
            </tr>
            <tr>
                <th>Nama:</th>
                <td><?php echo htmlspecialchars($customer['name']); ?></td>
            </tr>
            <tr>
                <th>Kecepatan:</th>
                <td>2 Mbps</td>
            </tr>
            <tr>
                <th>Harga Paket:</th>
                <td>Rp. 100.000,00</td>
            </tr>
            <tr>
                <th>Admin Bank:</th>
                <td>Rp. 0</td>
            </tr>
            <tr class="highlight">
                <th>Total:</th>
                <td>Rp. <?php echo number_format($billing['amount'], 2, ',', '.'); ?></td>
            </tr>
            <tr>
                <th>Terbilang:</th>
                <td><?php echo $amountWords; ?></td>
            </tr>
        </table>

        <div class="footer">
            <p>“Terima kasih atas kepercayaan Anda membayar melalui loket kami.”</p>
            <p>Simpanlah struk ini sebagai bukti pembayaran Anda. Struk ini merupakan dokumen resmi.</p>
        </div>
    </div>
</body>
</html>
