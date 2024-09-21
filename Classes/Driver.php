<?php
namespace DELIVERY\Driver;

require_once __DIR__ . '/User.php';  // Adjust path based on file location
if (!class_exists('DELIVERY\User\User')) {
    echo "User class not found!";
    exit;
}

use DELIVERY\User\User;
use DELIVERY\Database\Database;
use PDO;
use PDOException;
class Driver extends User{
    public static function Login($Email, $Password ){}
    public function UpdateOrderStatus($OrderID, $Status) {
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
    
    
    
    
}

