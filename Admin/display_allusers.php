<?php
session_start();

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Function to fetch users from the database with pagination and optional search
function fetchUsersFromDatabase($filter = null, $clientName = null, $limit = 5, $offset = 0) {
    $db = new Database();
    $conn = $db->getStarted();
    $users = [];

    if ($conn) {
        $baseQuery = "SELECT ID, Name, Age, Username, Email, CreatedTime, Permission FROM user";
        $query = $baseQuery; // Start with the base query

        // Modify query based on filter
        if ($filter) {
            if ($filter === 'AvailableDrivers') {
                $query .= " WHERE Permission = 'driver' AND AvailabilityStatus = 'on'";
            } else {
                $query .= " WHERE Permission = :Permission"; // Use parameter for filter
            }
        }

        // Add condition for client name search
        if ($clientName) {
            if (strpos($query, 'WHERE') !== false) {
                $query .= " AND Name LIKE :clientName"; // If a WHERE clause already exists
            } else {
                $query .= " WHERE Name LIKE :clientName"; // If no WHERE clause exists
            }
        }

        // Add LIMIT and OFFSET for pagination
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);

        // Bind parameters for limit and offset
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        // Bind parameters for filter and client name if needed
        if ($filter && $filter !== 'AvailableDrivers') {
            $stmt->bindParam(':Permission', $filter);
        }
        if ($clientName) {
            $clientNameParam = '%' . $clientName . '%';
            $stmt->bindParam(':clientName', $clientNameParam);
        }

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $users;
}

// Function to fetch total number of users for pagination
function fetchTotalUsersCount($filter = null, $clientName = null) {
    $db = new Database();
    $conn = $db->getStarted();
    $totalUsers = 0;

    if ($conn) {
        $baseQuery = "SELECT COUNT(*) as total FROM user";
        $query = $baseQuery; // Start with the base query

        // Modify query based on filter
        if ($filter) {
            if ($filter === 'AvailableDrivers') {
                $query .= " WHERE Permission = 'driver' AND AvailabilityStatus = 'on'";
            } else {
                $query .= " WHERE Permission = :Permission"; // Use parameter for filter
            }
        }

        // Add condition for client name search
        if ($clientName) {
            if (strpos($query, 'WHERE') !== false) {
                $query .= " AND Name LIKE :clientName"; // If a WHERE clause already exists
            } else {
                $query .= " WHERE Name LIKE :clientName"; // If no WHERE clause exists
            }
        }

        $stmt = $conn->prepare($query);

        // Bind parameters for filter and client name if needed
        if ($filter && $filter !== 'AvailableDrivers') {
            $stmt->bindParam(':Permission', $filter);
        }
        if ($clientName) {
            $clientNameParam = '%' . $clientName . '%';
            $stmt->bindParam(':clientName', $clientNameParam);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalUsers = $result['total'];
    }

    return $totalUsers;
}

// Get current page from URL, default is page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 7; // Records per page
$offset = ($page - 1) * $limit; // Calculate offset for SQL query

// Fetch users with pagination and optional search/filter
$selectedFilter = $_POST['Select'] ?? null;
$clientName = isset($_POST['ClientName']) ? $_POST['ClientName'] : null;
$users = fetchUsersFromDatabase($selectedFilter, $clientName, $limit, $offset);

// Fetch total number of users for pagination
$totalUsers = fetchTotalUsersCount($selectedFilter, $clientName);
$totalPages = ceil($totalUsers / $limit);

// Check if the form is submitted to delete a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $userIdToDelete = $_POST['UserId'];

    // Delete the user from the database
    $db = new Database();
    $conn = $db->getStarted();
    if ($conn) {
        $stmt = $conn->prepare("DELETE FROM user WHERE ID = :userId");
        $stmt->bindParam(':userId', $userIdToDelete);
        $stmt->execute();
    }
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
    <title>User List</title>
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
            <li><a style="font-size: 20px;" href="admin_dashboard.php" class="nav-link">
            <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                Dashboard</a></li>

            <li><a style="font-size: 20px;" href="admin_trackorder.php" class="nav-link">
            <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                Track Orders</a></li>

        </ul>
        <hr>
        <footer style="margin-left:1.5%">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
        </div>
    <div class="b-example-divider"></div>
    <div id="status-update-container" class="border rounded p-4">
        <div>
            <h3 style="font-size: 30px">User List</h3>
            <hr>
        </div>
<div class="d-flex">
        <div class="d-flex align-items-center">
    <form method="post" action="" class="d-flex">
        <input  type="text" name="ClientName" class="form-control me-2" placeholder="Search by Name" required>
        <button style="width: 150px;" type="submit" class="btn btn-primary  custom-width-button">Search</button>
    </form>
</div>
        <div class="d-flex align-items-center" style="margin-left:40%">
            <form method="post" action="" class="d-flex">
                <select class="form-select form-select-sm me-2 custom-width-select" name="Select" aria-label="Small select example">
                    <option value="" selected>All</option>
                    <option value="Client">Client</option>
                    <option value="Driver">Driver</option>
                    <option value="Admin">Admin</option>
                    <option value="AvailableDrivers">Available Drivers</option>
                </select>
                <button style="width: 107px;" type="submit" class="btn btn-primary custom-width-button">Filter</button>
            </form>
        </div>
        </div>

        <div class="custom-card">
            <table class="table table-secondary table-bordered custom-table">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">User ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Age</th>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Created Time</th>
                        <th scope="col">Role</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $index => $entry): ?>
                            <tr>
                                <th scope="row"><?php echo $index + 1; ?></th>
                                <td><?php echo $entry['ID']; ?></td>
                                <td><?php echo $entry['Name']; ?></td>
                                <td><?php echo $entry['Age']; ?></td>
                                <td><?php echo $entry['Username']; ?></td>
                                <td><?php echo $entry['Email']; ?></td>
                                <td><?php echo $entry['CreatedTime']; ?></td>
                                <td><?php echo $entry['Permission']; ?></td>
                                <td>
    <!-- Modify Form -->
    <form action="modify_user.php" method="post" style="display: inline-block;">
        <input type="hidden" name="UserId" value="<?php echo $entry['ID']; ?>">
        <button type="submit" name="modify" class="btn btn-success">Modify</button>
    </form>

    <!-- Delete Form -->
    <form action="" method="post" style="display: inline-block;">
        <input type="hidden" name="UserId" value="<?php echo $entry['ID']; ?>">
        <button type="submit" name="delete" class="btn btn-danger">Delete</button>
    </form>
</td>

                                
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No users found.</td>
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
