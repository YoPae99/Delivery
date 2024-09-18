<?php
namespace DELIVERY\Database;

require_once __DIR__ . '/../Configuration/config.php';

class Database {
    public function getStarted() {
        try {
            // Correct DSN string with semicolon between host and dbname
            $conn = new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
