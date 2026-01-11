<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../models/Sprint.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $project_id = $_POST["project_id"] ?? 0;
    $name = trim($_POST["name"] ?? "");
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    
    // Validaciones
    $errors = [];
    
    // Validar proyecto (debe existir y pertenecer al usuario)
    $projectCheck = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND owner_id = ?");
    $projectCheck->execute([$project_id, $_SESSION["user_id"]]);
    if (!$projectCheck->fetch()) {
        $errors[] = "Proyecto no válido o no tienes permisos";
    }
    
    if (empty($name)) {
        $errors[] = "El nombre del sprint es requerido";
    } elseif (strlen($name) > 150) {
        $errors[] = "El nombre no puede exceder 150 caracteres";
    }
    
    if (empty($start_date) || empty($end_date)) {
        $errors[] = "Las fechas son requeridas";
    } else {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        if ($start > $end) {
            $errors[] = "La fecha de inicio no puede ser posterior a la fecha de fin";
        }
        
        // Validar que no se superponga con otro sprint del mismo proyecto
        $overlapCheck = $pdo->prepare("
            SELECT id FROM sprints 
            WHERE project_id = ? 
            AND (
                (start_date BETWEEN ? AND ?) 
                OR (end_date BETWEEN ? AND ?)
                OR (? BETWEEN start_date AND end_date)
                OR (? BETWEEN start_date AND end_date)
            )
        ");
        $overlapCheck->execute([
            $project_id, 
            $start_date, $end_date,
            $start_date, $end_date,
            $start_date, $end_date
        ]);
        
        if ($overlapCheck->fetch()) {
            $errors[] = "Este sprint se superpone con otro sprint existente en el mismo proyecto";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['sprint_errors'] = $errors;
        header("Location: sprint_form.php");
        exit;
    }

    $sprint = new Sprint($pdo);
    $sprint->project_id = $project_id;
    $sprint->name = $name;
    $sprint->start_date = $start_date;
    $sprint->end_date = $end_date;

    if ($sprint->create()) {
        // Crear notificación
        $notificationSql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notificationStmt = $pdo->prepare($notificationSql);
        $notificationMessage = "🎯 Sprint creado: " . htmlspecialchars($name) . " (Proyecto ID: $project_id)";
        $notificationStmt->execute([$_SESSION["user_id"], $notificationMessage]);
        
        header("Location: dashboard.php?msg=sprint_created");
        exit;
    } else {
        $_SESSION['sprint_errors'] = ["Error al crear el sprint en la base de datos"];
        header("Location: sprint_form.php");
        exit;
    }
} else {
    header("Location: sprint_form.php");
    exit;
}
?>