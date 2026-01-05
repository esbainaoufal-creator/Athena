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
}
