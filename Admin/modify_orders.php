<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Check if the session ID is set, otherwise exit
if (!isset($_SESSION['ID'])) {
    echo "User is not logged in.";
    exit;
}

$userId = $_SESSION['ID'];

// Database connection setup
$db = new Database();
$conn = $db->getStarted();

if (!$conn) {
    echo "Database connection failed.";
    exit;
}

// Function to fetch a single order by OrderId
function fetchOrderById($conn, $orderId) {
    $stmt = $conn->prepare("
        SELECT 
            o.OrderId, 
            o.Address, 
            u.Email 
        FROM 
            orders o 
        LEFT JOIN 
            user u ON o.ClientId = u.ID 
        WHERE 
            o.OrderId = :orderId
    ");
    $stmt->bindParam(':orderId', $orderId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to fetch available drivers
function fetchAvailableDrivers($conn) {
    $stmt = $conn->prepare("
        SELECT 
            ID AS DriverID, 
            Name AS DriverName 
        FROM 
            user 
        WHERE 
            Permission = 'driver' 
            AND AvailabilityStatus = 'on'
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if an OrderId is provided for modification
$order = null;
if (isset($_POST['OrderId'])) {
    $orderId = htmlspecialchars($_POST['OrderId']);
    $order = fetchOrderById($conn, $orderId);
}

// Handle form submissions for updating driver and address
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitAll'])) {
    $OrderId = htmlspecialchars($_POST['OrderId']);
    $DriverId = htmlspecialchars($_POST['DriverId']);
    $Address = htmlspecialchars($_POST['Address']);

    if ($OrderId) {
        try {
            // Check if DriverId is 'N/A'
            $driverIdToUpdate = ($DriverId === 'N/A') ? null : $DriverId;

            $stmt = $conn->prepare("UPDATE orders SET DriverId = :driverId, Address = :address WHERE OrderId = :orderId");
            $stmt->bindParam(':driverId', $driverIdToUpdate);
            $stmt->bindParam(':address', $Address);
            $stmt->bindParam(':orderId', $OrderId);
            $stmt->execute();

            // Redirect after successful submission
            header('Location: display_allorders.php');
            exit;
        } catch (PDOException $e) {
            echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: Order ID is required.</p>";
    }
}

$availableDrivers = fetchAvailableDrivers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <title>Modify Orders</title>
</head>
<body>
<main class="container mt-5">
    <h3>Modify Orders</h3>
    
    <?php if ($order): ?>
        <form method="post" action="">
            <input type="hidden" name="OrderId" value="<?php echo $order['OrderId']; ?>">
            
            <div class="mb-3">
                <label for="email" class="form-label">Client Email</label>
                <input type="email" class="form-control" id="email" value="<?php echo $order['Email']; ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="Address" value="<?php echo $order['Address']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="driver" class="form-label">Assign Driver</label>
                <select name="DriverId" class="form-select" required>
                    <option value="N/A" selected>N/A</option>
                    <?php foreach ($availableDrivers as $driver): ?>
                        <option value="<?php echo $driver['DriverID']; ?>"><?php echo $driver['DriverName']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" name="submitAll" class="btn btn-primary">Submit</button>
            <a href="display_allorders.php" class="btn btn-secondary">Go Back</a>
        </form>
    <?php else: ?>
        <p>No order found for modification.</p>
    <?php endif; ?>
</main>
</body>
</html>
