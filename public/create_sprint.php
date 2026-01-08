<?php
session_start();

require_once "../config/database.php";
require_once "../models/sprint.php";

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sprint = new sprint($pdo);
    $sprint->project_id = $_POST["project_id"];
    $sprint->name = $_POST["name"];
    $sprint->start_date = $_POST["start_date"];
    $sprint->end_date = $_POST["end_date"];

    if ($sprint->create()) {
        echo "Sprint creado";
    } else {
        echo "Error al crear sprint";
    }
}
