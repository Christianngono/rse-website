<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$admin = $_GET['admin'] ?? '';
$action = $_GET['action'] ?? '';
$emailTo = $_GET['email'] ?? '';

if (!filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => "Adresse email invalide"]);
  exit;
}

$sql = "SELECT admin_username, action, target_user_id, timestamp FROM admin_logs WHERE 1=1";
$params = [];

if ($date) {
  $sql .= " AND DATE(timestamp) = ?";
  $params[] = $date;
}
if ($admin) {
  $sql .= " AND admin_username LIKE ?";
  $params[] = "%$admin%";
}
if ($action) {
  $sql .= " AND action LIKE ?";
  $params[] = "%$action%";
}

$sql .= " ORDER BY timestamp DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$logs) {
  echo json_encode(['success' => false, 'message' => "Aucun log à envoyer."]);
  exit;
}

$body = "Journal des actions admin :\n\n";
foreach ($logs as $log) {
  $body .= "{$log['timestamp']} | {$log['admin_username']} | {$log['action']} | ID cible: {$log['target_user_id']}\n";
}

$headers = "From: rse@quiz.com\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$sent = mail($emailTo, "Journal des actions administratives", $body, $headers);

if ($sent) {
  echo json_encode(['success' => true, 'message' => "Email envoyé à $emailTo"]);
} else {
  echo json_encode(['success' => false, 'message' => "Échec de l’envoi de l’email"]);
}