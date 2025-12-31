<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado");
}

echo "Hola " . $_SESSION["user_name"] . "<br>";
echo "Rol: " . $_SESSION["user_role"];