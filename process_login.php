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
            // Debugging: Check if user data is received correctly
            echo "<pre>";
            print_r($user);
            echo "</pre>";

            $_SESSION['user'] = $user;

            // Redirect to homepage.php
            header("Location: homepage.php");
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
