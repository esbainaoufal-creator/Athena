<?php
require_once "config/Database.php";
require_once "core/Auth.php";
require_once "models/Task.php";

Auth::requireLogin();

$task_id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

if ($task_id && $status) {
    $taskModel = new Task();
    $taskModel->updateStatus($task_id, $status);
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
exit;