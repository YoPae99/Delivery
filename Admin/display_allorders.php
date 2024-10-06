<?php
session_start();

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Function to fetch orders from the database with pagination
function fetchOrdersFromDatabase($status = null, $clientName = null, $limit = 5, $offset = 0) {
    $db = new Database();
    $conn = $db->getStarted();
    $orders = [];

    if ($conn) {
        // Construct the base query with JOINs between `orders` and `user`
        $baseQuery = "SELECT orders.ClientId, orders.OrderId, orders.Address, orders.OrderDate, orders.Status, user.Email, 
                      driver.Name AS DriverName 
                      FROM orders 
                      JOIN user ON orders.ClientId = user.ID 
                      LEFT JOIN user AS driver ON orders.DriverId = driver.ID";

        // Add condition based on status
        if ($status) {
            if ($status === 'Ongoing') {
                $query = $baseQuery . " WHERE orders.Status IN ('Order Received', 'Order Ready', 'Picked up', 'Pending')";
            } elseif ($status === 'New Order') {
                $query = $baseQuery . " WHERE orders.Status IS NULL OR orders.Status = ''";
            } else {
                $query = $baseQuery . " WHERE orders.Status = :status";
            }
        } else {
            $query = $baseQuery;
        }

        // Add condition for client name search
        if ($clientName) {
            $query .= " WHERE user.Name LIKE :clientName";
        }

        // Add LIMIT and OFFSET for pagination
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($query);

        // Bind parameters if necessary
        if ($status && $status !== 'Ongoing' && $status !== 'New Order') {
            $stmt->bindParam(':status', $status);
        }
        if ($clientName) {
            $clientNameParam = '%' . $clientName . '%';
            $stmt->bindParam(':clientName', $clientNameParam);
        }

        // Bind limit and offset
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $orders;
}

// Function to fetch total number of orders for pagination
function fetchTotalOrdersCount($status = null, $clientName = null) {
    $db = new Database();
    $conn = $db->getStarted();
    $totalOrders = 0;

    if ($conn) {
        $baseQuery = "SELECT COUNT(*) as total FROM orders JOIN user ON orders.ClientId = user.ID";

        if ($status) {
            $query = $baseQuery . " WHERE orders.Status = :status";
        } else {
            $query = $baseQuery;
        }

        if ($clientName) {
            $query .= " WHERE user.Name LIKE :clientName";
        }

        $stmt = $conn->prepare($query);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        if ($clientName) {
            $clientNameParam = '%' . $clientName . '%';
            $stmt->bindParam(':clientName', $clientNameParam);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalOrders = $result['total'];
    }

    return $totalOrders;
}

// Get current page from URL, default is page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 7; // Records per page
$offset = ($page - 1) * $limit; // Calculate offset for SQL query

// Fetch orders with pagination
$orders = fetchOrdersFromDatabase(null, null, $limit, $offset);

// Fetch total number of orders for pagination
$totalOrders = fetchTotalOrdersCount();
$totalPages = ceil($totalOrders / $limit);

// Handle deletion
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

        // Optionally, refresh order list
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle status selection from the dropdown
if (isset($_POST['Select'])) {
    $selectedStatus = $_POST['Select'];

    if ($selectedStatus == 'Proceeded') {
        $orders = fetchOrdersFromDatabase('Delivered', null, $limit, $offset); // Assuming 'Delivered' as proceeded
    } elseif ($selectedStatus == 'Ongoing') {
        $orders = fetchOrdersFromDatabase('Ongoing', null, $limit, $offset); // Fetch ongoing orders with multiple statuses
    } elseif ($selectedStatus == 'New Order') {
        $orders = fetchOrdersFromDatabase('New Order', null, $limit, $offset); // Fetch orders with null or blank status
    } elseif ($selectedStatus == 'Canceled') {
        $orders = fetchOrdersFromDatabase('Canceled', null, $limit, $offset); // Fetch orders with canceled status
    } else {
        $orders = fetchOrdersFromDatabase(null, null, $limit, $offset); // Fetch all orders if 'All' is selected
    }
}

// Handle search by client name
if (isset($_POST['ClientName'])) {
    $clientName = $_POST['ClientName'];
    $orders = fetchOrdersFromDatabase(null, $clientName, $limit, $offset);
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
            width: 150px; /* Set the desired width */
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
                <!-- Search Bar -->
                
            </form>
        </div>
        <br>
        <div class="d-flex align-items-center">
    <form method="post" action="" class="d-flex">
        <input style="width: " type="text" name="ClientName" class="form-control me-2" placeholder="Search by Name" required>
        <button type="submit" class="btn btn-primary btn-sm custom-width-button">Search</button>
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
<!-- Pagination buttons in the middle -->
<div class="pagination mt-4 d-flex justify-content-center">
    <!-- Always show Previous button, but disable it on the first page -->
    <a style="width: 75px;" 
   href="?page=<?php echo $page > 1 ? $page - 1 : 1; ?>" 
   class="btn btn-secondary mx-1 d-flex justify-content-center <?php echo $page == 1 ? 'disabled' : ''; ?>">
    Previous
</a>


    <?php
    // Define the range of visible page numbers
    $range = 2; // Show 2 page numbers before and after the current page
    $start = max(1, $page - $range); // Starting point
    $end = min($totalPages, $page + $range); // End point

    // Show the first page and ellipsis if necessary
    if ($start > 1) {
        echo '<a href="?page=1" class="btn btn-light mx-1">1</a>';
        if ($start > 2) {
            echo '<span class="btn btn-light mx-1 disabled">...</span>';
        }
    }

    // Loop to show page numbers within the calculated range
    for ($i = $start; $i <= $end; $i++) {
        echo '<a href="?page=' . $i . '" class="btn btn-' . ($i == $page ? 'primary' : 'light') . ' mx-1">' . $i . '</a>';
    }

    // Show the last page and ellipsis if necessary
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            echo '<span class="btn btn-light mx-1 disabled">...</span>';
        }
        echo '<a href="?page=' . $totalPages . '" class="btn btn-light mx-1">' . $totalPages . '</a>';
    }
    ?>

    <!-- Always show Next button, but disable it on the last page -->
    <a style="width: 75px;" href="?page=<?php echo $page < $totalPages ? $page + 1 : $totalPages; ?>" 
       class="btn btn-secondary mx-1 d-flex justify-content-center <?php echo $page == $totalPages ? 'disabled' : ''; ?>">
        Next
    </a>
</div>


    </div>
</main>
</body>
</html>
