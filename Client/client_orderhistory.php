<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Database/Database.php'; 
use DELIVERY\Database\Database;

// Initialize database connection
$db = new Database();
$pdo = $db->getStarted();

// Function to fetch client orders
function fetchClientOrders($clientId) {
    global $pdo;
    // Fetch orders where the client ID matches and filter by delivered status
    $stmt = $pdo->prepare("
        SELECT o.OrderId, u.Name, u.Email, o.Address, o.OrderDate, o.Status, o.StatusUpdateAt
        FROM orders o 
        JOIN user u ON o.DriverId = u.ID 
        WHERE o.ClientId = :clientId
    ");
    $stmt->bindParam(':clientId', $clientId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // Fetch client orders for the logged-in user
    $orders = fetchClientOrders($userId);

    // Initialize variable to hold availability status
    $availabilityStatus = null;

    // Fetch user's availability status
    $stmt = $pdo->prepare("SELECT AvailabilityStatus FROM user WHERE ID = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $availabilityStatus = $result['AvailabilityStatus']; // Store the availability status
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Determine new status based on checkbox
        $newStatus = (isset($_POST['toggle']) && $_POST['toggle'] === 'on') ? 'on' : 'off';
    
        // Update the database
        $stmt = $pdo->prepare("UPDATE user SET AvailabilityStatus = :status WHERE ID = :userId");
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
    
        // Re-fetch the availability status after update
        $stmt = $pdo->prepare("SELECT AvailabilityStatus FROM user WHERE ID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            $availabilityStatus = $result['AvailabilityStatus']; // Update availability status
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
} else {
    echo "User is not logged in.";
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
    <title>Client Order History</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Client Dashboard</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 200px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span style="margin-left: 35px;" class="fs-4">RINDRA</span>
        </a>
        <span class="fs-4">FAST DELIVERY</span>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="client_dashboard.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>

                    Dashboard           
                </a>
            </li>
            <li>
                <a href="client_createorder.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>

                    Create Order    
                </a>
            </li>
        </ul>
        <hr>
        <footer>&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
    </div>

    <div class="b-example-divider"></div>

    <div id="status-update-container" class="border rounded p-4">
        <div>
            <h3 style="font-size: 30px">Your Orders</h3>
            <hr>
        </div>

        <div class="custom-card">
            <table class="table table-secondary table-striped-columns table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Name</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Address</th>
                        <th scope="col">Contact</th>
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
                                <td><?php echo htmlspecialchars($entry['Email'] ?? 'N/A'); ?></td>
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
    </div>
</div>
</main>
</body>
</html>
