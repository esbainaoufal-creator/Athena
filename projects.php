<?php
require_once "config/Database.php";
require_once "core/Auth.php";
require_once "models/Project.php";

Auth::requireLogin();

$user = Auth::user();
$projectModel = new Project();

$action = $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    
    if (empty($title)) {
        $error = "Le titre est requis";
    } else {
        $success = $projectModel->create($title, $description, $user['id']);
        if ($success) {
            header("Location: projects.php");
            exit;
        } else {
            $error = "Erreur lors de la création du projet";
        }
    }
}

if ($action === 'create') {
    ?>
    <!DOCTYPE html>
<html>
<head>
    <title>Nouveau Projet - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-plus-circle"></i> Nouveau Projet</h1>
        <a href="projects.php"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-folder-plus"></i> Créer un projet</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Titre du projet</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-check-circle"></i> Créer le projet
                </button>
            </form>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}

if ($user['role'] === 'admin') {
    $projects = $projectModel->getAll();
} else {
    $projects = $projectModel->getByOwner($user['id']);
}

$db = Database::getInstance();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Projets - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-folder"></i> Mes Projets</h1>
        <div class="user-info">
            <span><?php echo $user['name']; ?></span>
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        </div>
    </div>

    <div class="nav">
        <a href="projects.php" class="active"><i class="fas fa-folder"></i> Projets</a>
        <a href="sprints.php"><i class="fas fa-rocket"></i> Sprints</a>
        <a href="tasks.php"><i class="fas fa-tasks"></i> Tâches</a>
        <a href="projects.php?action=create"><i class="fas fa-plus"></i> Nouveau projet</a>
    </div>

    <div class="container">
        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>Aucun projet</h3>
                <p>Commencez par créer votre premier projet</p>
                <a href="projects.php?action=create" class="btn mt-2">
                    <i class="fas fa-plus"></i> Créer un projet
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Liste des projets</h3>
                    <a href="projects.php?action=create" class="btn btn-small">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): 
                            $stmt = $db->prepare("SELECT COUNT(*) FROM sprints WHERE project_id = ?");
                            $stmt->execute([$project['id']]);
                            $sprints_count = $stmt->fetchColumn();
                            
                            $stmt = $db->prepare("
                                SELECT COUNT(*) FROM tasks t 
                                JOIN sprints s ON t.sprint_id = s.id 
                                WHERE s.project_id = ?
                            ");
                            $stmt->execute([$project['id']]);
                            $tasks_count = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                <div style="font-size: 0.85rem; color: var(--gray); margin-top: 4px;">
                                    <span><i class="fas fa-rocket"></i> <?php echo $sprints_count; ?> sprints</span>
                                    <span style="margin-left: 10px;"><i class="fas fa-tasks"></i> <?php echo $tasks_count; ?> tâches</span>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(substr($project['description'] ?? 'Aucune description', 0, 100)); ?>
                                <?php if (strlen($project['description'] ?? '') > 100): ?>...<?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                            <td>
                                <a href="sprints.php?project_id=<?php echo $project['id']; ?>" class="btn btn-small" style="background: var(--info);">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="stats-grid" style="margin-top: 2rem;">
                <div class="stat-card">
                    <i class="fas fa-folder-open"></i>
                    <h3>Projets totaux</h3>
                    <div class="stat-number"><?php echo count($projects); ?></div>
                </div>
                
                <?php 
                $stmt = $db->prepare("
                    SELECT COUNT(DISTINCT s.id) 
                    FROM sprints s 
                    JOIN projects p ON s.project_id = p.id 
                    WHERE " . ($user['role'] === 'admin' ? "1=1" : "p.owner_id = ?")
                );
                if ($user['role'] === 'admin') {
                    $stmt->execute();
                } else {
                    $stmt->execute([$user['id']]);
                }
                $total_sprints = $stmt->fetchColumn();
                ?>
                
                <div class="stat-card">
                    <i class="fas fa-rocket"></i>
                    <h3>Sprints totaux</h3>
                    <div class="stat-number"><?php echo $total_sprints; ?></div>
                </div>
                
                <?php 
                $stmt = $db->prepare("
                    SELECT COUNT(DISTINCT t.id) 
                    FROM tasks t 
                    JOIN sprints s ON t.sprint_id = s.id 
                    JOIN projects p ON s.project_id = p.id 
                    WHERE " . ($user['role'] === 'admin' ? "1=1" : "p.owner_id = ?")
                );
                if ($user['role'] === 'admin') {
                    $stmt->execute();
                } else {
                    $stmt->execute([$user['id']]);
                }
                $total_tasks = $stmt->fetchColumn();
                ?>
                
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3>Tâches totales</h3>
                    <div class="stat-number"><?php echo $total_tasks; ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>