

<?php
require_once "config/database.php";

function createUser($name, $email, $password, $role = "member") {
    global $pdo;

    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";

    $stmt = $pdo->prepare($sql);

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    return $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":password" => $hashedPassword,
        ":role" => $role

    ]);
}

function loginUser($email, $password) {
    global $pdo;

    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":email" => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        return $user;
    }
    return false;
}