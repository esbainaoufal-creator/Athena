<?php
require_once "../config/database.php";

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

        $sql = "INSERT INTO " . $this->table . " 
        (title, description, owner_id)
        VALUES (:title, :description, :owner_id)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":title" => $this->title,
            ":description" => $this->description,
            ":owner_id" => $this->owner_id
        ]);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM " . $this->table; // espacio después de FROM
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id"; // espacios después de FROM y antes de WHERE
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByOwner($owner_id)
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE owner_id = :owner_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":owner_id" => $owner_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
