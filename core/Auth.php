<?php
session_start();

class Auth {
    public static function login($email, $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    }
    
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    
    public static function user() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }
    
    public static function logout() {
        session_destroy();
    }
    
    public static function requireLogin() {
        if (!self::check()) {
            header("Location: login.php");
            exit;
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        if ($_SESSION['user_role'] !== 'admin') {
            header("Location: dashboard.php");
            exit;
        }
    }
    
    public static function requireManager() {
        self::requireLogin();
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'admin' && $role !== 'manager') {
            header("Location: dashboard.php");
            exit;
        }
    }
}