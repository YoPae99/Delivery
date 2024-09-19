<?php
// Include necessary files
require_once __DIR__ . '/Classes/User.php';

// Use correct namespaces
use DELIVERY\User\User;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $permission = $_POST['permission'];
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

    // Try to create the user and handle duplicate errors
    try {
        User::CreateUser($name, $permission, $age, $username, $email, $hashedPassword);

        // Redirect only if the user is successfully created
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        // Check for duplicate entry error
        if ($e->getCode() == 23000) { 
            echo "<p style='color: red;'>Error: Username or Email already exists. Please choose another.</p>";
        } else {
            echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "Invalid request.";
}
?>
