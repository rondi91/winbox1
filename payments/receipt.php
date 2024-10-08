<?php
require '../vendor/autoload.php'; // Make sure you have dompdf installed
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

// Generate HTML content for the receipt
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
        }
        .details {
            margin-bottom: 20px;
        }
        .details th, .details td {
            padding: 5px 10px;
            text-align: left;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h1>Payment Receipt</h1>
        <h2>Billing ID: ' . htmlspecialchars($billing['billing_id']) . '</h2>

        <table class="details">
            <tr>
                <th>Customer Name:</th>
                <td>' . htmlspecialchars($customer['name']) . '</td>
            </tr>
            <tr>
                <th>Billing Date:</th>
                <td>' . htmlspecialchars($billing['billing_date']) . '</td>
            </tr>
            <tr>
                <th>Due Date:</th>
                <td>' . htmlspecialchars($billing['due_date']) . '</td>
            </tr>
            <tr>
                <th>Amount Paid:</th>
                <td>' . htmlspecialchars($billing['amount']) . '</td>
            </tr>
            <tr>
                <th>Payment Status:</th>
                <td>' . htmlspecialchars($billing['status']) . '</td>
            </tr>
        </table>

        <p class="total">Total Amount: $' . htmlspecialchars($billing['amount']) . '</p>

        <div class="footer">
            Thank you for your payment! If you have any questions, please contact us at support@example.com
        <div class="footer">
            Thank you for your payment! If you have any questions, please contact us at support@example.com.
        </div>
    </div>
</body>
</html>
';

// Initialize Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// Set paper size and orientation (optional)
$dompdf->setPaper('A4', 'portrait');

// Render the PDF
$dompdf->render();

// Output the generated PDF to the browser
$dompdf->stream("receipt_" . $billing['billing_id'] . ".pdf", ["Attachment" => false]);
