<?php
require '../vendor/autoload.php'; // Include Composer's autoloader
include 'load_data.php'; // Include the data loading functions

use Dompdf\Dompdf;
use Dompdf\Options;

// Load payments data
$payments = loadPayments();
$billings = loadBillings();
$customers = loadCustomers();
$packages = loadPackages();
$subscriptions = loadSubscriptions();

// Instantiate Dompdf
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// Start output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Payments Report</h1>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Billing ID</th>
                <th>Customer Name</th>
                <th>Package</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                    <td><?php echo htmlspecialchars($payment['billing_id']); ?></td>
                    <td>
                        <?php
                        // Find related customer name
                        $customerName = 'N/A'; // Default value
                        foreach ($billings as $billing) {
                            if ($billing['billing_id'] == $payment['billing_id']) {
                                foreach ($customers as $customer) {
                                    if ($customer['id'] == $billing['customer_id']) {
                                        $customerName = htmlspecialchars($customer['name']);
                                        break 2; // Break out of both loops
                                    }
                                }
                            }
                        }
                        echo $customerName;
                        ?>
                    </td>
                    <td>
                        <?php
                        // Find related package name
                        $packageName = 'N/A'; // Default value
                        foreach ($billings as $billing) {
                            if ($billing['billing_id'] == $payment['billing_id']) {
                                foreach ($subscriptions as $subscription) {
                                    if ($subscription['id'] == $billing['subscription_id']) {
                                        foreach ($packages as $package) {
                                            if ($package['id'] == $subscription['paket_id']) {
                                                $packageName = htmlspecialchars($package['name']);
                                                break 2; // Break out of both loops
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        echo $packageName;
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                    <td><?php echo htmlspecialchars($payment['amount']); ?></td>
                    <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Get the HTML content
$html = ob_get_clean();

// Load the HTML content into Dompdf
$dompdf->loadHtml($html);

// (Optional) Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to browser
$dompdf->stream("payments_report_" . date('Y-m-d_H-i-s') . ".pdf", array("Attachment" => true));
exit;
