<?php
session_start();

require_once "../config/database.php";
require_once "../models/user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "member";

    // Validaciones básicas
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "La contraseña debe tener al menos 6 caracteres";
        header("Location: register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Email inválido";
        header("Location: register.php");
        exit;
    }

    $user = new User($pdo);
    $user->name = $name;
    $user->email = $email;
    $user->password = $password;
    $user->role = $role;

    if ($user->register()) {
        // Auto-login después de registro
        if ($user->login($email, $password)) {
            header("Location: dashboard.php?msg=registered");
            exit;
        } else {
            header("Location: login.php?msg=registered");
            exit;
        }
    } else {
        $_SESSION['register_error'] = "Error al registrar usuario (¿email ya existe?)";
        header("Location: register.php");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
?>