<?php
// bulk_update.php

require 'vendor/autoload.php'; // RouterOS API and other dependencies
require 'config.php';          // Load the router configuration

use RouterOS\Client;
use RouterOS\Query;

// Check if form was submitted and users were selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_ids']) && isset($_POST['newProfile'])) {
    $userNames = $_POST['user_ids'];    // List of selected user names
    $newProfile = $_POST['newProfile']; // New profile to set
    

    // Create RouterOS client instance
    $client = new Client([
        'host' => $mikrotikConfig['host'],
        'user' => $mikrotikConfig['user'],
        'pass' => $mikrotikConfig['pass'],
    ]);

    foreach ($userNames as $username) {
        // Step 1: Fetch user details from /ppp/secret to get the .id based on the username
        $secretQuery = (new Query("/ppp/secret/print"))
            ->where("name", $username);  // Match the user by name

        $userSecret = $client->query($secretQuery)->read();

        // var_dump($userSecret);
        // die();

        // Ensure the user exists
        if (isset($userSecret[0])) {
            $userSecretId = $userSecret[0]['.id'];  // Fetch the .id for the secret
           

            // Step 2: Update the user's profile using the .id
            $updateQuery = (new Query("/ppp/secret/set"))
                ->equal('.id', $userSecretId)    // Use .id to update
                ->equal('profile', $newProfile); // Update profile

            // Send update request to RouterOS API and get the response
            $response = $client->query($updateQuery)->read();


            // Check if there is any error in the response
            if (isset($response['!trap'])) {
                // An error occurred during the update
                echo "Error updating profile for user: " . $username . "\n";
            } else {
                // Update was successful, proceed to remove active connection
                echo "Profile updated for user: " . $username . "\n";

                // Step 3: Remove the active connection if the user is active
                $activeQuery = (new Query("/ppp/active/print"))
                    ->where("name", $username);    // Match the username in active connections

                $activeConnection = $client->query($activeQuery)->read();

                // If the user is active, remove the active connection
                if (isset($activeConnection[0]['.id'])) {
                    $activeConnectionId = $activeConnection[0]['.id'];

                    // Remove the active connection
                    $removeActiveQuery = (new Query("/ppp/active/remove"))
                        ->equal('.id', $activeConnectionId);  // Match by active connection ID
                    $client->query($removeActiveQuery)->read();
                }
            }
        }
    }

    // Redirect back to the manage page with a success message
    header("Location: manage_pppoe.php?message=Bulk update successful");
    exit();
} else {
    // No users selected or profile not set
    header("Location: manage_pppoe.php?message=No users selected or profile not set");
    exit();
}
