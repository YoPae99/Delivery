<?php
session_start();
require_once __DIR__ . '/../Classes/Admin.php';  // Include the Admin class
use DELIVERY\Admin\Admin;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['OrderID'])) {
        if (!isset($_SESSION['record']) || !is_array($_SESSION['record'])) {
            $_SESSION['record'] = [];
        }
        
        // Ensure Status exists before using it
        $status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : 'Unknown';
        $orderId = htmlspecialchars($_POST['OrderID']);
        date_default_timezone_set('Asia/Bangkok');
        
        // Check if the order is marked as 'Delivered'
        // if ($status == 'Delivered') {
        //     echo 'Order ' . $orderId . ' is delivered!';
        // } else {

            // Add the order details to the session
            $_SESSION['record'][] = [
                'OrderId' => $orderId,
                'ClientId' => 'Unknown',  // Default value, will be updated after the function call
                'Status' => $status,
                'Date' => date('Y-m-d H:i:s')
            ];
        // }

        // Update the order status in the database using the Admin class
        try {
            $admin = new Admin();  // Create an instance of the Admin class
            
            // Call the UpdateOrderStatus function and pass OrderID and Status
            $clientId = $admin->UpdateOrderStatus($orderId, $status);
            
            // Update the session with the retrieved ClientId
            if ($clientId) {
                $_SESSION['record'][count($_SESSION['record']) - 1]['ClientId'] = $clientId; // Update the last entry
            }
            
        } catch (Exception $e) {
            echo "Failed to update order status: " . $e->getMessage();
        }
    }

    // Handle delete button functionality
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $indexToDelete = intval($_POST['index']);
        if (isset($_SESSION['record'][$indexToDelete])) {
            unset($_SESSION['record'][$indexToDelete]);
            $_SESSION['record'] = array_values($_SESSION['record']);
        }
    }

    // Redirect to avoid resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
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
    </style>
    <title>Update Order</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
  <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 200px;">
  <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span style="margin-left: 35px;" class="fs-4">RINDRA</span>
        </a>
        <span class="fs-4" >FAST DELIVERY</span>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li>
        <a href="driver_dashboard.php" class="nav-link">
        <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="driver_updateorder.php" class="nav-link">
        <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Update Order
        </a>
      </li>
      <li class="nav-item">
        <a href="/logout.php" class="nav-link">
        <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Sign out
        </a>
      </li>
    </ul>
    <hr>
            <footer>&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>

  </div>
  <div class="b-example-divider"></div>
  <div id="status-update-container" class="border rounded p-4">
        <form id="status-update" method="post">
        <div>
                <h3 style="font-size: 30px">Update Order</h3>
                <hr>
            </div>
            <div class="mb-3">
                <input type="text" name="OrderID" class="form-control" id="order_id" placeholder="Enter Order ID" required>
            </div>
            <div>
                <select name="status" class="form-select" required>
                    <option selected>Select Order Status</option>
                    <option value="Order Received">Order Received</option>
                    <option value="Order Ready">Order Ready</option>
                    <option value="Picked up">Picked up</option>
                    <option value="Pending">Pending</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Canceled">Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary mt-2">Submit</button>
            </div>  
        </form>
        <br><hr>
        <div class="custom-card">
            <table class="table table-secondary table-bordered custom-table">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Client ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Update Status At</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($_SESSION['record']) && !empty($_SESSION['record'])): ?>
                        <?php foreach ($_SESSION['record'] as $entry): ?>
                        <tr>
                            <th scope="row"><?php echo $entry['OrderId']; ?></th>
                            <td><?php echo $entry['ClientId']; ?></td>
                            <td><?php echo $entry['Status']; ?></td>
                            <td><?php echo $entry['Date']; ?></td>
                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button style="width: 70%;" type="submit" name="delete" class="btn btn-danger">Clear</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
  </main>
</body>
</html>