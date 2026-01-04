<?php
session_start();
?>
<form method="POST">
    <label>Email</label><br>
    <input name="email" placeholder="Email"><br>
    <label>Password</label><br>
    <input name="password" type="password" placeholder="Password"><br>
    <button type="submit">Login</button><br>
</form>


<?php

require_once "user.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $user = loginUser($email, $password);

    if ($user) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_role"] = $user["role"];

        echo "Login correcto. Bienvenido " . $user["name"];

    }else {
        echo "Email o contraseña incorrectos";
    }
}