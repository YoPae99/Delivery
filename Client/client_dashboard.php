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

      #order-status {
  margin-left: 5%;
  margin-top: 20%;
  width: 65em;
  position: relative; /* Allow absolute positioning of labels */
}

.form-range {
  width: 100% !important; /* Ensure the range bar spans the container */
}

.status-labels {
  display: flex;
  position: absolute; /* Position labels absolutely within the container */
  top: -20px; /* Adjust this based on your layout */
  width: 100%; /* Make sure it spans the full width */
}

.status-labels h3 {
  font-size: 18px;
  margin: 0; /* Remove margin */
  padding: 0; /* Remove padding */
  text-align: center;
  color: black;
  position: relative; /* Enable positioning */
  flex: 1; /* Allow each label to take equal space */
}

.status-labels h3:nth-child(1) {
  left: -9%; /* Align first label to the start */
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
      <li>
        <a href="#" class="nav-link text-white">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"/></svg>
          Track Orders
        </a>
      </li>
      <!-- <li>
        <a href="#" class="nav-link text-white">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
          Products
        </a>
      </li>
      <li>
        <a href="#" class="nav-link text-white">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
          Customers
        </a>
      </li> -->
      <li class="nav-item">
        <a href="../login.php" class="nav-link text-white" aria-current="page">
          <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
          Sign out
        </a>
      </li>
    </ul>
    <hr>
    <!-- <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
        <strong>mdo</strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
        <li><a class="dropdown-item" href="#">New project...</a></li>
        <li><a class="dropdown-item" href="#">Settings</a></li>
        <li><a class="dropdown-item" href="#">Profile</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Sign out</a></li>
      </ul>
    </div> -->
  </div>

  <div class="b-example-divider"></div>

  <div id="order-status">
  <div class="status-labels">
    <h3>Order Received</h3>
    <h3>Order Ready</h3>
    <h3>Assigned Driver</h3>
    <h3>Out for Delivery</h3>
    <h3>Delivered</h3>
  </div>
  <input type="range" class="form-range" min="0" max="4" step="1" id="customRange3">
  
</div>


  </main>
  <!-- <script src="js/bootstrap.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script src="js/sidebar.js"></script> -->

</body>
</html>
