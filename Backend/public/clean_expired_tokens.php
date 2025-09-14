<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Supprimer les tokens expirés
$stmt = $pdo->prepare("DELETE FROM activation_tokens WHERE expires_at < NOW()");
$stmt->execute();

echo json_encode([
  'status' => 'ok',
  'message' => 'suppression des tokens expirés effectuée',
  'deleted' => $stmt->rowCount()
]);