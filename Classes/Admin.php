<?php
namespace DELIVERY\Admin;
require_once __DIR__ . '/User.php'; 
require_once __DIR__ . '/../Database/Database.php'; // Correct path to Database.php
use DELIVERY\User\User;

class Admin extends User {
    public function Login($Email, $Password) {
        // Implementation here
    }

    public function CreateOrder($ClientID, $Address) {
        // Implementation here
    }

    public function AssignOrderToDriver($OrderID, $UserID) {
        // Implementation here
    }
}
?>