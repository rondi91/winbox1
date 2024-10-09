<?php
require '../vendor/autoload.php'; // Load the dompdf library

use Dompdf\Dompdf;

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

// Generate the HTML for the receipt
$html = '
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
            padding: 3mm; /* Reduced padding */
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
        h1 {
            text-align: center;
            margin: 0;
            padding: 2px; /* Further reduced padding */
            font-size: 11px;
        }
        .details {
            width: 100%;
            font-size: 9px;
            margin-bottom: 5px;
        }
        .details th, .details td {
            padding: 1px 0; /* Further reduced padding */
            text-align: left;
        }
        .total {
            font-size: 11px;
            font-weight: bold;
            margin-top: 3px; /* Reduce margin */
        }
        .footer {
            margin-top: 3px; /* Reduced margin */
            text-align: center;
            font-size: 7px; /* Smaller footer */
            color: #888;
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
                <td>' . htmlspecialchars($customer['id']) . '</td>
            </tr>
            <tr>
                <th>Nama:</th>
                <td>' . htmlspecialchars($customer['name']) . '</td>
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
            <tr class="total">
                <th>Total:</th>
                <td>Rp. ' . number_format($billing['amount'], 2, ',', '.') . '</td>
            </tr>
            <tr>
                <th>Terbilang:</th>
                <td>' . $amountWords . '</td>
            </tr>
        </table>
        <div class="footer">
            <p>“Terima kasih atas kepercayaan Anda membayar melalui loket kami.”</p>
            <p>Simpanlah struk ini sebagai bukti pembayaran Anda. Struk ini merupakan dokumen resmi.</p>
        </div>
    </div>
</body>
</html>
';

// Initialize Dompdf and generate the PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// Set the paper size to 100mm x 80mm (283.465 points x 226.772 points)
$dompdf->setPaper([0, 0, 283.465, 226.772], 'portrait');

// Render the PDF
$dompdf->render();

// Output the generated PDF to the browser
$dompdf->stream("receipt_" . $billing['billing_id'] . ".pdf", ["Attachment" => false]);