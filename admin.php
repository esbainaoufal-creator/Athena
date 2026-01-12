<?php
require_once "config/Database.php";
require_once "core/Auth.php";
require_once "models/User.php";
require_once "models/Project.php";
require_once "models/Sprint.php";
require_once "models/Task.php";

Auth::requireAdmin();

$user = Auth::user();
$userModel = new User();
$projectModel = new Project();
$sprintModel = new Sprint();
$taskModel = new Task();

$users_count = $userModel->count();
$projects_count = $projectModel->countAll();
$sprints_count = $sprintModel->countAll();
$tasks_count = $taskModel->countAll();

$users = $userModel->getAll();
$projects = $projectModel->getAll();

$db = Database::getInstance();
$stmt = $db->query("SELECT t.*, u.name as user_name, s.name as sprint_name, p.title as project_title FROM tasks t LEFT JOIN users u ON t.user_id = u.id JOIN sprints s ON t.sprint_id = s.id JOIN projects p ON s.project_id = p.id ORDER BY t.created_at DESC LIMIT 10");
$recent_tasks = $stmt->fetchAll();

$stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roles_stats = $stmt->fetchAll();

$stmt = $db->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
$tasks_stats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-crown"></i> Administration</h1>
        <div class="user-info">
            <span><i class="fas fa-user-shield"></i> <?php echo $user['name']; ?></span>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <div class="nav">
        <a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Admin</a>
        <a href="#users"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="#projects"><i class="fas fa-folder"></i> Projets</a>
        <a href="#tasks"><i class="fas fa-tasks"></i> Tâches</a>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users" style="color: #4361ee;"></i>
                <h3>Utilisateurs</h3>
                <div class="stat-number"><?php echo $users_count; ?></div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-folder-open" style="color: #7209b7;"></i>
                <h3>Projets</h3>
                <div class="stat-number"><?php echo $projects_count; ?></div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-rocket" style="color: #f72585;"></i>
                <h3>Sprints</h3>
                <div class="stat-number"><?php echo $sprints_count; ?></div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-tasks" style="color: #4cc9f0;"></i>
                <h3>Tâches</h3>
                <div class="stat-number"><?php echo $tasks_count; ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <div>
                <div class="card" id="users">
                    <div class="card-header">
                        <h3><i class="fas fa-users-cog"></i> Utilisateurs</h3>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="status-badge">
                                        <?php echo $u['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card mt-3" id="tasks">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Tâches récentes</h3>
                    </div>
                    
                    <?php if (empty($recent_tasks)): ?>
                        <div class="empty-state">
                            <p>Aucune tâche</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tâche</th>
                                    <th>Projet</th>
                                    <th>Assignée à</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['user_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $task['status']; ?>">
                                            <?php echo $task['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="card" id="projects">
                    <div class="card-header">
                        <h3><i class="fas fa-folder"></i> Projets</h3>
                        <a href="projects.php?action=create" class="btn btn-small">
                            <i class="fas fa-plus"></i> Nouveau
                        </a>
                    </div>
                    
                    <?php if (empty($projects)): ?>
                        <div class="empty-state">
                            <p>Aucun projet</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Propriétaire</th>
                                    <th>Création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): 
                                    $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
                                    $stmt->execute([$project['owner_id']]);
                                    $owner = $stmt->fetch();
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <td><?php echo htmlspecialchars($owner['name'] ?? '-'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Statistiques</h3>
                    </div>
                    
                    <div style="padding: 1.5rem;">
                        <?php foreach ($tasks_stats as $stat): 
                            $color = $stat['status'] === 'todo' ? '#ef476f' : 
                                    ($stat['status'] === 'in_progress' ? '#ffd166' : '#06d6a0');
                        ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem;">
                                <span><?php echo $stat['status']; ?></span>
                                <span><?php echo $stat['count']; ?> tâches</span>
                            </div>
                            <div style="height: 8px; background: #e9ecef; border-radius: 4px;">
                                <div style="height: 100%; background: <?php echo $color; ?>; width: <?php echo ($stat['count'] / $tasks_count) * 100; ?>%;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>