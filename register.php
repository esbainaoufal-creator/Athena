<form method="POST">
    <input name="name" placeholder="Nombre"><br>
    <input name="email" placeholder="Email"><br>
    <input name="password" type="password" placeholder="Contraseña"><br>
    <button type="submit">Registrar</button>
</form>

<?php
require_once "config/database.php";
require_once "User.php";

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
