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
            // Handle multiple statuses
            if (is_array($status)) {
                $statusPlaceholders = implode(',', array_fill(0, count($status), '?')); // Create placeholders for the IN clause
                $query = $baseQuery . " WHERE orders.Status IN ($statusPlaceholders)";
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

        // Bind parameters
        if (is_array($status)) {
            foreach ($status as $index => $stat) {
                $stmt->bindValue($index + 1, $stat); // Bind each status parameter
            }
        } elseif ($status) {
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
        if (isset($_POST['Select'])) {
            $selectedStatus = $_POST['Select'];
        
            if ($selectedStatus == 'Proceeded') {
                $orders = fetchOrdersFromDatabase(['Delivered'], null, $limit, $offset); // Changed to array
            } elseif ($selectedStatus == 'Ongoing') {
                $orders = fetchOrdersFromDatabase(['Pending', 'Order Received', 'Picked up', 'Pending'], null, $limit, $offset); // Changed to array
            } elseif ($selectedStatus == 'New Order') {
                $orders = fetchOrdersFromDatabase(['New Order'], null, $limit, $offset);
            } elseif ($selectedStatus == 'Canceled') {
                $orders = fetchOrdersFromDatabase(['Canceled'], null, $limit, $offset);
            } else {
                $orders = fetchOrdersFromDatabase(null, null, $limit, $offset); // Fetch all orders if 'All' is selected
            }
        }
        

        // Optionally, refresh order list
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle status selection from the dropdown
if (isset($_POST['Select'])) {
    $selectedStatus = $_POST['Select'];

    if ($selectedStatus == 'Order Received' || 
        $selectedStatus == 'Order Ready' || 
        $selectedStatus == 'Picked up' || 
        $selectedStatus == 'Pending' || 
        $selectedStatus == 'Delivered' || 
        $selectedStatus == 'Canceled') {
        $orders = fetchOrdersFromDatabase($selectedStatus, null, $limit, $offset);
    } elseif ($selectedStatus == 'New Order') {
        $orders = fetchOrdersFromDatabase('New Order', null, $limit, $offset); // Fetch orders with null or blank status
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
        .pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* Allows pagination items to wrap within the container */
}

.page-item {
    margin: 0 5px; /* Adds some spacing between items */
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
<div class="d-flex">
    <div class="d-flex align-items-center">
    <form method="post" action="" class="d-flex">
        <input  type="text" name="ClientName" class="form-control me-2" placeholder="Search by Name" required>
        <button style="width: 150px;" type="submit" class="btn btn-primary  custom-width-button">Search</button>
    </form>
</div>
        <div class="d-flex" style="margin-left:40%">
            <form method="post" action="" class="d-flex">
                
            <select class="form-select form-select-sm me-2 custom-width-select" name="Select" aria-label="Small select example">
    <option value="All" selected>All</option>
    <option value="Order Received">Order Received</option>
    <option value="Order Ready">Order Ready</option>
    <option value="Picked up">Picked up</option>
    <option value="Pending">Pending</option>
    <option value="Delivered">Delivered</option>
    <option value="Canceled">Canceled</option>
</select>

                <button style="width: 107px;" type="submit" class="btn btn-primary  custom-width-button">Filter</button>
                <!-- Search Bar -->
                
            </form>
        </div>

        
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
                                     <!-- Modify Form -->
<form action="modify_orders.php" method="post" style="display: inline-block;">
    <input type="hidden" name="OrderId" value="<?php echo $entry['OrderId']; ?>">
    <button type="submit" name="modify" class="btn btn-success">Modify</button>
</form>


    <!-- Delete Form -->
    <form action="" method="post" style="display: inline-block;">
        <input type="hidden" name="OrderId" value="<?php echo $entry['OrderId']; ?>">
        <button type="submit" name="delete" class="btn btn-danger">Delete</button>
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

        <!-- Pagination -->
       <div class="pagination mt-4 d-flex justify-content-center">
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
        </div>


    </div>
</main>
</body>
</html>
