<?php
// Include necessary files
require_once __DIR__ . '/Classes/User.php';
session_start(); // Start the session

// Use correct namespaces
use DELIVERY\User\User;

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a random token
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Invalid CSRF token.";
        exit;
    }

    // Retrieve form data
    $name = $_POST['name'];
    $permission = $_POST['permission'];
    $age = $_POST['age'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmpassword'];

    // Basic validation
    $errors = [];

    // Name validation
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name can only contain letters and spaces.";
    }

    // Age validation
    if (!filter_var($age, FILTER_VALIDATE_INT) || $age < 1) {
        $errors[] = "Age must be a valid positive integer.";
    }

    // Username validation
    if (!preg_match("/^(?=.*[a-zA-Z])(?=.*[0-9]).{5,}$/", $username)) {
        $errors[] = "Username must be at least 5 characters long and include both letters and numbers.";
    }

    // Password validation
    if (!preg_match("/^(?=.*[A-Z])(?=.{8,})/", $password)) {
        $errors[] = "Password must be at least 8 characters long and include at least one capital letter.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match!";
    }

    // If there are errors, display them and stop the execution
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>Error: $error</p>";
        }
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
