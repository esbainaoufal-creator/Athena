<?php
require_once "config/database.php";

class Project
{
    private $conn;
    private $table = "projects";

    public $id;
    public $title;
    public $description;
    public $owner_id;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }
    public function create()
    {
        $sql = "INSERT INTO" . $this->table . "
        (title, description, owner_id)
        VALUES (:title, :description, :owner_id)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":title" => $this->title,
            ":description" => $this->description,
            ":owner_id" => $this->owner_id
        ]);
    }

    public function getALL() {
        $sql = "SELECT * FROM" . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
