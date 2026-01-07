<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Athena</title>
</head>
<body>

    <h2>Login</h2>

    <form action="login.php" method="POST">
        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contraseña</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

</body>
</html>

<?php
require_once "../config/database.php";
require_once "../models/user.php";

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
