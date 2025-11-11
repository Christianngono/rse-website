<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$frequency = $_POST['frequency'] ?? 'daily';
$format = $_POST['format'] ?? 'pdf';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => "Email invalide"]);
  exit;
}

$stmt = $pdo->prepare("REPLACE INTO report_config (id, email, frequency, format) VALUES (1, ?, ?, ?)");
$stmt->execute([$email, $frequency, $format]);

echo json_encode(['success' => true, 'message' => "Configuration enregistrée"]);