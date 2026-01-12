<?php
class Sprint {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($project_id, $name, $start_date, $end_date) {
        $stmt = $this->db->prepare("INSERT INTO sprints (project_id, name, start_date, end_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$project_id, $name, $start_date, $end_date]);
    }
    
    public function getByProject($project_id) {
        $stmt = $this->db->prepare("SELECT * FROM sprints WHERE project_id = ? ORDER BY start_date DESC");
        $stmt->execute([$project_id]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sprints WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM sprints");
        return $stmt->fetchColumn();
    }
}