<?php
require_once __DIR__ . '/Classes/User.php';

use DELIVERY\User\User;

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $permission = isset($_POST['permission']) ? trim($_POST['permission']) : '';

    if (empty($email) || empty($password)) {
        echo "Both fields are required!";
        exit;
    }

    try {
        $user = User::Login($email, $password);

        if ($user) {
            // Store user information in session
            $_SESSION['user'] = $user;
            // Store user permission level if needed
            $_SESSION['permission'] = $user['Permission'];

            // Redirect to homepage.php
            header("Location: Dashboard/client_dashboard.php");
            exit();
        } else {
            echo "<p style='color: red;'>Invalid email or password!</p>";
        }
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
