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
                echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
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

// Function to fetch delivered client orders that are delivered with pagination
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
require_once __DIR__ . '/../Database/Database.php'; 
use DELIVERY\Database\Database;

// Initialize database connection
$db = new Database();
$pdo = $db->getStarted();

// Function to fetch delivered client orders with pagination
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

// Function to fetch total delivered orders count for pagination
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
if (!isset($_SESSION['ID'])) {
    echo "User is not logged in.";
    exit;
}

$userId = $_SESSION['ID'];

// Pagination setup for delivered orders
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 7; // Records per page
$offset = ($page - 1) * $limit; // Calculate offset for SQL query

// Fetch delivered client orders for the logged-in user with pagination
$deliveredOrders = fetchDeliveredClientOrdersWithPagination($userId, $limit, $offset);

// Fetch total number of delivered orders for pagination
$totalOrders = fetchTotalDeliveredOrdersCount($userId);
$totalPages = ceil($totalOrders / $limit);

// Check if the session ID is set
if (isset($_SESSION['ID'])) {
    $userId = $_SESSION['ID'];

    // Fetch the user's username
    $db = new \DELIVERY\Database\Database();
    $conn = $db->getStarted();
    $stmt = $conn->prepare("SELECT Name FROM user WHERE ID = :userId");
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
?>
