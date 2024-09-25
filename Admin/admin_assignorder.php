<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;

// Function to fetch available drivers from the database
function fetchAvailableDrivers() {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    $drivers = [];
    if ($conn) {
        $stmt = $conn->prepare("
            SELECT 
                ID AS DriverID, 
                Name AS DriverName 
            FROM 
                user 
            WHERE 
                Permission = 'driver' 
                AND AvailabilityStatus = 'on'  -- Ensure only available drivers are fetched
        ");
        $stmt->execute();
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $drivers;
}

// Function to fetch orders from the database
function fetchOrdersFromDatabase() {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    $orders = [];
    if ($conn) {
        $stmt = $conn->prepare("
            SELECT 
                o.ClientId, 
                o.OrderId, 
                o.Address, 
                o.OrderDate, 
                u.Name AS DriverName 
            FROM 
                orders o 
            LEFT JOIN 
                user u ON o.DriverId = u.ID
            WHERE 
                o.DriverId IS NULL  -- Only fetch orders without a driver assigned
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For testing purposes: print the fetched orders
        // var_dump($orders); 
    }
    return $orders;
}


// Initialize orders variable
$orders = fetchOrdersFromDatabase();

// Initialize available drivers variable
$availableDrivers = fetchAvailableDrivers();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Handle delete action
        $orderIdToDelete = $_POST['OrderId'];

        // Delete from the database
        $db = new \DELIVERY\Database\Database();
        $conn = $db->getStarted();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM orders WHERE OrderId = :orderId");
            $stmt->bindParam(':orderId', $orderIdToDelete);
            $stmt->execute();
        }
    } elseif (isset($_POST['ClientId'], $_POST['Address'], $_POST['DriverId'], $_POST['OrderId'])) {
        // Handle add order action
        $ClientId = htmlspecialchars($_POST['ClientId']);
        $Address = htmlspecialchars($_POST['Address']);
        $DriverId = htmlspecialchars($_POST['DriverId']); // Capture selected Driver ID
        $OrderId = htmlspecialchars($_POST['OrderId']); // Capture Order ID

        // Ensure a driver is selected
        if ($DriverId && $OrderId) {
            try {
                $db = new \DELIVERY\Database\Database();
                $conn = $db->getStarted();

                if ($conn) {
                    // Update the order with the selected driver
                    $stmt = $conn->prepare("UPDATE orders SET DriverId = :driverId WHERE OrderId = :orderId");
                    $stmt->bindParam(':driverId', $DriverId);
                    $stmt->bindParam(':orderId', $OrderId);
                    $stmt->execute();
                }
                
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo "<p style='color: red;'>Error: Client ID invalid. Please choose another.</p>";
                } else {
                    echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Error: You must select a driver and an order to assign.</p>";
        }
    }

    // Fetch the updated orders after any action
    $orders = fetchOrdersFromDatabase();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <style>
        #status-update-container {
            margin-left: 0%;
            width: 78em;
            background-color: #f8f9fa;
        }
        .border {
            border: 1px solid #ced4da;
        }
        .rounded {
            border-radius: 0.25rem;
        }
        .custom-card {
            margin-top: 20px;
            height: 380px;
            min-height: 300px;
            overflow-y: auto;
        }
        footer {
            position: absolute;
            bottom: 10px;
            font-size: 14px;
            color: #aaa;
        }
        .custom-width-select {
            width: 250px; /* Set the desired width */
        }
        .custom-width-button {
            width: 100px; /* Set the desired width */
        }
    </style>
    <title>Assign Driver</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span style="margin-left: 51px;" class="fs-4">RINDRA</span>
        </a>
        <span style="margin-left: 20px;" class="fs-4" >FAST DELIVERY</span>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a style="font-size: 20px;" href="admin_dashboard.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                    Dashboard</a>
            </li>
            <li>
                <a style="font-size: 20px;" href="admin_trackorder.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                    Track Orders</a>
            </li>
        </ul>
        <hr>
        <footer style="margin-left:1.5%">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
        </div>

    <div class="b-example-divider"></div>

    <div id="status-update-container" class="border rounded p-4">
        
            <div>
                <h3 style="font-size: 30px">Assign Driver</h3>
                <hr>
            </div>
        <div class="custom-card">
            <table class="table table-secondary table-bordered custom-table">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Client ID</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Address</th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Assigned Driver</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $entry): ?>
                            <tr>
                                <td><?php echo $entry['ClientId']; ?></td>
                                <td><?php echo $entry['OrderId']; ?></td>
                                <td><?php echo $entry['Address']; ?></td>
                                <td><?php echo $entry['OrderDate']; ?></td>
                                <td><?php echo $entry['DriverName'] ?: 'None'; ?></td> <!-- Display DriverName -->
                                <td>
    <div class="d-flex align-items-center">
        <form method="post" action="" class="d-flex">   
            <input type="hidden" name="ClientId" value="<?php echo $entry['ClientId']; ?>"> <!-- Include ClientId -->
            <input type="hidden" name="Address" value="<?php echo $entry['Address']; ?>"> <!-- Include Address -->
            <input type="hidden" name="OrderId" value="<?php echo $entry['OrderId']; ?>"> <!-- Include OrderId -->
            <select class="form-select form-select-sm me-2 custom-width-select" name="DriverId" aria-label="Select Available Driver" required>
                <option selected disabled>Select Available Driver</option>
                    <?php foreach ($availableDrivers as $driver): ?>
                <option value="<?php echo $driver['DriverID']; ?>"><?php echo $driver['DriverName']; ?></option>
                    <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-success btn-sm custom-width-button">Assign</button>
        </form>
    </div>
</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
