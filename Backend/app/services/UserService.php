<?php
require_once(__DIR__ . '/../models/User.php');
require_once(__DIR__ . '/../config/database.php');

class UserService {
    public static function createUser($username, $password, $role = 'user' 'admin', $profil = 'novice' 'confirmé' 'expert', $email = null, $phone = null) {
        global $pdo;
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, profil, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $password_hash]);
    }

    public static function getUserByUsername($username) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchObject('User');
    }
}