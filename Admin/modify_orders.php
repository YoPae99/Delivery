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
            u.Email,
            o.Status, 
            o.ClientId  -- Fetch the ClientId for email update
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
            // Fetch the current status of the order
            $stmt = $conn->prepare("SELECT Status, DriverId FROM orders WHERE OrderId = :orderId");
            $stmt->bindParam(':orderId', $OrderId);
            $stmt->execute();
            $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            $orderStatus = $orderData['Status'];
            $currentDriverId = $orderData['DriverId'];

            // Prepare the SQL update statement
            $sql = "UPDATE orders SET Address = :address"; // Start with address
            $params = [':address' => $Address]; // Parameters array

            // Check if a new driver is selected
            if (!empty($DriverId) && $DriverId !== $currentDriverId) {
                // Only update DriverId if the status allows it
                if (!in_array($orderStatus, ['Picked up', 'Pending', 'Delivered'])) {
                    // Update driver
                    $driverIdToUpdate = ($DriverId === 'N/A') ? null : $DriverId;
                    $sql .= ", DriverId = :driverId"; // Add driver update to the SQL statement
                    $params[':driverId'] = $driverIdToUpdate; // Add driver parameter
                } else {
                    // Inform the user that the driver cannot be reassigned
                    echo '<div class="alert alert-danger" role="alert">Warning: The order status is ' . htmlspecialchars($orderStatus) . '. Driver cannot be changed.</div>';
                }
            }

            $sql .= " WHERE OrderId = :orderId"; // Add where clause for the OrderId
            $params[':orderId'] = $OrderId; // Add order ID parameter

            // Prepare and execute the update statement
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            // Redirect after successful submission
            // header('Location: display_allorders.php');
            // exit;
        } catch (PDOException $e) {
            // Handle any errors here
            $errors[] = "Failed to update order: " . $e->getMessage();
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
            <input type="email" class="form-control" id="email" name="Email" value="<?php echo isset($order['Email']) ? htmlspecialchars($order['Email']) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="Address" value="<?php echo isset($order['Address']) ? htmlspecialchars($order['Address']) : ''; ?>" required>
        </div>

        <div class="mb-3">
    <label for="driver" class="form-label">Reassign Driver</label>
    <select name="DriverId" class="form-select">
        <option value="">Select a Driver</option>
        <?php foreach ($availableDrivers as $driver): ?>
            <option value="<?php echo htmlspecialchars($driver['DriverID']); ?>" 
                <?php echo (isset($order['DriverId']) && $driver['DriverID'] == $order['DriverId']) ? 'selected' : ''; ?>> 
                <?php echo htmlspecialchars($driver['DriverName']); ?>
            </option>
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
