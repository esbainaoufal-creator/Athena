<?php
require_once "config/database.php";

class Task
{
    private $conn;
    private $table = "tasks";

    public $id;
    public $sprint_id;
    public $user_id;
    public $title;
    public $status;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function create()
    {
        $sql = "INSERT INTO " . $this->table . "
            (sprint_id, user_id, title, status)
            VALUES (:sprint_id, :user_id, :title, :status)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":sprint_id" => $this->sprint_id,
            ":user_id"   => $this->user_id,
            ":title"     => $this->title,
            ":status"    => $this->status ?? "todo"
        ]);
    }

    public function getBySprint($sprint_id)
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE sprint_id = :sprint_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":sprint_id" => $sprint_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($task_id, $status, $user_id, $user_role)
    {
        if (!$this->canEdit($task_id, $user_id, $user_role)) {
            return false;
        }

        $sql = "UPDATE " . $this->table . "
            SET status = :status
            WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":status" => $status,
            ":id" => $task_id
        ]);
    }

    public function canEdit($task_id, $user_id, $user_role)
    {
        if ($user_role === "admin") {
            return true;
        }

        $sql = "SELECT user_id FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":id" => $task_id
        ]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            return false;
        }

        return $task["user_id"] == $user_id;
    }

    public function canAssign($user_role)
    {
        return in_array($user_role, ["admin", "manager"]);
    }

    public function assignToUser($task_id, $user_id, $actor_role) {
    if (!$this->canAssign($actor_role)) {
        return false;
    }

    $sql = "UPDATE " . $this->table . "
            SET user_id = :user_id
            WHERE id = :id";
    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ":user_id" => $user_id,
        ":id" => $task_id
    ]);
}
}

