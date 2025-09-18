<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: welcome.php?message=" . urlencode("Nom d’utilisateur et mot de passe requis."));
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            header("Location: welcome.php?message=" . urlencode("Identifiants incorrects."));
            exit;
        }

        $_SESSION['user']       = $user['username'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['profil']     = $user['profil'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['phone']      = $user['phone'];
        $_SESSION['quiz_score'] = (int) $user['quiz_score'];

        $redirect = $user['role'] === 'admin' ? 'admin.php' : 'quiz.php';
        header("Location: $redirect");
        exit;

    } catch (PDOException $e) {
        error_log("Erreur DB: " . $e->getMessage());
        header("Location: welcome.php?message=" . urlencode("Erreur interne du serveur."));
        exit;
    }
}

// Si GET, afficher la page d’accueil
include 'welcome.php';