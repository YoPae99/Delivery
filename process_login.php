<?php
require_once __DIR__ . '/Classes/User.php';

use DELIVERY\User\User;

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        echo "Both fields are required!";
        exit;
    }

    try {
        $user = User::Login($email, $password);

        if ($user) {
            // Store user information in session
            $_SESSION['user'] = $user; // Store entire user info
            $_SESSION['ID'] = $user['ID']; // Store user ID

            // Redirect based on permission level
            if ($user['Permission'] == 'client') {
                header("Location: Client/client_dashboard.php");
                exit();
            } elseif ($user['Permission'] == 'driver') {
                header("Location: Driver/driver_dashboard.php");
                exit();
            } elseif ($user['Permission'] == 'admin') {
                header("Location: Admin/admin_dashboard.php");
                exit();
            }
        } else {
            echo "<p style='color: red;'>Invalid email or password!</p>";
        }
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
