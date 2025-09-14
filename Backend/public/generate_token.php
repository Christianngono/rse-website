<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) {
  echo json_encode(['error' => 'Utilisateur non connecté']);
  exit;
}

$token = bin2hex(random_bytes(16));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

$stmt = $pdo->prepare("INSERT INTO activation_tokens (username, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$user, $token, $expiresAt]);

echo json_encode([
  'status' => 'ok',
  'token' => $token,
  'expires_at' => $expiresAt
]);