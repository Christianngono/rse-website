<?php
require '../config/database.php';
session_start();
header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$response = ['success' => false, 'message' => ''];

if (!$username || !$password) {
    $response['message'] = "Identifiants requis.";
    echo json_encode($response);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $response['message'] = "Identifiants incorrects.";
} elseif (!$user['is_active']) {
    $response['message'] = "Votre compte n'est pas activé.";
} else {
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];
    $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE username = ?");
    $stmt->execute([$username]);

    $response['success'] = true;
    $response['message'] = "Connexion réussie.";
}

echo json_encode($response);