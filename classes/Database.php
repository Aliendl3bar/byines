<?php

class Database {
    // Singleton instance
    private static $instance = null;
    
    // PDO Connection
    private $conn;
    
    // Database credentials
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $name = 'byines';
    
    // Private constructor to prevent direct instantiation
    private function __construct() {
        try {
            // Data Source Name
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            
            // PDO Options for better error handling and security
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch arrays by default
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            // For production, you might want to log this instead of outputting directly
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
