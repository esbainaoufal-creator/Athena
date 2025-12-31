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