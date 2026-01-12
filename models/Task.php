<?php
class Task {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($sprint_id, $title, $user_id) {
        $stmt = $this->db->prepare("INSERT INTO tasks (sprint_id, title, user_id) VALUES (?, ?, ?)");
        return $stmt->execute([$sprint_id, $title, $user_id]);
    }
    
    public function getBySprint($sprint_id) {
        $stmt = $this->db->prepare("SELECT t.*, u.name as user_name FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.sprint_id = ? ORDER BY t.created_at DESC");
        $stmt->execute([$sprint_id]);
        return $stmt->fetchAll();
    }
    
    public function getByUser($user_id) {
        $stmt = $this->db->prepare("SELECT t.*, s.name as sprint_name FROM tasks t JOIN sprints s ON t.sprint_id = s.id WHERE t.user_id = ? ORDER BY t.created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function getRecent($user_id, $limit = 5) {
        $stmt = $this->db->prepare("SELECT t.*, s.name as sprint_name FROM tasks t JOIN sprints s ON t.sprint_id = s.id WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT " . (int)$limit);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function countByUser($user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
    
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM tasks");
        return $stmt->fetchColumn();
    }
}