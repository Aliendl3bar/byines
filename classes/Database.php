<?php

class Database {
    // singleton instance
    private static $instance = null;
    
    // pdo connection
    private $conn;
    
    // db credentials
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $name = 'byines';
    
    // prevent direct instantiation
    private function __construct() {
        try {
            // dsn string
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            
            // pdo options
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch arrays by default
                PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            // log instead of displaying in production
            die("Database Connection Error: " . $e->getMessage());
        }
    }
    
    // Get the singleton instance
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
        // Get the PDO connection object
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent unserializing of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>
