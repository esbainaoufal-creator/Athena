<?php
require_once "config/Database.php";
require_once "core/Auth.php";
require_once "models/User.php";
require_once "models/Sprint.php";
require_once "models/Task.php";

Auth::requireLogin();

$user = Auth::user();
$userModel = new User();
$sprintModel = new Sprint();
$taskModel = new Task();

$sprint_id = $_GET['sprint_id'] ?? 0;

if (isset($_GET['action']) && $_GET['action'] === 'create') {
    if (!$sprint_id) {
        header("Location: sprints.php");
        exit;
    }
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $title = $_POST["title"] ?? "";
        $status = $_POST["status"] ?? "todo";
        $assigned_user = $_POST["user_id"] ?? $user['id'];
        
        if (!empty($title)) {
            $taskModel->create($sprint_id, $title, $assigned_user);
            header("Location: tasks.php?sprint_id=" . $sprint_id);
            exit;
        }
    }
    
    $users = $userModel->getAll();
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>New Task</title><link rel="stylesheet" href="style.css"></head>
    <body>
        <div class="header"><h1>New Task</h1><a href="tasks.php?sprint_id=<?php echo $sprint_id; ?>">Back</a></div>
        <div class="container">
            <form method="POST">
                <input type="text" name="title" placeholder="Title" required>
                <select name="status">
                    <option value="todo">Todo</option>
                    <option value="in_progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
                
                <?php if ($user['role'] === 'admin' || $user['role'] === 'manager'): ?>
                <select name="user_id">
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo $u['id'] == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                
                <button type="submit">Create</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($sprint_id) {
    $tasks = $taskModel->getBySprint($sprint_id);
    $sprint = $sprintModel->getById($sprint_id);
} else {
    $tasks = $taskModel->getByUser($user['id']);
    $sprint = null;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tasks - Athena</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <h1>
            <?php if ($sprint): ?>
            Tasks: <?php echo htmlspecialchars($sprint['name']); ?>
            <?php else: ?>
            My Tasks
            <?php endif; ?>
        </h1>
        <a href="dashboard.php">Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($sprint): ?>
        <a href="tasks.php?sprint_id=<?php echo $sprint_id; ?>&action=create" class="btn">New Task</a>
        <?php endif; ?>
        
        <table>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo htmlspecialchars($task['title']); ?></td>
                <td><?php echo $task['status']; ?></td>
                <td><?php echo htmlspecialchars($task['user_name'] ?? 'Unassigned'); ?></td>
                <td><?php echo date('d/m/Y', strtotime($task['created_at'])); ?></td>
                <td>
                    <a href="update_task.php?id=<?php echo $task['id']; ?>&status=done">Done</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>