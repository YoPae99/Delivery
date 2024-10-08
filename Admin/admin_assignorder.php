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

// Function to fetch orders without a driver assigned
function fetchOrdersFromDatabase($conn) {
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
            o.DriverId IS NULL
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Handle form submissions (Assign Driver / Delete Order)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Handle delete action
        $orderIdToDelete = $_POST['OrderId'];
        $stmt = $conn->prepare("DELETE FROM orders WHERE OrderId = :orderId");
        $stmt->bindParam(':orderId', $orderIdToDelete);
        $stmt->execute();
    } elseif (isset($_POST['ClientId'], $_POST['Address'], $_POST['DriverId'], $_POST['OrderId'])) {
        // Handle assigning driver to order
        $ClientId = htmlspecialchars($_POST['ClientId']);
        $Address = htmlspecialchars($_POST['Address']);
        $DriverId = htmlspecialchars($_POST['DriverId']); 
        $OrderId = htmlspecialchars($_POST['OrderId']);

        if ($DriverId && $OrderId) {
            try {
                $stmt = $conn->prepare("UPDATE orders SET DriverId = :driverId WHERE OrderId = :orderId");
                $stmt->bindParam(':driverId', $DriverId);
                $stmt->bindParam(':orderId', $OrderId);
                $stmt->execute();
            } catch (PDOException $e) {
                echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Error: You must select a driver and an order to assign.</p>";
        }
    }

    // After form submission, redirect to avoid resubmission on page refresh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
$orders = fetchOrdersFromDatabase($conn);
$availableDrivers = fetchAvailableDrivers($conn);
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

        <!-- Pagination -->
       <!-- <div class="pagination mt-4 d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=1" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if($page >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item <?php if($page >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $totalPages; ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        </div> -->

    </div>
</main>
</body>
</html>
