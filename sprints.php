<?php
require_once "config/Database.php";
require_once "core/Auth.php";
require_once "models/Sprint.php";
require_once "models/Project.php";

Auth::requireLogin();

$user = Auth::user();
$project_id = $_GET['project_id'] ?? 0;

if (!$project_id) {
    header("Location: projects.php");
    exit;
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: projects.php");
    exit;
}

if ($user['role'] !== 'admin' && $project['owner_id'] != $user['id']) {
    header("Location: projects.php");
    exit;
}

$sprintModel = new Sprint();
$action = $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    
    if (empty($name) || empty($start_date) || empty($end_date)) {
        $error = "Tous les champs sont requis";
    } elseif ($start_date > $end_date) {
        $error = "La date de début doit être avant la date de fin";
    } else {
        $stmt = $db->prepare("SELECT id FROM sprints WHERE project_id = ? AND start_date <= ? AND end_date >= ?");
        $stmt->execute([$project_id, $end_date, $start_date]);
        
        if ($stmt->fetch()) {
            $error = "Ce sprint chevauche un sprint existant";
        } else {
            $success = $sprintModel->create($project_id, $name, $start_date, $end_date);
            if ($success) {
                header("Location: sprints.php?project_id=" . $project_id);
                exit;
            } else {
                $error = "Erreur lors de la création du sprint";
            }
        }
    }
}

if ($action === 'create') {
    ?>
    <!DOCTYPE html>
<html>
<head>
    <title>Nouveau Sprint - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-rocket"></i> Nouveau Sprint</h1>
        <a href="sprints.php?project_id=<?php echo $project_id; ?>"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-plus-circle"></i> Créer un sprint</h2>
            <p style="text-align: center; color: var(--gray); margin-bottom: 1.5rem;">
                Projet: <strong><?php echo htmlspecialchars($project['title']); ?></strong>
            </p>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nom du sprint</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Date de début</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">Date de fin</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-check-circle"></i> Créer le sprint
                </button>
            </form>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}

$sprints = $sprintModel->getByProject($project_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sprints - Athena</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-rocket"></i> Sprints</h1>
        <div class="user-info">
            <span><?php echo $user['name']; ?></span>
            <a href="projects.php"><i class="fas fa-arrow-left"></i> Projets</a>
        </div>
    </div>

    <div class="nav">
        <a href="sprints.php?project_id=<?php echo $project_id; ?>" class="active">
            <i class="fas fa-rocket"></i> Sprints
        </a>
        <a href="projects.php"><i class="fas fa-folder"></i> Projets</a>
        <a href="tasks.php"><i class="fas fa-tasks"></i> Tâches</a>
        <a href="sprints.php?project_id=<?php echo $project_id; ?>&action=create">
            <i class="fas fa-plus"></i> Nouveau sprint
        </a>
    </div>

    <div class="container">
        <div class="card" style="background: linear-gradient(135deg, #f0f4ff 0%, #e6f0ff 100%); margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="color: var(--primary); margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($project['title']); ?>
                    </h2>
                    <p style="color: var(--gray);">
                        <?php echo htmlspecialchars($project['description'] ?? 'Aucune description'); ?>
                    </p>
                </div>
                <div>
                    <a href="sprints.php?project_id=<?php echo $project_id; ?>&action=create" class="btn">
                        <i class="fas fa-plus"></i> Nouveau sprint
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (empty($sprints)): ?>
            <div class="empty-state">
                <i class="fas fa-rocket"></i>
                <h3>Aucun sprint</h3>
                <p>Créez votre premier sprint pour ce projet</p>
                <a href="sprints.php?project_id=<?php echo $project_id; ?>&action=create" class="btn mt-2">
                    <i class="fas fa-plus"></i> Créer un sprint
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Liste des sprints</h3>
                    <span class="btn btn-small" style="background: var(--info);">
                        <?php echo count($sprints); ?> sprints
                    </span>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Période</th>
                            <th>Durée</th>
                            <th>Tâches</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sprints as $sprint): 
                            $start_date = new DateTime($sprint['start_date']);
                            $end_date = new DateTime($sprint['end_date']);
                            $interval = $start_date->diff($end_date);
                            $duration = $interval->days + 1;
                            
                            $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE sprint_id = ?");
                            $stmt->execute([$sprint['id']]);
                            $tasks_count = $stmt->fetchColumn();
                            
                            $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE sprint_id = ? AND status = 'done'");
                            $stmt->execute([$sprint['id']]);
                            $done_tasks = $stmt->fetchColumn();
                            
                            $progress = $tasks_count > 0 ? round(($done_tasks / $tasks_count) * 100) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($sprint['name']); ?></strong>
                                <?php if ($tasks_count > 0): ?>
                                <div style="margin-top: 5px;">
                                    <div style="height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden;">
                                        <div style="height: 100%; width: <?php echo $progress; ?>%; background: var(--success);"></div>
                                    </div>
                                    <small style="color: var(--gray); font-size: 0.8rem;">
                                        <?php echo $progress; ?>% complété
                                    </small>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $start_date->format('d/m/Y'); ?> -<br>
                                <?php echo $end_date->format('d/m/Y'); ?>
                            </td>
                            <td>
                                <span class="status-badge" style="background: var(--info); color: white;">
                                    <?php echo $duration; ?> jour<?php echo $duration > 1 ? 's' : ''; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: var(--primary); font-weight: bold;"><?php echo $tasks_count; ?></span>
                                    <small style="color: var(--gray);">tâches</small>
                                </div>
                            </td>
                            <td>
                                <a href="tasks.php?sprint_id=<?php echo $sprint['id']; ?>" class="btn btn-small">
                                    <i class="fas fa-tasks"></i> Voir tâches
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>