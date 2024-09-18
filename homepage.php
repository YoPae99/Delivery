<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
echo "Welcome, " . htmlspecialchars($user['Name']) . "!"; // Adjust based on actual user data

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
</head>
<body>
    <p>This is DELIVERY HOMEPAGE</p>
</body>
</html>
