<?php
require_once "../config/database.php";

class Notification
{
    private $conn;
    private $table = "notifications";

    public $id;
    public $user_id;
    public $message;
    public $is_read;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function create()
    {
        $sql = "INSERT INTO " . $this->table . "
            (user_id, message, is_read)
            VALUES (:user_id, :message, :is_read)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":user_id" => $this->user_id,
            ":message" => $this->message,
            ":is_read" => $this->is_read ?? 0
        ]);
    }

    public function getUnreadByUser($user_id)
    {
        $sql = "SELECT * FROM " . $this->table . "
            WHERE user_id = :user_id AND is_read = 0
            ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":user_id" => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($id)
    {
        $sql = "UPDATE " . $this->table . "
            SET is_read = 1
            WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id]);
    }
}
?>