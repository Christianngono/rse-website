<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$input = json_decode(file_get_contents("php://input"), true);
$token = $input['token'] ?? '';

if (!$token) {
  echo json_encode(['success' => false, 'message' => 'Token manquant']);
  exit;
}

// Vérifier le token en base
$stmt = $pdo->prepare("SELECT * FROM signature_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$record = $stmt->fetch();

if ($record) {
  // Marquer comme validé
  $update = $pdo->prepare("UPDATE signature_tokens SET is_validated = 1 WHERE token = ?");
  $update->execute([$token]);

  echo json_encode(['success' => true, 'message' => 'Signature validée']);
} else {
  echo json_encode(['success' => false, 'message' => 'Token invalide ou expiré']);
}