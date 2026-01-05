<?php
session_start();
require_once "../config/database.php";
require_once "../user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $name = $_POST["name"] ?? "";
        $email = $_POST["email"] ?? "";
        $password = $_POST["password"] ?? "";
        $userModel = new User($pdo);
        $created = $userModel->create($name, $email, $password);

            if ($created) {
                        echo "Usuario registrado correctamente";
            } else {
                        echo "Error al registrar el usuario";
            }
}
?>