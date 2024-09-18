<?php
// Include necessary files
require_once __DIR__ . '/Classes/User.php';

// Use correct namespaces
use DELIVERY\User\User;

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

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Use the User class to create the user
    User::CreateUser($name, $age, $username, $email, $hashedPassword);

    // Redirect or inform the user of successful sign-up
    echo "User created successfully!";
    // header("Location: success.php"); // Optionally redirect to a success page
} else {
    echo "Invalid request.";
}
?>
