<?php
namespace DELIVERY\Client;
require_once __DIR__ . '/User.php';
use DELIVERY\User\User;

class Client extends User {
    // Method to check if the order belongs to the client
    public function doesOrderBelongToClient($orderId, $clientId) {
        $db = new \DELIVERY\Database\Database();
        $conn = $db->getStarted();

        if ($conn) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE OrderId = :orderId AND ClientId = :clientId");
            $stmt->bindParam(':orderId', $orderId);
            $stmt->bindParam(':clientId', $clientId);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            return $count > 0;  // Return true if the order belongs to the client
        }
        return false;
    }

    // Method to track the order status
    public function TrackOrder($orderId) {
        $db = new \DELIVERY\Database\Database();
        $conn = $db->getStarted();

        if ($conn) {
            $stmt = $conn->prepare("SELECT Status FROM orders WHERE OrderId = :orderId");
            $stmt->bindParam(':orderId', $orderId);
            $stmt->execute();
            $status = $stmt->fetchColumn();

            return $status;  // Return the status of the order
        }
        return null;  // Return null if the connection fails
    }
}
