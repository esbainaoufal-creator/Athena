<?php
require_once "config/database.php";

class Comment {
    private $conn;
    private $table = "comments";

    public $id;
    public $task_id;
    public $user_id;
    public $content;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }
}
