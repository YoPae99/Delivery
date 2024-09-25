<?php
session_start();
require_once __DIR__ . '/../Database/Database.php';  // Include the Database class
use DELIVERY\Database\Database;

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo "User is not logged in.";
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

// Proceed if user is logged in
$db = new Database();
$conn = $db->getStarted(); // Assuming this method returns the DB connection

// Fetch username from session
$username = htmlspecialchars($_SESSION['user']['Name']);

try {
    // SQL query to count users
    $query1 = "SELECT COUNT(*) AS user_count FROM user"; // Replace 'user' with your actual table name
    $stmt1 = $conn->prepare($query1);
    $stmt1->execute();
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    $user_count = $result1['user_count'];

    // Count orders
    $query2 = "SELECT COUNT(*) AS order_count FROM orders";
    $stmt2 = $conn->prepare($query2);
    $stmt2->execute();
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    $order_count = $result2['order_count'];

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
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
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

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
            background-color: #0056b3;
            color: white;
        }

        .content-container {
            margin-top: 10px;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            #overview-section, 
            #quick-access-section {
                padding: 20px;
            }

            .btn-custom {
                height: 80px;
                width: 160px;
            }

            footer {
                position: relative;
                margin-top: 20px;
            }
        }

        @media (max-width: 576px) {
            .btn-custom {
                height: 70px;
                width: 140px;
                font-size: 14px;
            }

            h4 {
                font-size: 1.5rem;
            }
        }
        
    </style>
    <title>Admin Dashboard</title>
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
<main>
  <h1 class="visually-hidden">Sidebars examples</h1>

  <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px;">
  <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Welcome</span>
        </a>
        <span class="fs-4">Mr. <?php echo htmlspecialchars($_SESSION['user']['Username']); ?></span>
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
          Track Orders
        </a>
      </li>

      <li class="nav-item">
        <a style="font-size: 20px;" href="/logout.php"  class="nav-link">
        <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
          Sign out
        </a>
      </li>
    </ul>
    <hr>
    <footer style="margin-left:1.5%">&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
  </div>

  <div class="b-example-divider"></div>

  <!-- Main content -->
  <div class="container content-container">
      <div class="row">
          <!-- Overview Section -->
          <div id="overview-section" class="col-12 text-center">
          <h4 style="margin-top: 20px; padding: 15px; background-color: #505963; color: white; font-size: 2rem; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
            Admin Overview
              </h4>
              <div class="row justify-content-center mt-4">

                  <div class="col-auto">
                      <a href="display_allusers.php" class="btn btn-custom btn-secondary">
                      <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                          <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                      </svg>
                      <div class="fs-4 mb-3">Total Users: <?php echo $user_count; ?></div>
                      </a>
                  </div>

                  <div class="col-auto">
                      <a href="display_allorders.php" class="btn btn-custom btn-secondary">
                      <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                          <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                          <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                      </svg>
                          <div class="fs-4 mb-3">Total Orders: <?php echo $order_count; ?></div>
                      </a>
                  </div>
              </div>
          </div>

          <!-- Quick Access Section -->
          <div id="quick-access-section" class="col-12 text-center">
              <h4 style="margin-top: 20px; padding: 15px; color: #007bff; font-size: 2rem; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                  Quick Access
              </h4>
              <div class="row justify-content-center mt-4">
                  <div class="col-auto">
                      <a href="admin_createorder.php" class="btn btn-outline-primary" style="height: 90px; width: 180px;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
  <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
  <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
</svg>    
                      <div class="fs-4 mb-3">Create Order</div>
                      </a>
                  </div>
                  <div class="col-auto">
                      <a href="admin_assignorder.php" class="btn btn-outline-primary" style="height: 90px; width: 180px;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
  <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
</svg>
                      <div class="fs-4 mb-3">Assign Orders</div>
                      </a>
                  </div>
                  <div class="col-auto">
                      <a href="admin_updateorder.php" class="btn btn-outline-primary" style="height: 90px; width: 182px;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-journal-arrow-up" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 11a.5.5 0 0 0 .5-.5V6.707l1.146 1.147a.5.5 0 0 0 .708-.708l-2-2a.5.5 0 0 0-.708 0l-2 2a.5.5 0 1 0 .708.708L7.5 6.707V10.5a.5.5 0 0 0 .5.5"/>
  <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
  <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
</svg>    
                      <div class="fs-4 mb-3">Update Orders</div>
                      </a>
                  </div>
              </div>
          </div>
      </div>
  </div>

</main>
</body>
</html>
