<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Athena</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>📝 Registro</h2>
            
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="name" required placeholder="Juan Pérez">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="juan@ejemplo.com">
                </div>
                
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role">
                        <option value="member" selected>Miembro</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Crear Cuenta</button>
            </form>
            
            <p style="margin-top: 1.5rem; text-align: center;">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </div>
    </div>
</body>
</html>

<?php
require_once "../config/database.php";
require_once "../models/user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "member";

    $user = new User($pdo);
    $user->name = $name;
    $user->email = $email;
    $user->password = $password;
    $user->role = $role;

    if ($user->register()) {
        header("Location: login.php?msg=registered");
        exit;
    } else {
        echo '<div class="container"><div class="message error">Error al registrar usuario (email ya existe)</div></div>';
    }
}
?>