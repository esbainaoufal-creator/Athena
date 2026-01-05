<?php
require_once "../config/database.php";
require_once "../User.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $userModel = new User($pdo);
    if ($userModel->login($email, $password)) {
        echo "Login correcto. Bienvenido " . $userModel->name;
    } else {
        echo "Email o contraseña incorrectos";
    }
}
