<?php
require_once "config/Database.php";
require_once "core/Auth.php";
require_once "models/Project.php";
require_once "models/Sprint.php";
require_once "models/Task.php";
require_once "models/User.php";

Auth::requireLogin();

$user = Auth::user();
$user_id = $user['id'];
$user_role = $user['role'];

$projectModel = new Project();
$sprintModel = new Sprint();
$taskModel = new Task();
$userModel = new User();

// Statistiques
if ($user_role === 'admin') {
    $projects_count = $projectModel->countAll();
    $sprints_count = $sprintModel->countAll();
    $tasks_count = $taskModel->countAll();
    $users_count = $userModel->count();
} else {
    $projects_count = $projectModel->countByOwner($user_id);
    
    // Sprints des projets de l'utilisateur
    $stmt = Database::getInstance()->prepare("
        SELECT COUNT(*) FROM sprints s 
        JOIN projects p ON s.project_id = p.id 
        WHERE p.owner_id = ?
    ");
    $stmt->execute([$user_id]);
    $sprints_count = $stmt->fetchColumn();
    
    // Tâches assignées
    $tasks_count = $taskModel->countByUser($user_id);
    $users_count = 0;
}

// Tâches récentes
if ($user_role === 'admin') {
    $stmt = Database::getInstance()->query("
        SELECT t.*, u.name as user_name, s.name as sprint_name 
        FROM tasks t 
        LEFT JOIN users u ON t.user_id = u.id 
        JOIN sprints s ON t.sprint_id = s.id 
        ORDER BY t.created_at DESC 
        LIMIT 5
    ");
    $recent_tasks = $stmt->fetchAll();
} else {
    $recent_tasks = $taskModel->getRecent($user_id, 5);
}

// Tâches par statut
if ($user_role === 'admin') {
    $stmt = Database::getInstance()->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
    $tasks_by_status = $stmt->fetchAll();
} else {
    $stmt = Database::getInstance()->prepare("SELECT status, COUNT(*) as count FROM tasks WHERE user_id = ? GROUP BY status");
    $stmt->execute([$user_id]);
    $tasks_by_status = $stmt->fetchAll();
}

// Calculer les pourcentages
$todo_count = 0;
$in_progress_count = 0;
$done_count = 0;

foreach ($tasks_by_status as $status) {
    switch ($status['status']) {
        case 'todo': $todo_count = $status['count']; break;
        case 'in_progress': $in_progress_count = $status['count']; break;
        case 'done': $done_count = $status['count']; break;
    }
}

$total_tasks = $todo_count + $in_progress_count + $done_count;
$todo_percent = $total_tasks > 0 ? ($todo_count / $total_tasks) * 100 : 0;
$progress_percent = $total_tasks > 0 ? ($in_progress_count / $total_tasks) * 100 : 0;
$done_percent = $total_tasks > 0 ? ($done_count / $total_tasks) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-project-diagram"></i> Athena Dashboard</h1>
        <div class="user-info">
            <span><i class="fas fa-user"></i> <?php echo $user['name']; ?> (<?php echo $user_role; ?>)</span>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="projects.php"><i class="fas fa-folder"></i> Projets</a>
        <a href="sprints.php"><i class="fas fa-rocket"></i> Sprints</a>
        <a href="tasks.php"><i class="fas fa-tasks"></i> Tâches</a>
        <?php if ($user_role === 'admin'): ?>
            <a href="admin.php"><i class="fas fa-cog"></i> Administration</a>
        <?php endif; ?>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-folder-open"></i>
                <h3>Projets</h3>
                <div class="stat-number"><?php echo $projects_count; ?></div>
                <small>Actifs</small>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-rocket"></i>
                <h3>Sprints</h3>
                <div class="stat-number"><?php echo $sprints_count; ?></div>
                <small>En cours</small>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-tasks"></i>
                <h3>Tâches</h3>
                <div class="stat-number"><?php echo $tasks_count; ?></div>
                <small>Assignées</small>
            </div>
            
            <?php if ($user_role === 'admin'): ?>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Utilisateurs</h3>
                <div class="stat-number"><?php echo $users_count; ?></div>
                <small>Total</small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Two Columns Layout -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Left Column -->
            <div>
                <!-- Recent Tasks -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Tâches récentes</h3>
                        <a href="tasks.php" class="btn btn-small">Voir tout</a>
                    </div>
                    
                    <?php if (empty($recent_tasks)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Aucune tâche</h3>
                            <p>Créez votre première tâche pour commencer</p>
                            <a href="tasks.php?action=create" class="btn">Nouvelle tâche</a>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tâche</th>
                                    <th>Sprint</th>
                                    <th>Statut</th>
                                    <th>Assignée à</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['sprint_name']); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = 'status-' . $task['status'];
                                        $status_text = $task['status'] === 'todo' ? 'À faire' : 
                                                     ($task['status'] === 'in_progress' ? 'En cours' : 'Terminée');
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['user_name'] ?? 'Non assignée'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Progress Overview -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> Aperçu de progression</h3>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>À faire</span>
                                <span><?php echo $todo_count; ?> tâches (<?php echo round($todo_percent); ?>%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill progress-todo" style="width: <?php echo $todo_percent; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>En cours</span>
                                <span><?php echo $in_progress_count; ?> tâches (<?php echo round($progress_percent); ?>%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill progress-in-progress" style="width: <?php echo $progress_percent; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Terminées</span>
                                <span><?php echo $done_count; ?> tâches (<?php echo round($done_percent); ?>%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill progress-done" style="width: <?php echo $done_percent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <a href="projects.php?action=create" class="btn">
                            <i class="fas fa-plus"></i> Nouveau projet
                        </a>
                        <a href="sprints.php?action=create" class="btn btn-outline">
                            <i class="fas fa-rocket"></i> Nouveau sprint
                        </a>
                        <a href="tasks.php?action=create" class="btn btn-outline">
                            <i class="fas fa-tasks"></i> Nouvelle tâche
                        </a>
                        <?php if ($user_role === 'admin'): ?>
                            <a href="admin.php" class="btn btn-outline">
                                <i class="fas fa-cog"></i> Administration
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="card mt-3">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Résumé</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 12px; height: 12px; background: #ef476f; border-radius: 50%;"></div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">À faire</div>
                                    <div style="font-size: 0.9rem; color: var(--gray);"><?php echo $todo_count; ?> tâches</div>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 12px; height: 12px; background: #ffd166; border-radius: 50%;"></div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">En cours</div>
                                    <div style="font-size: 0.9rem; color: var(--gray);"><?php echo $in_progress_count; ?> tâches</div>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 12px; height: 12px; background: #06d6a0; border-radius: 50%;"></div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">Terminées</div>
                                    <div style="font-size: 0.9rem; color: var(--gray);"><?php echo $done_count; ?> tâches</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3><i class="fas fa-question-circle"></i> Aide rapide</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 1rem;">
                            <strong>1.</strong> Créez un projet<br>
                            <strong>2.</strong> Ajoutez des sprints<br>
                            <strong>3.</strong> Assignez des tâches<br>
                            <strong>4.</strong> Suivez la progression
                        </p>
                        <a href="#" style="color: var(--primary); font-size: 0.9rem; text-decoration: none;">
                            <i class="fas fa-book"></i> Voir le guide complet →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        
        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border); color: var(--gray); font-size: 0.9rem;">
            <p>Athena v1.0 • © <?php echo date('Y'); ?> • Système de gestion de projets</p>
            <p style="margin-top: 0.5rem;">Connecté en tant que : <strong><?php echo $user['name']; ?></strong> • Dernière connexion : aujourd'hui</p>
        </div>
    </div>
</body>
</html>