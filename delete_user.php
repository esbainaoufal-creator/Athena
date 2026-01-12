<?php
require_once "config/Database.php";
require_once "core/Auth.php";

Auth::requireAdmin();

$user_id = $_GET['id'] ?? 0;
$current_user = Auth::user();

if ($user_id && $user_id != $current_user['id']) {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
}

header("Location: admin.php");
exit;
?>