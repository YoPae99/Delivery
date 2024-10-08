<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;

// Ensure the client is logged in and retrieve their ID
if (!isset($_SESSION['ID'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

$clientId = $_SESSION['ID']; // Retrieve logged-in client ID

// Initialize error messages
$errorMessage = ''; // For general errors
$priceError = ''; // For price-related errors
$addressError = ''; // For address-related errors

// Function to fetch orders for the logged-in client with pagination
function fetchClientOrders($clientId, $start, $limit) {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    $orders = [];
    if ($conn) {
        // Select only the orders for the currently logged-in client with LIMIT and OFFSET
        $stmt = $conn->prepare("
            SELECT o.ClientId, o.OrderId, o.Address, o.Price, o.OrderDate
            FROM orders o 
            WHERE o.ClientId = :clientId
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $start, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $orders;
}

// Function to count the total number of orders for the logged-in client
function countClientOrders($clientId) {
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    if ($conn) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM orders 
            WHERE ClientId = :clientId
        ");
        $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    return 0;
}

// Pagination setup
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page or default to 1
$start = ($page - 1) * $limit; // Calculate offset
$totalOrders = countClientOrders($clientId); // Total number of orders
$totalPages = ceil($totalOrders / $limit); // Calculate total pages

// Fetch orders for the current page
$orders = fetchClientOrders($clientId, $start, $limit);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Handle delete action
        $orderIdToDelete = $_POST['OrderId'];
        $db = new \DELIVERY\Database\Database();
        $conn = $db->getStarted();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM orders WHERE OrderId = :orderId AND ClientId = :clientId");
            $stmt->bindParam(':orderId', $orderIdToDelete);
            $stmt->bindParam(':clientId', $clientId); // Ensure the deletion is for the current client's order
            $stmt->execute();
        }
    } elseif (isset($_POST['Address'], $_POST['Price'])) {
        // Capture and sanitize the inputs
        $Address = htmlspecialchars(trim($_POST['Address']));
        $Price = htmlspecialchars(trim($_POST['Price']));

        // Validate Address
        // Validate Address
if (empty($Address)) {
    $addressError = "Error: Address cannot be empty.";
} elseif (preg_match('/[0-9]/', $Address)) {
    $addressError = "Error: Address cannot contain numbers.";
}

// Validate Price
if (empty($Price)) {
    $priceError = "Error: Price cannot be empty.";
} elseif (!is_numeric($Price) || $Price <= 0) {
    $priceError = "Error: Price must be a positive number.";
} elseif (preg_match('/[a-zA-Z]/', $Price)) {
    $priceError = "Error: Price cannot contain letters.";
} else {
    try {
        // Create order with the logged-in client's ID
        Admin::CreateOrder($clientId, $Address, null, $Price);
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
        #status-update-container {
            margin-left: 0%;
            width: 100%; /* Set to full width */
            max-width: 78em; /* Restrict maximum width if needed */
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
        .pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap; /* Allows pagination items to wrap within the container */
        }

        .page-item {
            margin: 0 5px; /* Adds some spacing between items */
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
                <a style="font-size: 20px;" href="client_dashboard.php" class="nav-link">
                    <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a style="font-size: 20px;" href="client_trackorder.php" class="nav-link">
                    <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                    Track Order
                </a>
            </li>
        </ul>
        <hr>
        <footer style="margin-left:1.5%">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
    </div>

    <div class="b-example-divider"></div>

    <div id="status-update-container" class="border rounded p-4">
        <form id="status-update" method="post" action="client_addorder.php">
            <div>
                <h3 style="font-size: 25px">Add Order</h3>
                <hr>
            </div>
            <div class="mb-3">
                <input type="text" name="Address" class="form-control" placeholder="Address" required>
            </div>
            <div class="mb-3">
                <input type="text" name="Price" class="form-control" placeholder="Order Price" required>
                <?php if ($priceError): ?>
                    <p style='color: red;'><?php echo $priceError; ?></p>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

        <br>
        <div>
                <h4 style="font-size: 20px">Recent Orders:</h4>
            </div>
        <div class="custom-card">
            <table class="table table-secondary table-bordered custom-table">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Address</th>
                        <th scope="col">Price</th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $entry): ?>
                            <tr>
                                <td><?php echo $entry['OrderId']; ?></td>
                                <td><?php echo $entry['Address']; ?></td>
                                <td><?php echo $entry['Price']; ?></td>
                                <td><?php echo $entry['OrderDate']; ?></td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="OrderId" value="<?php echo $entry['OrderId']; ?>">
                                        <button style="width: 100%;" type="submit" name="delete" class="btn btn-secondary">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
