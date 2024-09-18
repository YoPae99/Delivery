<?php
// Include necessary files
require_once 'C:/laragon/www/CaseStudy/Classes/Admin.php';
require_once 'C:/laragon/www/CaseStudy/Classes/User.php';


// Use correct namespaces
use DELIVERY\Admin\Admin;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $age = $_POST['age'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmpassword'];

    // Basic validation
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    // Create an Admin instance (assuming you want to use Admin to create users)
    $admin = new Admin($name, $age, $username, $email, $password);

    // Use the Admin instance to create the user
    $admin->CreateUser($name, $age, $username, $email, $password);

    // Redirect or inform the user of successful sign-up
    echo "User created successfully!";
    // header("Location: success.php"); // Optionally redirect to a success page
} else {
    echo "Invalid request.";
}
?>
