<?php
// Path to customer file
$customerFile = 'customers.json';

// Function to load customers from the existing JSON file
function loadCustomers() {
    global $customerFile;
    if (file_exists($customerFile)) {
        $jsonData = file_get_contents($customerFile);
        return json_decode($jsonData, true);
    }
    return ['customers' => []];
}

// Function to save customers to the JSON file
function saveCustomers($customers) {
    global $customerFile;
    file_put_contents($customerFile, json_encode($customers, JSON_PRETTY_PRINT));
}

// Check if a file is uploaded
if (isset($_FILES['customerFile'])) {
    $fileTmpPath = $_FILES['customerFile']['tmp_name'];
    $fileType = $_FILES['customerFile']['type'];

    // Load existing customers
    $customers = loadCustomers();

    // Process CSV file
    if ($fileType === 'text/csv') {
        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            // Skip the first row (header)
            fgetcsv($handle);

            // Read each row and add to customer data
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $customers['customers'][] = [
                    'id' => uniqid(),
                    'name' => $data[0],
                    'email' => $data[1],
                    'phone' => $data[2],
                    'address' => $data[3],
                    'pppoe_id' => $data[4]
                ];
            }
            fclose($handle);
        }
    }
    // Process JSON file
    elseif ($fileType === 'application/json') {
        $jsonData = file_get_contents($fileTmpPath);
        $importedCustomers = json_decode($jsonData, true);
        if (isset($importedCustomers['customers'])) {
            // Merge imported customers into existing customers
            foreach ($importedCustomers['customers'] as $newCustomer) {
                $newCustomer['id'] = uniqid(); // Assign unique ID
                $customers['customers'][] = $newCustomer;
            }
        }
    }

    // Save the updated customer data back to the JSON file
    saveCustomers($customers);

    // Redirect back to the customer page with success message
    header("Location: customers.php?import=success");
    exit;
}
?>
