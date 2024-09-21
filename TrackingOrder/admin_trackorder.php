<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['OrderId'])) {
        if (!isset($_SESSION['record']) || !is_array($_SESSION['record'])) {
            $_SESSION['record'] = [];
        }

        $_SESSION['record'][] = [
            'OrderId' => htmlspecialchars($_POST['id']),
            'ClientId' => htmlspecialchars($_POST['ClientId']),
            'Date' => date('Y-m-d H:i:s')
        ];
    }

    // Delete button
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $indexToDelete = intval($_POST['index']);
        if (isset($_SESSION['record'][$indexToDelete])) {
            unset($_SESSION['record'][$indexToDelete]);
            $_SESSION['record'] = array_values($_SESSION['record']);
        }
    }

    // Set range value based on the selected status
    $selectedStatus = $_POST['status'] ?? 'Select Order Status';
    $rangeValue = 0; // Default value

    switch ($selectedStatus) {
        case 'OrderReceived':
            $rangeValue = 0;
            break;
        case 'OrderReady':
            $rangeValue = 1;
            break;
        case 'PickedUp':
            $rangeValue = 2;
            break;
        case 'Pending':
            $rangeValue = 3;
            break;
        case 'Delivered':
            $rangeValue = 4;
            break;
        case 'Canceled':
            $rangeValue = 4;
            break;
    }
    
}
// elseif ($selectedStatus == 'Canceled'){
        
// }
 else {
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
                <input type="text" name="id" class="form-control" id="order_id" placeholder="Enter Order ID" aria-describedby="emailHelp">
            </div>
            <div>
                <select name="status" class="form-select" aria-label="Status Select">
                    <option selected>Select Order Status</option>
                    <option value="Order Received" <?php if ($selectedStatus == 'OrderReceived') echo 'selected'; ?>>Order Received</option>
                    <option value="Order Ready" <?php if ($selectedStatus == 'OrderReady') echo 'selected'; ?>>Order Ready</option>
                    <option value="Picked up" <?php if ($selectedStatus == 'PickedUp') echo 'selected'; ?>>Picked up</option>
                    <option value="Pending" <?php if ($selectedStatus == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Delivered" <?php if ($selectedStatus == 'Delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="Delivered" <?php if ($selectedStatus == 'Canceled') echo 'selected'; ?>>Canceled</option>

                </select>
                <button type="submit" class="btn btn-primary mt-2">Submit</button>
            </div>  
        </form>
        <br>
        <hr>

        <br>
        <hr>
        <div class="custom-card">
            <table class="table table-striped table-bordered custom-table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Date and Time</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($_SESSION['record']) && !empty($_SESSION['record'])): ?>
                        <?php foreach ($_SESSION['record'] as $index => $entry): ?>
                        <tr>
                            <th scope="row"><?php echo $entry['id']; ?></th>
                            <td><?php echo $entry['clientId']; ?></td>
                            <td><?php echo $entry['date']; ?></td>
                            <!-- <td>
                                <form action="" method="post">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button style="width: 40%;" type="submit" name="delete" class="btn btn-danger">Delete</button>
                                </form>
                            </td> -->
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
