<?php
require_once "../config/database.php";
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

    
    public function login($email, $password) {
        $sql = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user["password"])) {
            
            $this->id = $user["id"];
            $this->name = $user["name"];
            $this->email = $user["email"];
            $this->role = $user["role"];

            $_SESSION["user_id"] = $this->id;
            $_SESSION["user_name"] = $this->name;
            $_SESSION["user_role"] = $this->role;

            return true;
        }

        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION["user_id"]);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
