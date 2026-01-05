<form method="POST">
    <input name="title" placeholder="Título del proyecto"><br>
    <textarea name="description" placeholder="Descripción"></textarea><br>
    <button type="submit">Crear Proyecto</button>
</form>

<?php
require_once "config/database.php";
require_once "Project.php";
require_once "Project.php";

$user = new User($pdo);
if (!$user->isLoggedIn()) {
    die("Debes iniciar sesión para crear un proyecto");
}

if (!in_array($_SESSION["user_role"], ["admin", "manager"])) {
    die("No tienes permiso para crear un proyecto");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $project = new Project($pdo);
    $project->title = $_POST["title"] ?? "";
    $project->description = $_POST["description"] ?? "";
    $project->owner_id = $_POST["owner_id"] ?? 1; // temporal, luego usar $_SESSION["user_id"]

    if ($project->create()) {
        echo "Proyecto creado correctamente";
    } else {
        echo "Error al crear proyecto";
    }
}