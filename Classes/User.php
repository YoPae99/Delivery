<?php
namespace DELIVERY\User;
require_once __DIR__ . '/../Database/Database.php'; // Path to Database.php
use DELIVERY\Database\Database;

class User {
    private $ID;
    private $Name;
    private $Age;
    private $Username;
    private $Email;
    private $Password;
    private $Permission;

    // Constructor
    public function __construct($Name, $Age, $Username, $Email, $Password) {
        $this->Name = $Name;
        $this->Age = $Age;
        $this->Username = $Username;
        $this->Email = $Email;
        $this->Password = $Password;
    }

    // Method for user login (to be implemented in derived classes)
    public function Login($Email, $Password) {
        // Implementation here
    }

    // Static method to create a user
    public static function CreateUser($Name, $Age, $Username, $Email, $Password) {
        $conn = new Database();
        $query = "INSERT INTO user(Name, Age, Username, Email, Password) VALUES (:Name, :Age, :Username, :Email, :Password)";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Name", $Name);
        $statement->bindParam(":Age", $Age);
        $statement->bindParam(":Username", $Username);
        $statement->bindParam(":Email", $Email);
        $statement->bindParam(":Password", $Password);
        $statement->execute();
    }
}
?>
