<?php
session_start(); // Always start the session to access session variables

// Check if 'role' (permission) is set in the session
if (isset($_SESSION['permission'])) {
    $userRole = $_SESSION['permission'];
} else {
    $userRole = null; // Assign a default value or handle the missing role
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
</head>
<body>

<?php if ($user['permission'] == 'admin') {
    header("Location: admin_homepage.php");
} elseif ($user['permission'] == 'client') {
    header("Location: client_homepage.php");
} elseif ($user['permission'] == 'driver') {
    header("Location: driver_homepage.php");
} else {
    header("Location: homepage.php"); // Default fallback
}
die();
?>

</body>
</html>
