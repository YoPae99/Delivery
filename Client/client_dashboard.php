<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Database/Database.php'; 
use DELIVERY\Database\Database;

// Initialize database connection
$db = new Database();
$pdo = $db->getStarted();

// Function to fetch available drivers
function fetchAvailableDrivers() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ID AS DriverID, Name AS DriverName FROM user WHERE Permission = 'driver' AND AvailabilityStatus = 'on'");
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

// Fetch available drivers for display
$availableDrivers = fetchAvailableDrivers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .border {
            border: 1px solid #ced4da;
        }

        .rounded {
            border-radius: 0.25rem;
        }

        .custom-card {
            margin-top: 20px;
            height: 300px; /* Fixed height */
            min-height: 300px; /* Ensure there’s enough space for content */
            overflow-y: auto; /* Enable vertical scrolling */
        }

        footer {
            position: absolute;
            bottom: 10px;
            font-size: 14px;
            color: #aaa;
        }

        /* Centering sections and improving layout */
        #overview-section,
        #quick-access-section {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Updated background colors */
        #overview-section {
            background-color: #505963;
            color: white;
        }

        .btn-custom {
            height: 90px;
            width: 180px;
            transition: background-color 0.3s, color 0.3s; /* Smooth transitions for hover effects */
        }

        .btn-custom:hover {
            background-color: #0056b3; /* Darker blue for hover effect */
            color: white; /* White text on hover */
        }

        .content-container {
            margin-top: 20px;
            min-height: 100vh;
            height: 100px;
        }

        .custom-switch {
            position: relative;
            width: 60px; /* Adjust width */
            height: 30px; /* Adjust height */
            background-color: #ccc;
            border-radius: 30px; /* Make it round */
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* Hide the default checkbox */
        .custom-switch input {
            display: none;
        }

        /* Toggle switch circle */
        .slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px; /* Adjust size of circle */
            height: 24px;
            background-color: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        /* When checked, move the circle to the right */
        .custom-switch input:checked + .slider {
            transform: translateX(30px); /* Move the circle */
        }

        /* Change background color when checked */
        .custom-switch input:checked + .slider::before {
            background-color: #0d6efd; /* Custom checked color */
        }

        /* Hover effect */
        .custom-switch:hover {
            background-color: #b3b3b3;
        }
        .form-check-input {
    width: 2em;  /* Adjust width of the checkbox */
    height: 1em;  /* Adjust height of the checkbox */
}

.btn-custom {
    padding: 0.5em 1em;  /* Adjust padding for the button */
    font-size: 1em;  /* Increase font size */
}

    </style>
    <title>Driver Dashboard</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Driver Dashboard</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 200px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Welcome</span>
    </a>
    <span class="fs-4">Mr. <?php echo htmlspecialchars($username); ?></span>

    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li>
            <a href="client_dashboard.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                Dashboard
            </a>
        </li>
        <li>
            <a href="client_trackorder.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                Track Order
            </a>
        </li>
        <li>
            <a href="/logout.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
                Sign out
            </a>
        </li>
    </ul>
    
    <footer style="margin-bottom:40px"><div class="card-body">
                
            </div></footer>
    <hr>
    <footer class="mt-auto text-center">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
</div>


    <div class="b-example-divider"></div>

    <div class="container content-container">
        <div id="overview-section" class="col-12 text-center">
             
            <h4 style="margin-top: 20px; padding: 15px; background-color: #505963; color: white; font-size: 2rem; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
            Client Overview   <!-- Greeting added here -->
            </h4>
            
            <div class="row justify-content-center mt-4">
            <div class="col-auto">
                    <a style="height: 100%;" href="client_addorder.php" class="btn btn-custom btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
  <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
  <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
</svg>
                        <h3 style="font-size:18px">Add Orders</h3>
                    </a>
                </div>
                <div class="col-auto">
                    <a style="height: 100%;" href="client_orderhistory.php" class="btn btn-custom btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                            <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
                        </svg>
                        <h3 style="font-size:18px">Order History</h3> 
                    </a>
                </div>
               
            </div>
        </div>
    </div>
</main>
</body>
</html>
