<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../models/project.php";

$projectModel = new project($pdo);
$projects = $projectModel->getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Bienvenido <?php echo $_SESSION["user_name"]; ?></h2>

<h3>Mis proyectos</h3>

<ul>
    <?php foreach ($projects as $project): ?>
        <li>
            <?php echo htmlspecialchars($project["title"]); ?>
        </li>
    <?php endforeach; ?>
</ul>

<a href="project_form.php">Crear nuevo proyecto</a>

</body>
</html>
