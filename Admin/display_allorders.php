<?php
session_start();

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Function to fetch orders and emails from the database
function fetchOrdersFromDatabase($status = null) {
    $db = new Database();
    $conn = $db->getStarted();
    $orders = [];

    if ($conn) {
        // Construct the base query with JOINs between `orders` and `user`
        $baseQuery = "SELECT orders.ClientId, orders.OrderId, orders.Address, orders.OrderDate, orders.Status, user.Email, 
                      driver.Name AS DriverName 
                      FROM orders 
                      JOIN user ON orders.ClientId = user.ID 
                      LEFT JOIN user AS driver ON orders.DriverId = driver.ID"; // Join to fetch driver's name

        // Add condition based on status
        if ($status) {
            if ($status === 'Ongoing') {
                // Fetch ongoing orders with multiple statuses
                $query = $baseQuery . " WHERE orders.Status IN ('Order Received', 'Order Ready', 'Picked up', 'Pending')";
            } elseif ($status === 'New Order') {
                // Fetch orders with null or blank status
                $query = $baseQuery . " WHERE orders.Status IS NULL OR orders.Status = ''";
            } else {
                // Fetch orders based on the provided status
                $query = $baseQuery . " WHERE orders.Status = :status";
            }
        } else {
            // Fetch all orders
            $query = $baseQuery;
        }

        $stmt = $conn->prepare($query);

        // Bind parameters if necessary
        if ($status && $status !== 'Ongoing' && $status !== 'New Order') {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $orders;
}


// Initialize orders variable
$orders = fetchOrdersFromDatabase();

//Deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle delete action 
    if (isset($_POST['delete'])) {
        $orderIdToDelete = $_POST['OrderId'];

        // Delete from the database
        $db = new Database();
        $conn = $db->getStarted();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM orders WHERE OrderId = :orderId");
            $stmt->bindParam(':orderId', $orderIdToDelete);
            $stmt->execute();
        }
        // Optionally, redirect to the same page to refresh order list
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}


    // Handle status selection from the dropdown
    if (isset($_POST['Select'])) {
        $selectedStatus = $_POST['Select'];

        if ($selectedStatus == 'Proceeded') {
            $orders = fetchOrdersFromDatabase('Delivered'); // Assuming 'Delivered' as proceeded
        } elseif ($selectedStatus == 'Ongoing') {
            $orders = fetchOrdersFromDatabase('Ongoing'); // Fetch ongoing orders with multiple statuses
        } elseif ($selectedStatus == 'New Order') {
            $orders = fetchOrdersFromDatabase('New Order'); // Fetch orders with null or blank status
        } elseif ($selectedStatus == 'Canceled') {
            $orders = fetchOrdersFromDatabase('Canceled'); // Fetch orders with null or blank status
        } else {
            $orders = fetchOrdersFromDatabase(); // Fetch all orders if 'All' is selected
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
            height: 440px; /* Fixed height */
            min-height: 300px; /* Ensure there’s enough space for content */
            overflow-y: auto; /* Enable vertical scrolling */
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
    <title>Order Record</title>
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
            <h3 style="font-size: 30px">Order Record</h3>
            <hr>
        </div>

        <div class="d-flex align-items-center">
            <form method="post" action="" class="d-flex">
                <select class="form-select form-select-sm me-2 custom-width-select" name="Select" aria-label="Small select example">
                    <option value="All" selected>All</option>
                    <option value="New Order">New Order</option>
                    <option value="Ongoing">On Going</option>
                    <option value="Proceeded">Completed</option>
                    <option value="Canceled">Canceled</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm custom-width-button">Filter</button>
            </form>
        </div>

        <div class="custom-card">
            <table class="table table-secondary table-striped-columns table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Client ID</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Address</th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Order Status</th>
                        <th scope="col">Assigned Driver</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $index => $entry): ?>
                            <tr>
                                <th scope="row"><?php echo $index + 1; ?></th>
                                <td><?php echo $entry['ClientId']; ?></td>
                                <td><?php echo $entry['OrderId']; ?></td>
                                <td><?php echo $entry['Email']; ?></td>
                                <td><?php echo $entry['Address']; ?></td>
                                <td><?php echo $entry['OrderDate']; ?></td>
                                <td><?php echo $entry['Status'] ?: 'No Status'; ?></td>
                                <td><?php echo $entry['DriverName'] ?: 'None'; ?></td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="OrderId" value="<?php echo $entry['OrderId']; ?>">
                                        <button style="width: 100%;" type="submit" name="delete" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
