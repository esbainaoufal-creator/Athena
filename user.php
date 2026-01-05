<?php

class User {
    private $conn;
    private $table = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;

    public function __construct($pdo) {
        $this->conn = $pdo;
        if(session_status() === PHP_SESSION_NONE){
            session_start(); 
        }
    }

    
    public function register() {
        $sql = "INSERT INTO " . $this->table . " 
                (name, email, password, role) 
                VALUES (:name, :email, :password, :role)";
        $stmt = $this->conn->prepare($sql);

        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        return $stmt->execute([
            ":name" => $this->name,
            ":email" => $this->email,
            ":password" => $this->password,
            ":role" => $this->role ?? 'member'
        ]);
    }

    
    
}
