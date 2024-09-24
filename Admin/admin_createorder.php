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
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Testing
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
    } elseif (isset($_POST['ClientId'], $_POST['Address'], $_POST['DriverId'])) {
        // Handle add order action
        $ClientId = htmlspecialchars($_POST['ClientId']);
        $Address = htmlspecialchars($_POST['Address']);
        $DriverId = htmlspecialchars($_POST['DriverId']); // Capture selected Driver ID

        // Ensure a driver is selected
        if ($DriverId) {
            try {
                Admin::CreateOrder($ClientId, $Address, $DriverId); // Pass Driver ID to CreateOrder method
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo "<p style='color: red;'>Error: Client ID invalid. Please choose another.</p>";
                } else {
                    echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Error: You must select a driver to create an order.</p>";
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
    </style>
    <title>Create Order</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Sidebars examples</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 200px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">RINDRA FAST DELIVERY</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            </li>
            <li>
                <a href="admin_trackorder.php" class="nav-link">Track Orders</a>
            </li>
            <li class="nav-item">
                <a href="../login.php" class="nav-link">Sign out</a>
            </li>
        </ul>
        <hr>
        <footer>&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
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
            </div>
            <div class="mb-3">
                <input type="text" name="Address" class="form-control" placeholder="Address" required>
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
                <button type="submit" class="btn btn-primary mt-2">Submit</button>
            </div>  
        </form>

        <br>
        <hr>
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
                                <td><?php echo $entry['DriverName'] ?: 'No Driver'; ?></td> <!-- Display DriverName -->
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="OrderId" value="<?php echo $entry['OrderId']; ?>">
                                        <button style="width: 100%;" type="submit" name="delete" class="btn btn-danger">Delete</button>
                                    </form>
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
