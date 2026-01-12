<?php
require_once "config/Database.php";
require_once "core/Auth.php";

Auth::requireAdmin();

$project_id = $_GET['id'] ?? 0;

if ($project_id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
}

header("Location: admin.php");
exit;
?>