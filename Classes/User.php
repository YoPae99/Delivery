<?php
namespace DELIVERY\User;

require_once __DIR__ . '/../Database/Database.php'; // Updated path to Database.php
use DELIVERY\Database\Database;

abstract class User {
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

    public function CreateUser($Name, $Age, $Username, $Email, $Password) {
        $conn = new Database();
        
        // Check if the username or email already exists
        $checkQuery = "SELECT COUNT(*) FROM user WHERE Username = :Username OR Email = :Email";
        $checkStatement = $conn->getStarted()->prepare($checkQuery);
        $checkStatement->bindParam(":Username", $Username);
        $checkStatement->bindParam(":Email", $Email);
        $checkStatement->execute();
        $count = $checkStatement->fetchColumn();
        
        if ($count > 0) {
            echo "Username or email already exists!";
            return;
        }
    
        // Hash the password
        $hashedPassword = password_hash($Password, PASSWORD_DEFAULT);
    
        // Proceed with inserting the new user
        $query = "INSERT INTO user(Name, Age, Username, Email, Password) VALUES (:Name, :Age, :Username, :Email, :Password)";
        $statement = $conn->getStarted()->prepare($query);
        $statement->bindParam(":Name", $Name);
        $statement->bindParam(":Age", $Age);
        $statement->bindParam(":Username", $Username);
        $statement->bindParam(":Email", $Email);
        $statement->bindParam(":Password", $hashedPassword);
        $statement->execute();
    }

    abstract public function Login($Email, $Password);
}
