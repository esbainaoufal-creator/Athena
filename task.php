<?php
require_once "config/database.php";

class Task {
    private $conn;
    private $table = "tasks";

    public $id;
    public $sprint_id;
    public $user_id;
    public $title;
    public $status;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }
}
