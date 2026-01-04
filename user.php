<?php
require_once "config/database.php";
class User {
    private $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;

    }
    public function create($name, $email, $password, $role = "member"){
        $sql = "INSERT INTO users (name, email, password, role) Values (:name, :email, :password, :role)";
        
        $stmt = $this->pdo->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        return $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":password" => $hashedPassword,
            ":role" => $role
        ]);
    }
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user["password"])) {
            return $user;
        } return false;
    }
}