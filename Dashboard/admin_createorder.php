<?php
session_start();

// Include necessary files
//degubbing
// if (file_exists(__DIR__ . '/Classes/Admin.php')) {
//     require_once __DIR__ . '/../Classes/Admin.php';
// } else {
//     echo "File not found!";
// }

require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ClientId']) && isset($_POST['Address'])) {
        $ClientId = htmlspecialchars($_POST['ClientId']);
        $Address = htmlspecialchars($_POST['Address']);
        
        // Store in session
        if (!isset($_SESSION['record']) || !is_array($_SESSION['record'])) {
            $_SESSION['record'] = [];
        }
        $_SESSION['record'][] = [
            'ClientId' => $ClientId,
            'Address' => $Address,
            'date' => date('Y-m-d H:i:s')
        ];

        // Attempt to insert into the database
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $ClientId = $_POST['ClientId'];
    $Address = $_POST['Address'];


    try {
        Admin::CreateOrder($ClientId, $Address);
        


    } catch (PDOException $e) {
        // Check for duplicate entry error
        if ($e->getCode() == 23000) { 
            echo "<p style='color: red;'>Error: Client ID invalid. Please choose another.</p>";
        } else {
            echo "<p style='color: red;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "Invalid request.";
}
    }

    // Delete button
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $indexToDelete = intval($_POST['index']);
    
        // Check if ClientId is set
        if (isset($_POST['ClientId'])) {
            $clientIdToDelete = $_POST['ClientId']; // Get the ClientId
    
            if (isset($_SESSION['record'][$indexToDelete])) {
                // Remove from session
                unset($_SESSION['record'][$indexToDelete]);
                $_SESSION['record'] = array_values($_SESSION['record']);
                
                // Delete from the database
                $db = new \DELIVERY\Database\Database();
                $conn = $db->getStarted();
    
                if ($conn) {
                    $stmt = $conn->prepare("DELETE FROM orders WHERE ClientId = :clientId");
                    $stmt->bindParam(':clientId', $clientIdToDelete);
                    $stmt->execute();
                }
            }
        } else {
            echo "ClientId not set in POST data."; // Debugging message
        }
    }
    
    

    // Set range value based on the selected status
    $selectedStatus = $_POST['status'] ?? 'Select Order Status';
    $rangeValue = 0; // Default value

    switch ($selectedStatus) {
        case 'Order Received':
            $rangeValue = 0;
            break;
        case 'Order Ready':
            $rangeValue = 1;
            break;
        case 'Picked up':
            $rangeValue = 2;
            break;
        case 'Pending':
            $rangeValue = 3;
            break;
        case 'Delivered':
            $rangeValue = 4;
            break;
    }
} else {
    $rangeValue = 0; // Default value when no form is submitted
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      /* Your existing styles */
      #status-update-container {
          margin-left: 1%;
          margin-top: 5%;
          width: 70em;
          background-color: #f8f9fa;
      }

      .border {
          border: 1px solid #ced4da;
      }

      .rounded {
          border-radius: 0.25rem;
      }

      .p-4 {
          padding: 1.5rem;
      }

      #order-status {
          margin-left: 1%;
          margin-right: 1%;
          margin-top: 20%;
          width: 65em;
          position: relative;
      }

      .form-range {
          width: 100% !important;
      }

      .status-labels {
          display: flex;
          position: absolute;
          top: -20px;
          width: 100%;
      }

      .status-labels h3 {
          font-size: 18px;
 
          text-align: center;
          color: black;
          flex: 1;
      }

      .status-labels h3:nth-child(1) {
  left: -5.5%; /* Align first label to the start */
}

.status-labels h3:nth-child(2) {
  left: -6%; /* Align second label to the first stop */
}

.status-labels h3:nth-child(3) {
  left: -2%; /* Align third label to the second stop */
}

.status-labels h3:nth-child(4) {
  left: 2%; /* Align fourth label to the third stop */
}

.status-labels h3:nth-child(5) {
  left: 10%; /* Align fifth label to the end */
  margin-left: -40px; /* Adjust margin to center the last label */
}
    </style>
    <title>Dashboard</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Sidebars examples</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>
            <span class="fs-4">RINDRA FAST DELIVERY</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
        <li>
        <a href="/Dashboard/admin_dashboard.php" class="nav-link" role="button" data-bs-toggle="button" aria-pressed="true">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="/TrackingOrder/admin_trackorder.php" class="nav-link">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"/></svg>
          Track Orders
        </a>
      </li>
      <!-- <li>
        <a href="#"  class="nav-link" role="button" data-bs-toggle="button" aria-pressed="true">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
          Edit User
        </a>
      </li>  -->
      <li class="nav-item">
        <a href="../login.php"  class="nav-link" role="button" data-bs-toggle="button" aria-pressed="true">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
          Sign out
        </a>
      </li>
        </ul>
        <hr>
    </div>

    <div class="b-example-divider"></div>

    <div id="status-update-container" class="border rounded p-4">
    <form id="status-update" method="post" action="admin_createorder.php">
        <div>
            <h3 style="font-size: 30px">Add Order</h3>
            <hr>
        </div>
        <div class="mb-3">
            <input type="text" name="ClientId" class="form-control" placeholder="Client ID" required>
        </div>
        <div class="mb-3">
            <input type="text" name="Address" class="form-control" placeholder="Address" required>
        </div>
        <div>
            <button type="submit" class="btn btn-primary mt-2">Submit</button>
        </div>  
    </form>
    <br>
    <hr>
    <div class="custom-card">
        <table class="table table-striped table-bordered custom-table">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Client ID</th>
                    <th scope="col">Address</th>
                    <th scope="col">Date and Time</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
    <?php if (isset($_SESSION['record']) && !empty($_SESSION['record'])): ?>
        <?php foreach ($_SESSION['record'] as $index => $entry): ?>
        <tr>
            <th scope="row"><?php echo $entry['ClientId']; ?></th>
            <td><?php echo $entry['Address']; ?></td>
            <td><?php echo $entry['date']; ?></td>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                    <input type="hidden" name="ClientId" value="<?php echo $entry['ClientId']; ?>"> <!-- Ensure this is present -->
                    <button style="width: 40%;" type="submit" name="delete" class="btn btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>

        </table>
    </div>
</div>
</div>
</main>
</body>
</html>
