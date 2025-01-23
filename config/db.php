<?php


class Database {
    private $host = 'localhost';
    private $db_name = '';
    private $username = 'root';  
    private $password = '';      
    private $conn;

    // Connect to the database
    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Connection error: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed.'
            ]);
            exit();
        }
        return $this->conn;
    }
}
?>