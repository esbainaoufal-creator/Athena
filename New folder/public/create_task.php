<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../models/Task.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sprint_id = $_POST["sprint_id"] ?? 0;
    $title = trim($_POST["title"] ?? "");
    $status = $_POST["status"] ?? "todo";
    $user_id = $_POST["user_id"] ?? $_SESSION["user_id"];
    
    // Validaciones
    if (empty($sprint_id) || $sprint_id <= 0) {
        $_SESSION['task_error'] = "Debes seleccionar un sprint válido";
        header("Location: task_form.php");
        exit;
    }
    
    if (empty($title)) {
        $_SESSION['task_error'] = "El título de la tarea es requerido";
        header("Location: task_form.php");
        exit;
    }
    
    // Validar que el sprint existe
    $sprintCheck = $pdo->prepare("SELECT id FROM sprints WHERE id = ?");
    $sprintCheck->execute([$sprint_id]);
    if (!$sprintCheck->fetch()) {
        $_SESSION['task_error'] = "El sprint seleccionado no existe";
        header("Location: task_form.php");
        exit;
    }

    $task = new Task($pdo);
    $task->sprint_id = $sprint_id;
    $task->title = $title;
    $task->status = $status;
    $task->user_id = $user_id;

    if ($task->create()) {
        header("Location: dashboard.php?msg=task_created");
        exit;
    } else {
        $_SESSION['task_error'] = "Error al crear la tarea";
        header("Location: task_form.php");
        exit;
    }
} else {
    header("Location: task_form.php");
    exit;
}
?>