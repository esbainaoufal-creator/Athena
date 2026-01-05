<?php
require_once "config/database.php";

class Project {
    private $conn;
    private $table = "projects";

    public $id;
    public $title;
    public $description;
    public $owner_id;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }
}
