<?php 

require 'vendor/autoload.php'; // Load the Twilio PHP SDK

use Twilio\Rest\Client;

// Function to send WhatsApp messages
function sendWhatsAppMessage($to, $message) {
    // Twilio credentials
    $sid = 'AC70bded51df0225edbc94c548d2f28ac2';      // Your Twilio Account SID
    $token = '67837c72fb92159e0535f664433bd386';     // Your Twilio Auth Token
    $whatsappNumber = 'whatsapp:+your_twilio_whatsapp_number'; // Your Twilio WhatsApp Number
    
    // Initialize Twilio client
    $client = new Client($sid, $token);
    
    try {
        // Send a WhatsApp message
        $client->messages->create(
            "whatsapp:$to",  // Destination WhatsApp number
            [
                'from' => $whatsappNumber,
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Example of sending billing details to the customer via WhatsApp
function sendBillingDetailsViaWhatsApp($customerPhone, $customerName, $billing) {
    // Format the billing details message
    $message = "Hello $customerName,\n\n";
    $message .= "Here are your billing details:\n";
    $message .= "Billing ID: {$billing['billing_id']}\n";
    $message .= "Amount Due: {$billing['amount']}\n";
    $message .= "Due Date: {$billing['due_date']}\n";
    $message .= "Status: {$billing['status']}\n\n";
    $message .= "Thank you for your payment!\n\n";
    $message .= "Best Regards,\nYour Company Name";
    
    // Send the message to the customer's WhatsApp number
    sendWhatsAppMessage($customerPhone, $message);
}
// After generating or processing a billing
$customerPhone = "+1234567890";  // Customer's phone number (must include country code)
$customerName = "John Doe";
$billing = [
    'billing_id' => '12345',
    'amount' => '100.00 USD',
    'due_date' => '2023-10-15',
    'status' => 'unpaid'
];

// Send the billing details to the customer via WhatsApp
sendBillingDetailsViaWhatsApp($customerPhone, $customerName, $billing);




?>