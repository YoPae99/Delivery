<?php
session_start();

require_once __DIR__ . '/../Classes/Driver.php';  // Include the Driver class
use DELIVERY\Driver\Driver;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['OrderID'])) {
        if (!isset($_SESSION['record']) || !is_array($_SESSION['record'])) {
            $_SESSION['record'] = [];
        }

        // Ensure Status exists before using it
        $status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : 'Unknown';

        $_SESSION['record'][] = [
            'OrderId' => htmlspecialchars($_POST['OrderID']),
            'ClientId' => 'Unknown',  // Default value, will be updated after the function call
            'Status' => $status,
            'Date' => date('Y-m-d H:i:s')
        ];

        // Update the order status in the database using the Driver class
        try {
            $driver = new Driver();  // Create an instance of the Driver class
            $orderId = htmlspecialchars($_POST['OrderID']);
            
            // Call the UpdateOrderStatus function and pass OrderID and Status
            $clientId = $driver->UpdateOrderStatus($orderId, $status);
            
            // Update the session with the retrieved ClientId
            if ($clientId) {
                $_SESSION['record'][count($_SESSION['record']) - 1]['ClientId'] = $clientId; // Update the last entry
            }

        } catch (Exception $e) {
            echo "Failed to update order status: " . $e->getMessage();
        }
    }

    //delete button
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $indexToDelete = intval($_POST['index']);
        if (isset($_SESSION['record'][$indexToDelete])) {
            unset($_SESSION['record'][$indexToDelete]);
            $_SESSION['record'] = array_values($_SESSION['record']);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      /* .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      } */
      #status-update-container {
            margin-left: 1%;
            margin-top: 5%;
            width: 70em;
            background-color: #f8f9fa;
        }
    </style>
    <title>Update Order</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>

  <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
      <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>
      <span class="fs-4">RINDRA FAST DELIVERY</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="homepage.php" class="nav-link active" aria-current="page">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
          Home
        </a>
      </li>
      <li>
        <a href="#" class="nav-link text-white">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="#" class="nav-link text-white">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"/></svg>
          Track Orders
        </a>
      </li>
      <li class="nav-item">
        <a href="../login.php" class="nav-link text-white" aria-current="page">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
          Sign out
        </a>
      </li>
    </ul>
    <hr>
  </div>

  <div class="b-example-divider"></div>


  <div id="status-update-container" class="border rounded p-4">
        <form id="status-update" method="post">
            <div class="mb-3">
                <label for="order_id" class="form-label">Update Order Status</label>
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
            <table class="table table-striped table-bordered custom-table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Client ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Date and Time</th>
                        <th scope="col">Date and Time</th>
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
                                    <button style="width: 40%;" type="submit" name="delete" class="btn btn-danger">Clear</button>
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
