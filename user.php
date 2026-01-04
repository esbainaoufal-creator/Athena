

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
}