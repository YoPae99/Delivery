<?php
namespace DELIVERY\Admin;

require_once __DIR__ . '/User.php'; 
require_once __DIR__ . '/../Database/Database.php';

use DELIVERY\User\User;
use DELIVERY\Database\Database;
use Exception;
use PDO;

class Admin extends User {
    public function Login($Email, $Password) {
        // Implementation here
        $conn = new Database();
        $encrypted_password = password_hash($Password, PASSWORD_BCRYPT);    
        try {
            $query = "SELECT * FROM user WHERE email = :email";

            $statement = $conn->getStarted()->prepare($query);
            $statement->bindParam(':email', $email);
            
            $statement->execute();
    
            //fetch the result
            $result = $statement->fetch(PDO::FETCH_ASSOC);
    
            if($result){
                if (password_verify($Password, $result['password']))
                    echo "logged in";
                else
                    echo "error";
            }
        }
        catch (Exception $ex){
            echo $ex->getMessage();
        }
    }

    

    public function CreateUser($Name, $Age, $Username, $Email, $Password) {}

    public function AssignOrderToDriver($OrderID, $UserID) {
        // Implementation here
    }
}

