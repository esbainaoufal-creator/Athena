<?php
require_once "../config/database.php";

class Comment
{
    private $conn;
    private $table = "comments";

    public $id;
    public $task_id;
    public $user_id;
    public $content;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function create()
    {
        $sql = "INSERT INTO " . $this->table . "
            (task_id, user_id, content)
            VALUES (:task_id, :user_id, :content)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":task_id" => $this->task_id,
            ":user_id" => $this->user_id,
            ":content" => $this->content
        ]);
    }

    public function getByTask($task_id)
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE task_id = :task_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":task_id" => $task_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function canDelete($comment_id, $user_id, $user_role)
    {
        if ($user_role === "admin") {
            return true;
        }

        $sql = "SELECT user_id FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":id" => $comment_id
        ]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comment) {
            return false;
        }

        return $comment["user_id"] == $user_id;
    }
}
