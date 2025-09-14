<?php
require '../config/database.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "<h2>Token d'activation manquant.</h2>";
    exit;
}

$stmt = $pdo->prepare("SELECT username FROM activation_tokens WHERE token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    echo "<h2>Token invalide ou expiré.</h2>";
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET is_active = TRUE WHERE username = ?");
$stmt->execute([$user['username']]);

$stmt = $pdo->prepare("DELETE FROM activation_tokens WHERE token = ?");
$stmt->execute([$token]);

echo "<h2>Compte activé avec succès !</h2><p><a href='Frontend/login.html'>Se connecter</a></p>";