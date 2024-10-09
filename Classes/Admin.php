<?php
namespace DELIVERY\Admin;
require_once __DIR__ . '../User.php'; 
require_once __DIR__ . '/../Database/Database.php'; // Correct path to Database.php
use DELIVERY\User\User;
use DELIVERY\Database\Database;
use PDO;
use PDOException;

class Admin extends User {

    public static function CreateOrder($ClientId, $Address, $DriverId, $Price) {
        $db = new \DELIVERY\Database\Database();
        $conn = $db->getStarted();
        if ($conn) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO orders (ClientId, Address, DriverId, price) 
                    VALUES (:clientId, :address, :driverId, :price)
                ");
                $stmt->bindParam(':clientId', $ClientId);
                $stmt->bindParam(':address', $Address);
                $stmt->bindParam(':driverId', $DriverId);
                $stmt->bindParam(':price', $Price); // Correct price binding
                $stmt->execute();
            } catch (PDOException $e) {
                echo "An error occurred: " . $e->getMessage(); // Log the error
            }
        }
    }
    
    public function AssignOrderToDriver($OrderId, $UserId) {
        // Implementation here
    }
    public function TrackOrder($OrderID) {
        $conn = new Database();
        try {
            // Check if the order exists
            $query = "SELECT * FROM orders WHERE OrderId = :OrderID";
            $statement = $conn->getStarted()->prepare($query);
            $statement->bindParam(':OrderID', $OrderID);
            $statement->execute();
            $order = $statement->fetch(PDO::FETCH_ASSOC);
    
            if ($order) {
                // Retrieve the Status from the database
                $status = $order['Status'];  // Adjust column name if necessary
                return $status;
            } else {
                header("Location: ../errordisplay.php");
                exit();
                
            }
        } catch (PDOException $e) {
            echo "Error retrieving order status: " . $e->getMessage();
            return null;
        }
    }
     
public static function UpdateOrderStatus($OrderID, $Status) {
    $conn = new Database();
    try {
        // Check if the order exists
        $query = "SELECT * FROM orders WHERE OrderId = :Orders";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(':Orders', $OrderID);
        $statement->execute();
        $order = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            // Retrieve the ClientId
            $ClientId = $order['ClientID'];  // Adjust column name if necessary
            
            // Update the order status
            $updateQuery = "UPDATE orders SET status = :status WHERE OrderId = :Orders";
            $updateStatement = $conn->getStarted()->prepare($updateQuery);
            $updateStatement->bindParam(':status', $Status);
            $updateStatement->bindParam(':Orders', $OrderID);
            $updateStatement->execute();
            
            // Return the ClientId for display
            return $ClientId;
        } else {
            echo "Order not found.";
            return null;
        }
    } catch (PDOException $e) {
        echo "Error updating order: " . $e->getMessage();
        return null;
    }
}

public function SearchOrderByClientName($Name) {
    $conn = new Database();
    try {
        // Search for the client's orders by joining the orders and users tables
        $query = "
            SELECT orders.* 
            FROM orders 
            JOIN users ON orders.ClientID = users.ID 
            WHERE users.Name = :Name";
        
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(':Name', $Name); // Bind the client name
        $statement->execute();

        $orders = $statement->fetchAll(PDO::FETCH_ASSOC); // Fetch all orders matching the client name

        if ($orders) {
            return $orders; // Return the orders found
        } else {
            echo "No orders found for client: " . htmlspecialchars($Name);
            return null;
        }
    } catch (PDOException $e) {
        echo "Error searching for orders by client name: " . $e->getMessage();
        return null;
    }
}


}
?>