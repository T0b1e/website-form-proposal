<?php
require_once __DIR__ . '/../../config/db.php'; 

class User {
    private $conn;
    private $table = 'users';

    public $user_id;
    public $username;
    public $email;
    public $password;
    public $address;
    public $phone;
    public $department;
    public $role;
    public $created_at;
    public $updated_at;

    // Constructor to initialize the database connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Create a new user
    public function create() {
        $query = "INSERT INTO $this->table (username, email, password, address, phone, department, role) 
                  VALUES (:username, :email, :password, :address, :phone, :department, :role)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind data
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':role', $this->role);

        return $stmt->execute();
    }

    // Read user by ID
    public function read($user_id) {
        $query = "SELECT * FROM $this->table WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Read user by email
    public function readByEmail($email) {
        // Remove: $email = urldecode($_POST['email']);
        // Use the passed-in $email directly
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    // Read user details by ID without password
    public function readById($user_id) {
        $query = "SELECT username, email, address, phone, department, role FROM $this->table WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user details (without modifying department and role)
    public function update() {
        $query = "UPDATE $this->table 
                  SET username = :username, email = :email, address = :address, phone = :phone
                  WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    // Update password
    public function updatePassword($newPassword) {
        $query = "UPDATE $this->table SET password = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    // Delete user
    public function delete($user_id) {
        $query = "DELETE FROM $this->table WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }
}
?>
