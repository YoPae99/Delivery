<?php
namespace DELIVERY\Database;

require_once __DIR__ . '/../Configuration/config.php';

class Database {
    public function getStarted() {
        try {
            //new PDO('DSN', 'username', 'password');
            //Only database host and database name include in DSN. The username and password do not need.
            $conn = new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

            //PDO ATTR_ERRMODE is for error handling
            //PDO ERRMODE_EXCEPTION is to throw an exception
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
