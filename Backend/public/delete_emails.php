<?php
header('Content-Type: application/json');
session_start();
require '../config/database.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
  echo json_encode(['success' => false, 'message' => 'Accès refusé']);
  exit;
}

// Suppression
$ids = $_POST['delete_ids'] ?? [];
if (!empty($ids)) {
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("DELETE FROM email_logs WHERE id IN ($placeholders)");
  $stmt->execute($ids);
  echo json_encode(['success' => true, 'message' => 'Suppression réussie']);
} else {
  echo json_encode(['success' => false, 'message' => 'Aucun ID fourni']);
}