<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;

// Initialize error messages
$errorMessage = ''; // For general errors
$priceError = ''; // For price-related errors
$clientIdError = ''; // For Client ID related errors
$addressError = ''; // For Address related errors

// Function to check if ClientId belongs to a driver
function checkClientRole($clientId) {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    if ($conn) {
        $stmt = $conn->prepare("SELECT Permission FROM user WHERE ID = :clientId");
        $stmt->bindParam(':clientId', $clientId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['Permission'] ?? null; // Return the permission role
    }
    return null;
}

// Function to fetch available drivers from the database
function fetchAvailableDrivers() {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    $drivers = [];
    if ($conn) {
        $stmt = $conn->prepare("SELECT ID AS DriverID, Name AS DriverName FROM user WHERE Permission = 'driver' AND AvailabilityStatus = 'on'");
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
        // Update this SQL statement to order by OrderDate in descending order
        $stmt = $conn->prepare("SELECT o.ClientId, o.OrderId, o.Address, o.Price, o.OrderDate, u.Name AS DriverName 
                                 FROM orders o 
                                 LEFT JOIN user u ON o.DriverId = u.ID 
                                 ORDER BY o.OrderDate DESC"); // Added ORDER BY clause
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $orders;
}


// Function to check if ClientId exists in the database and fetch its role
function checkClientIdExists($clientId) {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    if ($conn) {
        $stmt = $conn->prepare("SELECT Permission FROM user WHERE ID = :clientId");
        $stmt->bindParam(':clientId', $clientId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Return permission if exists, otherwise false
    }
    return false;
}

// Initialize orders and available drivers
$orders = fetchOrdersFromDatabase();
$availableDrivers = fetchAvailableDrivers();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Handle delete action
        $orderIdToDelete = $_POST['OrderId'];
        $db = new \DELIVERY\Database\Database();
        $conn = $db->getStarted();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM orders WHERE OrderId = :orderId");
            $stmt->bindParam(':orderId', $orderIdToDelete);
            $stmt->execute();
        }
    } elseif (isset($_POST['ClientId'], $_POST['Address'], $_POST['Price'])) { // Corrected structure here
        // Capture and sanitize the inputs
        $ClientId = htmlspecialchars(trim($_POST['ClientId']));
        $Address = htmlspecialchars(trim($_POST['Address']));
        $DriverId = isset($_POST['DriverId']) ? htmlspecialchars(trim($_POST['DriverId'])) : null; // Allow DriverId to be null
        $Price = htmlspecialchars(trim($_POST['Price']));

        // Validate Client ID
        if (empty($ClientId)) {
            $clientIdError = "Error: Client ID cannot be empty.";
        } else {
            // Check if the Client ID exists
            $clientData = checkClientIdExists($ClientId);
            if (!$clientData) {
                $clientIdError = "Error: Client ID does not exist.";
            } else {
                // Check the permission role of the provided ClientId
                if ($clientData['Permission'] === 'driver') {
                    $errorMessage = "Error: Drivers cannot create orders.";
                }
            }
        }

        // Validate Address
        if (empty($Address)) {
            $addressError = "Error: Address cannot be empty.";
        } elseif (preg_match('/[0-9]/', $Address)) {
            $addressError = "Error: Address cannot contain numbers.";
        }

        // Validate Price
        if (!is_numeric($Price) || $Price <= 0) {
            $priceError = "Error: Price must be a positive number.";
        }

        // If no errors, proceed with order creation
        if (empty($clientIdError) && empty($addressError) && empty($priceError) && empty($errorMessage)) {
            try {
                // Create order even if DriverId is null
                Admin::CreateOrder($ClientId, $Address, $DriverId, $Price);
                // Redirect after successful creation
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMessage = "Error: Client ID invalid. Please choose another.";
                } else {
                    $errorMessage = "An unexpected error occurred: " . $e->getMessage();
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your existing CSS styles */
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
    </style>
    <title>Create Order</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Sidebars examples</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span style="margin-left: 51px;" class="fs-4">RINDRA</span>
        </a>
        <span style="margin-left: 20px;" class="fs-4">FAST DELIVERY</span>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a style="font-size: 20px;" href="admin_dashboard.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>Dashboard</a>
            </li>
            <li>
                <a style="font-size: 20px;" href="admin_trackorder.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>Track Orders</a>
            </li>
        </ul>
        <hr>
        <footer style="margin-left:1.5%">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
    </div>

    <div class="b-example-divider"></div>

    <div id="status-update-container" class="border rounded p-4">
        <form id="status-update" method="post" action="admin_createorder.php">
            <div>
                <h3 style="font-size: 30px">Add Order</h3>
                <hr>
            </div>
            <div class="mb-3">
                <input type="text" name="ClientId" class="form-control" placeholder="Client ID" required>
                <?php if ($clientIdError): ?>
                    <p style='color: red;'><?php echo $clientIdError; ?></p>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <p style='color: red;'><?php echo $errorMessage; ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <input type="text" name="Address" class="form-control" placeholder="Address" required>
                <?php if ($addressError): ?>
                    <p style='color: red;'><?php echo $addressError; ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <input type="text" name="Price" class="form-control" placeholder="Order Price" required>
                <?php if ($priceError): ?>
                    <p style='color: red;'><?php echo $priceError; ?></p>
                <?php endif; ?>
            </div>
            <div>
                <select class="form-select" name="DriverId" aria-label="Select Available Driver" required>
                    <option selected disabled>Select Available Driver</option>
                    <?php foreach ($availableDrivers as $driver): ?>
                        <option value="<?php echo $driver['DriverID']; ?>"><?php echo $driver['DriverName']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary mt-2">Order</button>
            </div>
        </form>

        <br>
        <h3 style="font-size: 20px">Recent Orders</h3>

        <hr>
        <div class="custom-card">
            <table class="table table-secondary table-bordered custom-table">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Client ID</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Address</th>
                        <th scope="col">Price</th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Assigned Driver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $entry): ?>
                            <tr>
                                <td><?php echo $entry['ClientId']; ?></td>
                                <td><?php echo $entry['OrderId']; ?></td>
                                <td><?php echo $entry['Address']; ?></td>
                                <td><?php echo $entry['Price']; ?></td>
                                <td><?php echo $entry['OrderDate']; ?></td>
                                <td><?php echo $entry['DriverName'] ?: 'None'; ?></td>
                                
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
