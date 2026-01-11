<?php
session_start();

require_once "../config/database.php";
require_once "../models/Project.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    
    // Validación
    if (empty($title)) {
        $_SESSION['error'] = "El título del proyecto es requerido";
        header("Location: project_form.php");
        exit;
    }
    
    if (strlen($title) > 150) {
        $_SESSION['error'] = "El título no puede exceder 150 caracteres";
        header("Location: project_form.php");
        exit;
    }

    $project = new Project($pdo);
    $project->title = $title;
    $project->description = $description;
    $project->owner_id = $_SESSION["user_id"];

    if ($project->create()) {
        header("Location: dashboard.php?msg=project_created");
        exit;
    } else {
        $_SESSION['error'] = "Error al crear el proyecto";
        header("Location: project_form.php");
        exit;
    }
} else {
    header("Location: project_form.php");
    exit;
}
?>