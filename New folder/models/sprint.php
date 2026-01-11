<?php
require_once "../config/database.php";



class Sprint {
    private $conn;
    private $table = "sprints";

    public $project_id;
    public $name;
    public $start_date;
    public $end_date;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    public function create() {
        $sql = "INSERT INTO " . $this->table . "
                (project_id, name, start_date, end_date)
                VALUES (:project_id, :name, :start_date, :end_date)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":project_id" => $this->project_id,
            ":name" => $this->name,
            ":start_date" => $this->start_date,
            ":end_date" => $this->end_date
        ]);
    }
}
