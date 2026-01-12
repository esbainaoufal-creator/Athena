<?php
class Project {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($title, $description, $owner_id) {
        $stmt = $this->db->prepare("INSERT INTO projects (title, description, owner_id) VALUES (?, ?, ?)");
        return $stmt->execute([$title, $description, $owner_id]);
    }
    
    public function getByOwner($owner_id) {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE owner_id = ? ORDER BY created_at DESC");
        $stmt->execute([$owner_id]);
        return $stmt->fetchAll();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM projects ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function countByOwner($owner_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE owner_id = ?");
        $stmt->execute([$owner_id]);
        return $stmt->fetchColumn();
    }
    
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM projects");
        return $stmt->fetchColumn();
    }
}