<?php

class User {
    private $conn;
    private $table = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;

    public function __construct($pdo) {
        $this->conn = $pdo;
        if(session_status() === PHP_SESSION_NONE){
            session_start(); 
        }
    }

    
}
