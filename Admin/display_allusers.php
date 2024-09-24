<?php
session_start();

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Function to fetch users from the database
function fetchUsersFromDatabase($Permission = null) {
    $db = new Database();
    $conn = $db->getStarted();
    $users = [];

    if ($conn) {
        $baseQuery = "SELECT ID, Name, Age, Username, Email, CreatedTime, Permission FROM user";
        
        if ($Permission && $Permission !== 'All') {
            // Filter based on the permission role
            $query = $baseQuery . " WHERE Permission = :Permission";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':Permission', $Permission);
        } else {
            // Fetch all users if no filter is applied or 'All' is selected
            $query = $baseQuery;
            $stmt = $conn->prepare($query);
        }

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $users;
}

// Initialize users variable
$selectedPermission = $_POST['Select'] ?? 'All';
$users = fetchUsersFromDatabase($selectedPermission);

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
            height: 600px;
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
    <title>Overview</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 200px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">RINDRA FAST DELIVERY</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li><a href="admin_dashboard.php" class="nav-link">Dashboard</a></li>
            <li><a href="admin_trackorder.php" class="nav-link">Track Orders</a></li>
            <li class="nav-item"><a href="../login.php" class="nav-link">Sign out</a></li>
        </ul>
        <hr>
        <footer>&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
    </div>
    <div class="b-example-divider"></div>
    <div id="status-update-container" class="border rounded p-4">
        <div>
            <h3 style="font-size: 30px">User List</h3>
            <hr>
        </div>

        <div class="d-flex align-items-center">
    <form method="post" action="" class="d-flex">
        <select class="form-select form-select-sm me-2 custom-width-select" name="Select" aria-label="Small select example">
            <option value="All" selected>All</option>
            <option value="Client">Client</option>
            <option value="Driver">Driver</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm custom-width-button">Filter</button>
    </form>
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
                                    <form action="" method="post">
                                        <input type="hidden" name="UserId" value="<?php echo $entry['ID']; ?>">
                                        <button style="width: 100%;" type="submit" name="delete" class="btn btn-danger">Delete</button>
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
    </div>
</main>
</body>
</html>
