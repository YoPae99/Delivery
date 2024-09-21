<?php
// Include necessary files
require_once __DIR__ . '/Classes/Admin.php';

// Use correct namespaces
use DELIVERY\Admin\Admin;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $ClientId = $_POST['ClientId'];
    $Address = $_POST['Address'];


    try {
        Admin::CreateOrder($ClientId, $Address);
        
        // Redirect only if the user is successfully created
        header("Location: ../admin_createorder.php");

    } catch (PDOException $e) {
        // Check for duplicate entry error
        if ($e->getCode() == 23000) { 
            echo "<p style='color: red;'>Error: Client ID invalid. Please choose another.</p>";
        } else {
            echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "Invalid request.";
}
?>
