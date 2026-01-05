<?php
session_start();
require_once "../config/database.php";
require_once "../User.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $userModel = new User($pdo);
    $user = $userModel->login($email, $password);

    if ($user) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_role"] = $user["role"];

        echo "Login correcto. Bienvenido " . $user["name"];
}else {
            echo "Email o contraseña incorrectos";
}
}
?>