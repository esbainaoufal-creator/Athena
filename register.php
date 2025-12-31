<form method="POST">
    <label>Name</label><br>
    <input name="name" placeholder="Name"><br>
    <label>Email</label><br>
    <input name="email" placeholder="Email"><br>
    <label>Password</label><br>
    <input name="password" type="password" placeholder="Password"><br>
</form>

<?php

require_once "user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    if (createUser($name, $email, $password)) {
        echo "Usuario creado correctamente";
    } else {
        echo "Error al crear usuario";
    }
}
