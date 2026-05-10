<?php
class User {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function register($name, $email, $password) {
        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ["success" => false, "message" => "Email already exists"];
        }

        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $hash])) {
            return ["success" => true, "user_id" => $this->db->lastInsertId()];
        }
        
        return ["success" => false, "message" => "Registration failed"];
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return ["success" => true, "user" => [
                "id" => $user['id'],
                "name" => $user['name']
            ]];
        }

        return ["success" => false, "message" => "Invalid email or password"];
    }
}
