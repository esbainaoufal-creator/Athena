<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../models/project.php";

$projectModel = new project($pdo);
$projects = $projectModel->getByOwner($_SESSION["user_id"]);

// Estadísticas reales
$user_id = $_SESSION["user_id"];
$totalProjects = count($projects);

// Total de sprints
$sprintsSql = "SELECT COUNT(*) as count FROM sprints s 
               JOIN projects p ON s.project_id = p.id 
               WHERE p.owner_id = :owner_id";
$sprintsStmt = $pdo->prepare($sprintsSql);
$sprintsStmt->execute([':owner_id' => $user_id]);
$totalSprints = $sprintsStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Total de tareas
$tasksSql = "SELECT COUNT(*) as count FROM tasks t 
             JOIN sprints s ON t.sprint_id = s.id 
             JOIN projects p ON s.project_id = p.id 
             WHERE p.owner_id = :owner_id";
$tasksStmt = $pdo->prepare($tasksSql);
$tasksStmt->execute([':owner_id' => $user_id]);
$totalTasks = $tasksStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Tareas por hacer
$todoSql = "SELECT COUNT(*) as count FROM tasks t 
            JOIN sprints s ON t.sprint_id = s.id 
            JOIN projects p ON s.project_id = p.id 
            WHERE p.owner_id = :owner_id AND t.status = 'todo'";
$todoStmt = $pdo->prepare($todoSql);
$todoStmt->execute([':owner_id' => $user_id]);
$totalTodo = $todoStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Tareas en progreso
$progressSql = "SELECT COUNT(*) as count FROM tasks t 
                JOIN sprints s ON t.sprint_id = s.id 
                JOIN projects p ON s.project_id = p.id 
                WHERE p.owner_id = :owner_id AND t.status = 'in_progress'";
$progressStmt = $pdo->prepare($progressSql);
$progressStmt->execute([':owner_id' => $user_id]);
$totalInProgress = $progressStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Tareas completadas
$doneSql = "SELECT COUNT(*) as count FROM tasks t 
            JOIN sprints s ON t.sprint_id = s.id 
            JOIN projects p ON s.project_id = p.id 
            WHERE p.owner_id = :owner_id AND t.status = 'done'";
$doneStmt = $pdo->prepare($doneSql);
$doneStmt->execute([':owner_id' => $user_id]);
$totalDone = $doneStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Total de comentarios
$commentsSql = "SELECT COUNT(*) as count FROM comments c 
                JOIN tasks t ON c.task_id = t.id 
                JOIN sprints s ON t.sprint_id = s.id 
                JOIN projects p ON s.project_id = p.id 
                WHERE p.owner_id = :owner_id";
$commentsStmt = $pdo->prepare($commentsSql);
$commentsStmt->execute([':owner_id' => $user_id]);
$totalComments = $commentsStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Tareas recientes
$recentTasksSql = "SELECT t.*, s.name as sprint_name, p.title as project_title 
                   FROM tasks t 
                   JOIN sprints s ON t.sprint_id = s.id 
                   JOIN projects p ON s.project_id = p.id 
                   WHERE p.owner_id = :owner_id 
                   ORDER BY t.created_at DESC 
                   LIMIT 5";
$recentTasksStmt = $pdo->prepare($recentTasksSql);
$recentTasksStmt->execute([':owner_id' => $user_id]);
$recentTasks = $recentTasksStmt->fetchAll(PDO::FETCH_ASSOC);

// Proyectos con más detalle
$detailedProjects = [];
foreach ($projects as $project) {
    // Sprints en este proyecto
    $projectSprintsSql = "SELECT COUNT(*) as sprints FROM sprints WHERE project_id = :project_id";
    $projectSprintsStmt = $pdo->prepare($projectSprintsSql);
    $projectSprintsStmt->execute([':project_id' => $project['id']]);
    $sprintCount = $projectSprintsStmt->fetch(PDO::FETCH_ASSOC)['sprints'] ?? 0;
    
    // Tareas en este proyecto
    $projectTasksSql = "SELECT COUNT(*) as tasks FROM tasks t 
                        JOIN sprints s ON t.sprint_id = s.id 
                        WHERE s.project_id = :project_id";
    $projectTasksStmt = $pdo->prepare($projectTasksSql);
    $projectTasksStmt->execute([':project_id' => $project['id']]);
    $taskCount = $projectTasksStmt->fetch(PDO::FETCH_ASSOC)['tasks'] ?? 0;
    
    $project['sprint_count'] = $sprintCount;
    $project['task_count'] = $taskCount;
    $detailedProjects[] = $project;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Athena</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-project-diagram"></i> Athena Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <strong><?php echo htmlspecialchars($_SESSION["user_name"]); ?></strong>
                    <span class="user-role"><?php echo $_SESSION["user_role"]; ?></span>
                </div>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="nav">
            <a href="project_form.php"><i class="fas fa-plus-circle"></i> Nuevo Proyecto</a>
            <a href="sprint_form.php"><i class="fas fa-rocket"></i> Nuevo Sprint</a>
            <a href="task_form.php"><i class="fas fa-tasks"></i> Nueva Tarea</a>
            <a href="#"><i class="fas fa-chart-bar"></i> Reportes</a>
            <a href="#"><i class="fas fa-users"></i> Equipo</a>
        </div>

        <!-- Mensajes -->
        <?php if (isset($_GET['msg'])): ?>
            <?php 
            $messages = [
                'project_created' => ['Proyecto creado exitosamente', 'success', 'fas fa-check-circle'],
                'sprint_created' => ['Sprint creado exitosamente', 'success', 'fas fa-rocket'],
                'task_created' => ['Tarea creada exitosamente', 'success', 'fas fa-tasks'],
                'registered' => ['¡Registro completado! Bienvenido', 'success', 'fas fa-user-plus']
            ];
            if (isset($messages[$_GET['msg']])):
            ?>
                <div class="message <?php echo $messages[$_GET['msg']][1]; ?>">
                    <i class="<?php echo $messages[$_GET['msg']][2]; ?>"></i> 
                    <?php echo $messages[$_GET['msg']][0]; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card projects">
                <i class="fas fa-folder-open"></i>
                <div class="stat-number"><?php echo $totalProjects; ?></div>
                <h3>Proyectos</h3>
                <small>Activos</small>
            </div>
            
            <div class="stat-card sprints">
                <i class="fas fa-rocket"></i>
                <div class="stat-number"><?php echo $totalSprints; ?></div>
                <h3>Sprints</h3>
                <small>Totales</small>
            </div>
            
            <div class="stat-card tasks">
                <i class="fas fa-tasks"></i>
                <div class="stat-number"><?php echo $totalTasks; ?></div>
                <h3>Tareas</h3>
                <small><?php echo $totalTodo; ?> por hacer</small>
            </div>
            
            <div class="stat-card comments">
                <i class="fas fa-comments"></i>
                <div class="stat-number"><?php echo $totalComments; ?></div>
                <h3>Comentarios</h3>
                <small>Totales</small>
            </div>
        </div>

        <!-- Distribución de Tareas -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Distribución de Tareas</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <div style="text-align: center;">
                    <div style="width: 60px; height: 60px; background: #f72585; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: white; font-weight: bold;">
                        <?php echo $totalTodo; ?>
                    </div>
                    <span style="color: #f72585; font-weight: 600;">Por Hacer</span>
                </div>
                <div style="text-align: center;">
                    <div style="width: 60px; height: 60px; background: #f8961e; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: white; font-weight: bold;">
                        <?php echo $totalInProgress; ?>
                    </div>
                    <span style="color: #f8961e; font-weight: 600;">En Progreso</span>
                </div>
                <div style="text-align: center;">
                    <div style="width: 60px; height: 60px; background: #4cc9f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: white; font-weight: bold;">
                        <?php echo $totalDone; ?>
                    </div>
                    <span style="color: #4cc9f0; font-weight: 600;">Completadas</span>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Proyectos -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-folder"></i> Mis Proyectos</h3>
                        <span class="badge"><?php echo $totalProjects; ?></span>
                    </div>
                    
                    <?php if (empty($detailedProjects)): ?>
                        <div class="empty-state" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-folder-open" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                            <h4>No tienes proyectos</h4>
                            <p>Comienza creando tu primer proyecto para organizar tu trabajo.</p>
                            <a href="project_form.php" class="btn mt-2" style="width: auto;">
                                <i class="fas fa-plus"></i> Crear Primer Proyecto
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="projects-grid">
                            <?php foreach ($detailedProjects as $project): ?>
                            <div class="project-card">
                                <div class="project-header">
                                    <h3><?php echo htmlspecialchars($project["title"]); ?></h3>
                                </div>
                                <div class="project-body">
                                    <div class="project-meta">
                                        <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($project["created_at"])); ?></span>
                                        <span>ID: #<?php echo $project["id"]; ?></span>
                                    </div>
                                    
                                    <p class="project-description">
                                        <?php 
                                        $desc = $project["description"] ?? 'Sin descripción';
                                        echo htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc);
                                        ?>
                                    </p>
                                    
                                    <div class="project-stats">
                                        <div class="project-stat">
                                            <i class="fas fa-rocket"></i>
                                            <span><?php echo $project['sprint_count']; ?> sprints</span>
                                        </div>
                                        <div class="project-stat">
                                            <i class="fas fa-tasks"></i>
                                            <span><?php echo $project['task_count']; ?> tareas</span>
                                        </div>
                                    </div>
                                    
                                    <div class="project-actions">
                                        <a href="#" class="btn-small">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="sprint_form.php?project_id=<?php echo $project['id']; ?>" class="btn-small btn-secondary">
                                            <i class="fas fa-plus"></i> Sprint
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Actividad Reciente -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                    </div>
                    
                    <?php if (empty($recentTasks)): ?>
                        <div style="text-align: center; padding: 2rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p style="margin-top: 1rem;">No hay actividad reciente</p>
                        </div>
                    <?php else: ?>
                        <div class="list">
                            <?php foreach ($recentTasks as $task): 
                                $statusConfig = [
                                    'todo' => ['icon' => 'far fa-circle', 'color' => '#f72585', 'text' => 'Por hacer'],
                                    'in_progress' => ['icon' => 'fas fa-spinner fa-spin', 'color' => '#f8961e', 'text' => 'En progreso'],
                                    'done' => ['icon' => 'fas fa-check-circle', 'color' => '#4cc9f0', 'text' => 'Completado']
                                ];
                                $status = $statusConfig[$task['status']] ?? $statusConfig['todo'];
                            ?>
                            <div class="list-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                    <div style="font-size: 0.85rem; color: var(--gray); margin-top: 3px;">
                                        <?php echo htmlspecialchars($task['project_title']); ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $task['status']; ?>" style="background: <?php echo $status['color']; ?>20; color: <?php echo $status['color']; ?>;">
                                        <i class="<?php echo $status['icon']; ?>"></i> <?php echo $status['text']; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="#" style="color: var(--primary); font-size: 0.9rem;">
                                Ver toda la actividad <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card mt-2">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="task_form.php" class="btn btn-small btn-outline">
                            <i class="fas fa-plus"></i> Nueva Tarea
                        </a>
                        <a href="#" class="btn btn-small btn-outline">
                            <i class="fas fa-file-export"></i> Exportar Reporte
                        </a>
                        <a href="#" class="btn btn-small btn-outline">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <a href="logout.php" class="btn btn-small btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Athena v1.0</strong> • Sistema de Gestión de Proyectos • 
                <?php echo date('Y'); ?> • 
                <a href="#" style="color: var(--primary);">Ayuda</a> • 
                <a href="#" style="color: var(--primary);">Soporte</a>
            </p>
            <p style="font-size: 0.8rem; margin-top: 5px; color: var(--gray);">
                Usuario: <?php echo htmlspecialchars($_SESSION["user_name"]); ?> • 
                Último acceso: Hoy <?php echo date('H:i'); ?>
            </p>
        </div>
    </div>

    <style>
        .empty-state {
            text-align: center;
            padding: 2rem;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: var(--gray-light);
            margin-bottom: 1rem;
        }
        
        .form-grid {
            display: grid;
            gap: 1rem;
        }
        
        @media (max-width: 992px) {
            .container > div:last-child {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>