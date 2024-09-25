<?php
session_start();
require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;

$status = null;
$hideThumbClass = 'hide-thumb';  // Default to hide the thumb
$rangeValue = 0;  // Default range value

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['OrderId'])) {
        $admin = new Admin();
        $status = $admin->TrackOrder(htmlspecialchars($_POST['OrderId']));

        if ($status) {
            // Set range value based on the status from the database
            switch ($status) {
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
                case 'Canceled':
                    $rangeValue = 4;
                    break;
                default:
                    $rangeValue = 0;
            }
            // Remove the hide-thumb class because an order was found
            $hideThumbClass = '';  // Empty to show the thumb
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
        /* Default style for the thumb */
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    height: 20px;
    width: 20px;
    background: #007bff;
    border-radius: 50%;
    cursor: pointer;
}

/* Hide the thumb if no order is found */
.hide-thumb::-webkit-slider-thumb {
    opacity: 0;
}

input[type="range"]::-moz-range-thumb {
    height: 20px;
    width: 20px;
    background: #007bff;
    border-radius: 50%;
    cursor: pointer;
}

.hide-thumb::-moz-range-thumb {
    opacity: 0;
}

        #status-update-container {
            margin-left: 0%;
            width: 75.5em;
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
          margin-top: 10%;
          width: 71em;
          position: relative;
      }

      .form-range {
          width: 100% !important;
          margin-right: 1%;
      }

      .status-labels {
          display: flex;
          position: absolute;
          top: -20px;
          width: 100%;
      }

      .status-labels h3 {
          font-size: 17px;
          color: black;
          
      }
      footer {
            position: absolute;
            bottom: 10px;
            font-size: 14px;
            color: #aaa;
        }

      .status-labels h3:nth-child(1) {
  margin-left: 0%; /* Align first label to the start */
}

.status-labels h3:nth-child(2) {
  margin-left: 10.7%; /* Align second label to the first stop */
}

.status-labels h3:nth-child(3) {
  margin-left: 17%; /* Align third label to the second stop */
}

.status-labels h3:nth-child(4) {
  margin-left: 18.4%; /* Align fourth label to the third stop */
}

.status-labels h3:nth-child(5) {

  margin-left: 16.4%; /* Adjust margin to center the last label */
}
        .custom-width-input {
            width: 250px; /* Set the desired width */
        }
        .custom-width-button {
            width: 100px; /* Set the desired width */
        }
    </style>
    <title>Track Order</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
    <h1 class="visually-hidden">Sidebars examples</h1>

    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span style="margin-left: 51px;" class="fs-4">RINDRA</span>
        </a>
        <span style="margin-left: 20px;" class="fs-4" >FAST DELIVERY</span>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a style="font-size: 20px;" href="admin_dashboard.php" class="nav-link">
                <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a style="font-size: 20px;" href="admin_trackorder.php" class="nav-link">
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
    <div>
                <h3 style="font-size: 30px">Order Tracking</h3>
                <hr>
            </div>

    <div class="d-flex align-items-center">
        <form class="d-flex" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    
        <input type="text" name="OrderId" class="form-control me-2 custom-width-input" placeholder="Input Order ID" required>
        <button type="submit" class="d-flex btn btn-primary custom-width-button">Search</button>
        </form>
    </div>

    
    
    <div id="order-status">
  <div class="status-labels">
    <h3>Order Received</h3>
    <h3>Order Ready</h3>
    <h3>Picked up</h3>
    <h3>Pending</h3>
    <h3>Delivered</h3>
  </div>
  <input type="range" class="form-range <?php echo $hideThumbClass; ?>" min="0" max="4" step="1" id="customRange3" value="<?php echo $rangeValue; ?>">
  
</div>
    </div>
    
</main>
</body>
</html>
