<?php
namespace DELIVERY\User;

require_once __DIR__ . '/../Database/Database.php'; // Path to Database.php

use DELIVERY\Database\Database;
use PDO;
use PDOException;
use Exception;

class User {
    private $ID;
    private $Name;
    private $Age;
    private $Username;
    private $Email; 
    private $Password;
    private $Permission;

    // Constructor
    public function __construct($Name = null, $Permission = null, $Age = null, $Username = null, $Email = null, $Password = null) {
        $this->Name = $Name;
        $this->Permission = $Permission;
        $this->Age = $Age;
        $this->Username = $Username;
        $this->Email = $Email;
        $this->Password = $Password;
    }

    // Static method to create a user
    public static function CreateUser($Name, $Permission, $Age, $Username, $Email, $Password) {
        $conn = new Database();
        try {
            $query = "INSERT INTO user (Name, Permission, Age, Username, Email, Password) VALUES (:Name, :Permission, :Age, :Username, :Email, :Password)";
            $statement = $conn->getStarted()->prepare($query);
            $statement->bindParam(":Name", $Name);
            $statement->bindParam(":Permission", $Permission);
            $statement->bindParam(":Age", $Age);
            $statement->bindParam(":Username", $Username);
            $statement->bindParam(":Email", $Email);
            $statement->bindParam(":Password", $Password);
            $statement->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "Error: Username or Email already exists. Please choose another.";
            } else {
                echo "An error occurred: " . $e->getMessage();
            }
        }
    }

    // Static method to handle user login
    public static function Login($email, $password) {
        $conn = new Database();
        try {
            $query = "SELECT * FROM user WHERE Email = :email";
            $statement = $conn->getStarted()->prepare($query);
            $statement->bindParam(':email', $email);
            $statement->execute();
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            
            // Debugging: Output the fetched user data
            // echo "<pre>";
            // print_r($user);
            // echo "</pre>";
    
            if ($user && password_verify($password, $user['Password'])) {
                return [
                    'ID' => $user['ID'],
                    'Name' => $user['Name'],
                    'Permission' => $user['Permission'],
                    'Age' => $user['Age'],
                    'Username' => $user['Username'],
                    'Email' => $user['Email'],
                    'Password' => $user['Password']
                ];
            } else {
                return false; // Invalid credentials
            }
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    
    
}
?>
