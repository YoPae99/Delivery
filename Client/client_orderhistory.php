<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Database/Database.php'; 
use DELIVERY\Database\Database;

// Initialize database connection
$db = new Database();
$pdo = $db->getStarted();

// Function to fetch client orders that are delivered with pagination
function fetchDeliveredClientOrdersWithPagination($clientId, $limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT o.OrderId, u.Name, u.Email, o.Address, o.OrderDate, o.Status, o.StatusUpdateAt
        FROM orders o 
        JOIN user u ON o.DriverId = u.ID 
        WHERE o.ClientId = :clientId AND o.Status = 'Delivered'
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch total delivered orders count for a client
function fetchTotalDeliveredOrdersCount($clientId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM orders 
        WHERE ClientId = :clientId AND Status = 'Delivered'
    ");
    $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Check if the session ID is set
if (isset($_SESSION['ID'])) {
    $userId = $_SESSION['ID'];

    // Fetch the user's username
    $stmt = $pdo->prepare("SELECT Name FROM user WHERE ID = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user ? $user['Name'] : 'User'; // Fallback to 'User' if not found

    // Pagination setup
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 7; // Records per page
    $offset = ($page - 1) * $limit; // Calculate offset for SQL query

    // Fetch delivered client orders for the logged-in user with pagination
    $orders = fetchDeliveredClientOrdersWithPagination($userId, $limit, $offset);

    // Fetch total number of delivered orders for pagination
    $totalOrders = fetchTotalDeliveredOrdersCount($userId);
    $totalPages = ceil($totalOrders / $limit);

} else {
    echo "User is not logged in.";
    exit;
}

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
        .pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* Allows pagination items to wrap within the container */
}

.page-item {
    margin: 0 5px; /* Adds some spacing between items */
}
    </style>
    <title>Client Order History</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Client Dashboard</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span style="margin-left: 51px;" class="fs-4">RINDRA</span>
        </a>
        <span style="margin-left: 20px;" class="fs-4" >FAST DELIVERY</span>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a style="font-size: 20px;" href="client_dashboard.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>

                    Dashboard           
                </a>
            </li>
            <li>
                <a style="font-size: 20px;" href="client_createorder.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>

                    Create Order    
                </a>
            </li>
        </ul>
        <hr>
        <footer style="margin-left:1.5%">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
        </div>

    <div class="b-example-divider"></div>

    <div id="status-update-container" class="border rounded p-4">
        <div>
            <h3 style="font-size: 30px">Order History</h3>
            <hr>
        </div>

        <div class="custom-card">
            <table class="table table-secondary table-striped-columns table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Driver Name</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Address</th>
                        <!-- <th scope="col">Contact</th> -->
                        <th scope="col">Order Date</th>
                        <th scope="col">Order Delivered Date</th>
                        <th scope="col">Order Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $index => $entry): ?>
                            <tr>
                                <th scope="row"><?php echo $index + 1; ?></th>
                                <td><?php echo htmlspecialchars($entry['Name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['OrderId'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['Address'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['OrderDate'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['StatusUpdateAt'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['Status'] ?? 'N/A'); ?></td>
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
</div>
</main>
</body>
</html>
