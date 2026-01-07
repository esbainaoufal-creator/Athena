<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Athena</title>
</head>
<body>

    <h2>Registro</h2>

    <form action="register.php" method="POST">
        <label>Nombre</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contraseña</label><br>
        <input type="password" name="password" required><br><br>

        <label>Rol</label><br>
        <select name="role">
            <option value="member" selected>Miembro</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type="submit">Registrarse</button>
    </form>

</body>
</html>

<?php
require_once "../config/database.php";
require_once "../models/user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $user = new User($pdo);
    $user->name = $name;
    $user->email = $email;
    $user->password = $password;
    $user->role = "member"; // por defecto

    if ($user->register()) {
        echo "Usuario registrado correctamente";
    } else {
        echo "Error al registrar usuario";
    }
}
