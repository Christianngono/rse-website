<?php
require_once(__DIR__ . '/../app/services/UserService.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $existingUser = UserService::getUserByUsername($username);
        if ($existingUser) {
            $_SESSION['error'] = "Nom d'utilisateur déjà pris.";
        } else {
            UserService::createUser($username, $password);
            $_SESSION['success'] = "Inscription réussie !";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
    }
}